<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Driver;

use BusinessG\BaseExcel\Data\Export\ExportConfig;
use BusinessG\BaseExcel\Driver\XlsWriterDriver as BaseXlsWriterDriver;
use BusinessG\LaravelExcel\LaravelEventDispatcherAdapter;
use BusinessG\BaseExcel\Exception\ExcelException;
use BusinessG\BaseExcel\Helper\Helper;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemOperator;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Laravel 适配：继承 base-excel XlsWriterDriver，增加 Storage、StreamedResponse 支持
 */
class XlsWriterDriver extends BaseXlsWriterDriver
{
    public function __construct(ContainerInterface $container, array $config, string $name)
    {
        $event = $container->has(EventDispatcherInterface::class)
            ? $container->get(EventDispatcherInterface::class)
            : new LaravelEventDispatcherAdapter($container->get(\Illuminate\Contracts\Events\Dispatcher::class));

        $storage = Storage::disk($config['filesystem']['storage'] ?? 'local');
        /** @var FilesystemOperator $filesystem */
        $filesystem = $storage->getDriver();

        parent::__construct($container, $config, $name, $event, $filesystem);
    }

    protected function exportOutPut(ExportConfig $config, string $filePath): string|StreamedResponse
    {
        $path = $this->buildExportPath($config);
        $fileName = basename($path);

        switch ($config->outPutType) {
            case ExportConfig::OUT_PUT_TYPE_UPLOAD:
                return $this->uploadToStorage($filePath, $path);

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

            default:
                throw new ExcelException('outPutType error');
        }
    }

    public function getTempDir(): string
    {
        $dir = ($this->config['temp_dir'] ?? null) ?: Helper::getTempDir() . DIRECTORY_SEPARATOR . 'laravel-excel';
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new ExcelException('Failed to build temporary directory: ' . $dir);
            }
        }

        return $dir;
    }
}
