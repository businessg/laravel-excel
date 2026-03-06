<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Driver;

use BusinessG\LaravelExcel\Data\Export\ExportCallbackParam;
use BusinessG\LaravelExcel\Data\Export\ExportConfig;
use BusinessG\LaravelExcel\Data\Export\ExportData;
use BusinessG\LaravelExcel\Data\Export\Sheet as ExportSheet;
use BusinessG\LaravelExcel\Data\Import\ImportConfig;
use BusinessG\LaravelExcel\Data\Import\ImportData;
use BusinessG\LaravelExcel\Data\Import\ImportPreCheckData;
use BusinessG\LaravelExcel\Data\Import\ImportRowCallbackParam;
use BusinessG\LaravelExcel\Data\Import\Sheet as ImportSheet;
use BusinessG\LaravelExcel\Event\AfterExportData;
use BusinessG\LaravelExcel\Event\AfterExportOutput;
use BusinessG\LaravelExcel\Event\AfterImportData;
use BusinessG\LaravelExcel\Event\BeforeExportData;
use BusinessG\LaravelExcel\Event\BeforeExportOutput;
use BusinessG\LaravelExcel\Event\AfterPreCheck;
use BusinessG\LaravelExcel\Event\AfterPreCheckData;
use BusinessG\LaravelExcel\Event\BeforeImportData;
use BusinessG\LaravelExcel\Event\Error;
use BusinessG\LaravelExcel\Exception\ExcelException;
use BusinessG\LaravelExcel\Helper\Helper;
use BusinessG\LaravelExcel\Progress\ProgressData;
use BusinessG\LaravelExcel\Progress\ProgressInterface;
use BusinessG\LaravelExcel\Strategy\Path\ExportPathStrategyInterface;
use Illuminate\Contracts\Events\Dispatcher as EventsDispatcher;
use Illuminate\Contracts\Filesystem\Filesystem;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class Driver implements DriverInterface
{
    public EventsDispatcher $event;
    public Filesystem $filesystem;

    public function __construct(protected ContainerInterface $container, protected array $config, protected string $name)
    {
        $this->event = $container->get(EventsDispatcher::class);
        $storage = $this->config['filesystem']['storage'] ?? 'local';
        $this->filesystem = \Illuminate\Support\Facades\Storage::disk($storage);
    }

    public function export(ExportConfig $config): ExportData
    {
        try {
            $exportData = new ExportData(['token' => $config->getToken()]);

            $filePath = $this->getTempFileName();

            $path = $this->exportExcel($config, $filePath);

            $this->event->dispatch(new BeforeExportOutput($config, $this));

            $exportData->response = $this->exportOutPut($config, $path);

            $this->event->dispatch(new AfterExportOutput($config, $this, $exportData));

            return $exportData;
        } catch (ExcelException $exception) {
            $this->event->dispatch(new Error($config, $this, $exception));
            throw $exception;
        } catch (\Throwable $throwable) {
            $this->event->dispatch(new Error($config, $this, $throwable));
            throw $throwable;
        }
    }

    public function import(ImportConfig $config): importData
    {
        try {
            $importData = new ImportData(['token' => $config->getToken()]);
            $config->setTempPath($this->fileToTemp($config->getPath()));

            // 预检先行：若有 preCheckCallback 则先预检
            if ($this->shouldRunPreCheckBeforeImport($config)) {
                $preCheckResult = $this->importExcelPreCheck($config);
                $this->event->dispatch(new AfterPreCheck($config, $this, $preCheckResult));
                if ($preCheckResult->invalidRows > 0 || $preCheckResult->terminated) {
                    $this->setImportProgressFail($config, '预检失败', $preCheckResult);
                    Helper::deleteFile($config->getTempPath());
                    return $importData;
                }
            }

            $importData->sheetData = $this->importExcel($config);

            Helper::deleteFile($config->getTempPath());
        } catch (ExcelException $exception) {
            $this->event->dispatch(new Error($config, $this, $exception));
            throw $exception;
        } catch (\Throwable $throwable) {
            $this->event->dispatch(new Error($config, $this, $throwable));
            throw $throwable;
        }

        return $importData;
    }

    protected function shouldRunPreCheckBeforeImport(ImportConfig $config): bool
    {
        foreach ($config->getSheets() as $sheet) {
            if (is_callable($sheet->preCheckCallback ?? null)) {
                return true;
            }
        }
        return false;
    }

    protected function setImportProgressFail(ImportConfig $config, string $message, ?ImportPreCheckData $preCheckResult = null): void
    {
        if (!$config->getIsProgress()) {
            return;
        }
        $progress = $this->container->get(ProgressInterface::class);
        foreach ($config->getSheets() as $sheet) {
            $sheetMessage = $this->buildSheetPreCheckFailMessage($sheet->name, $message, $preCheckResult);
            $progress->setSheetProgress($config, $sheet->name, new ProgressData([
                'status' => ProgressData::PROGRESS_STATUS_FAIL,
                'message' => $sheetMessage,
            ]));
        }
        $progress->setProgress($config, new ProgressData([
            'status' => ProgressData::PROGRESS_STATUS_FAIL,
            'message' => $message,
        ]));
        $progress->pushMessage($config->getToken(), $message);
    }

    protected function buildSheetPreCheckFailMessage(string $sheetName, string $baseMessage, ?ImportPreCheckData $preCheckResult): string
    {
        if (!$preCheckResult || empty($preCheckResult->errors)) {
            return $baseMessage;
        }
        $sheetErrors = array_filter($preCheckResult->errors, fn($e) => ($e['sheetName'] ?? '') === $sheetName);
        if (empty($sheetErrors)) {
            return $baseMessage;
        }
        $details = array_map(fn($e) => sprintf('Row %d: %s', $e['rowIndex'] ?? 0, implode('; ', $e['errors'] ?? [])), $sheetErrors);
        return $baseMessage . ': ' . implode('; ', $details);
    }

    /**
     * 文件to临时文件
     *
     * @param $path
     * @return string
     * @throws ExcelException
     */
    protected function fileToTemp($path)
    {
        $filePath = $this->getTempFileName();

        if (!Helper::isUrl($path)) {
            // 本地文件
            if (!is_file($path)) {
                throw new ExcelException(sprintf('File not exists[%s]', $path));
            }
            if (!copy($path, $filePath)) {
                throw new ExcelException('File copy error');
            }
        } else {
            // 远程文件
            if (!Helper::downloadFile($path, $filePath)) {
                throw new ExcelException('File download error');
            }
        }
        return $filePath;
    }

    /**
     * 获取临时文件
     *
     * @return string
     * @throws ExcelException
     */
    public function getTempFileName(): string
    {
        if (!$filePath = Helper::getTempFileName($this->getTempDir(), 'ex_')) {
            throw new ExcelException('Failed to build temporary file');
        }
        return $filePath;
    }

    /**
     * 获取临时目录
     * 优先使用配置 temp_dir，null 则使用系统临时目录 sys_get_temp_dir()/laravel-excel
     *
     * @return string
     * @throws ExcelException
     */
    public function getTempDir(): string
    {
        $dir = $this->config['temp_dir'] ?? Helper::getTempDir() . DIRECTORY_SEPARATOR . 'laravel-excel';
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new ExcelException('Failed to build temporary directory: ' . $dir);
            }
        }
        return $dir;
    }

    /**
     * 导出数据回调
     *
     * @param callable $callback 回调
     * @param ExportConfig $config
     * @param ExportSheet $sheet
     * @param int $page 页码
     * @param int $pageSize 限制每页数量
     * @param int|null $totalCount
     * @return mixed
     */
    protected function exportDataCallback(callable $callback, ExportConfig $config, ExportSheet $sheet, int $page, int $pageSize, ?int $totalCount)
    {
        $exportCallbackParam = new ExportCallbackParam([
            'driver' => $this,
            'config' => $config,
            'sheet' => $sheet,

            'page' => $page,
            'pageSize' => $pageSize,
            'totalCount' => $totalCount,
        ]);

        $this->event->dispatch(new BeforeExportData($config, $this, $exportCallbackParam));

        $result = call_user_func($callback, $exportCallbackParam);

        $this->event->dispatch(new AfterExportData($config, $this, $exportCallbackParam, $result ?? []));

        return $result;
    }

    protected function exportSheetData(callable $writeDataFun, ExportSheet $sheet, ExportConfig $config, array $columns)
    {
        $totalCount = $sheet->getCount();
        $pageSize = $sheet->getPageSize();
        $data = $sheet->getData();

        $isCallback = is_callable($data);

        $page = 1;
        $pageNum = ceil($totalCount / $pageSize);

        do {
            $list = $dataCallback = $data;

            if (!$isCallback) {
                $totalCount = 0;
                $dataCallback = function () use (&$totalCount, $list) {
                    return $list;
                };
            }

            $list = $this->exportDataCallback($dataCallback, $config, $sheet, $page, min($totalCount, $pageSize), $totalCount);

            $listCount = count($list ?? []);

            if ($list) {
                $writeDataFun($sheet->formatList($list, $columns));
            }

            $isEnd = !$isCallback || $totalCount <= 0 || $totalCount <= $pageSize || ($listCount < $pageSize || $pageNum <= $page);

            $page++;
        } while (!$isEnd);

    }

    /**
     * 导入行回调
     *
     * @param callable $callback
     * @param ImportConfig $config
     * @param ImportSheet $sheet
     * @param array $row
     * @param int $rowIndex
     * @return mixed|null
     */
    protected function importRowCallback(callable $callback, ImportConfig $config, ImportSheet $sheet, array $row, int $rowIndex)
    {
        $importRowCallbackParam = new ImportRowCallbackParam([
            'driver' => $this,
            'sheet' => $sheet,
            'config' => $config,
            'row' => $row,
            'rowIndex' => $rowIndex,
        ]);

        $this->event->dispatch(new BeforeImportData($config, $this, $importRowCallbackParam));
        $exception = null;
        try {
            $result = call_user_func($callback, $importRowCallbackParam);
        } catch (\Throwable $throwable) {
            $exception = $throwable;
        }
        $this->event->dispatch(new AfterImportData($config, $this, $importRowCallbackParam, $exception));

        return $result ?? null;
    }

    protected function invokePreCheckCallback(callable $callback, ImportConfig $config, ImportSheet $sheet, array $row, int $rowIndex): bool
    {
        $param = new ImportRowCallbackParam([
            'driver' => $this,
            'sheet' => $sheet,
            'config' => $config,
            'row' => $row,
            'rowIndex' => $rowIndex,
        ]);

        $this->event->dispatch(new \BusinessG\LaravelExcel\Event\BeforePreCheckData($config, $this, $param));
        $exception = null;
        $terminate = false;
        try {
            $args = [$param, &$terminate];
            call_user_func_array($callback, $args);
        } catch (\Throwable $throwable) {
            $exception = $throwable;
        }
        $this->event->dispatch(new AfterPreCheckData($config, $this, $param, $exception));

        if ($exception) {
            throw $exception;
        }
        return !$terminate;
    }

    /**
     * 导出文件输出
     *
     * @param ExportConfig $config
     * @param string $filePath
     * @return string|StreamedResponse
     * @throws ExcelException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function exportOutPut(ExportConfig $config, string $filePath): string|StreamedResponse
    {
        $path = $this->buildExportPath($config);
        $fileName = basename($path);
        switch ($config->outPutType) {
            // 上传
            case ExportConfig::OUT_PUT_TYPE_UPLOAD:
                try {
                    $this->filesystem->writeStream($path, fopen($filePath, 'r+'));
                    $this->deleteFile($filePath);
                } catch (\Throwable $throwable) {
                    throw new ExcelException('File upload failed:' . $throwable->getMessage() . ',' . get_class($throwable));
                }
                if (!$this->filesystem->fileExists($path)) {
                    throw new ExcelException('File upload failed');
                }

                return $path;
                break;
            // 直接输出
            case ExportConfig::OUT_PUT_TYPE_OUT:
                $response = new StreamedResponse(function () use ($filePath) {
                    $stream = fopen($filePath, 'r');
                    fpassthru($stream);
                    fclose($stream);
                    $this->deleteFile($filePath);
                });
                $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                $response->headers->set('Content-Disposition', 'attachment;filename="' . rawurlencode($fileName) . '"');
                $response->headers->set('Content-Length', (string) filesize($filePath));
                $response->headers->set('Content-Transfer-Encoding', 'binary');
                $response->headers->set('Cache-Control', 'must-revalidate');
                $response->headers->set('Cache-Control', 'max-age=0');
                $response->headers->set('Pragma', 'public');
                return $response;
                break;
            default:
                throw new ExcelException('outPutType error');
        }
    }

    protected function deleteFile($filePath)
    {
        if (file_exists($filePath)) {
            Helper::deleteFile($filePath);
        }
    }

    /**
     * 构建导出地址
     *
     * @param ExportConfig $config
     * @return string
     */
    protected function buildExportPath(ExportConfig $config)
    {
        $strategy = $this->container->get(ExportPathStrategyInterface::class);
        return implode(DIRECTORY_SEPARATOR, array_filter([
            $this->config['export']['rootDir'] ?? null,
            $strategy->getPath($config),
        ]));
    }

    /**
     * 获取配置
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    abstract function exportExcel(ExportConfig $config, string $filePath): string;

    abstract function importExcel(ImportConfig $config): array|null;

    abstract function importExcelPreCheck(ImportConfig $config): ImportPreCheckData;

    public function importPreCheck(ImportConfig $config): ImportPreCheckData
    {
        $config->setTempPath($this->fileToTemp($config->getPath()));
        try {
            $result = $this->importExcelPreCheck($config);
            Helper::deleteFile($config->getTempPath());
            return $result;
        } catch (ExcelException $exception) {
            Helper::deleteFile($config->getTempPath());
            throw $exception;
        } catch (\Throwable $throwable) {
            Helper::deleteFile($config->getTempPath());
            throw $throwable;
        }
    }
}
