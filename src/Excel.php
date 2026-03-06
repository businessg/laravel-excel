<?php

namespace BusinessG\LaravelExcel;

use BusinessG\LaravelExcel\Data\BaseConfig;
use BusinessG\LaravelExcel\Data\Export\ExportConfig;
use BusinessG\LaravelExcel\Data\Export\ExportData;
use BusinessG\LaravelExcel\Data\Import\ImportConfig;
use BusinessG\LaravelExcel\Data\Import\ImportData;
use BusinessG\LaravelExcel\Driver\DriverFactory;
use BusinessG\LaravelExcel\Driver\DriverInterface;
use BusinessG\LaravelExcel\Event\AfterExport;
use BusinessG\LaravelExcel\Event\AfterImport;
use BusinessG\LaravelExcel\Event\BeforeExport;
use BusinessG\LaravelExcel\Event\BeforeImport;
use BusinessG\LaravelExcel\Event\Error;
use BusinessG\LaravelExcel\Exception\ExcelException;
use BusinessG\LaravelExcel\Progress\ProgressData;
use BusinessG\LaravelExcel\Progress\ProgressInterface;
use BusinessG\LaravelExcel\Progress\ProgressRecord;
use BusinessG\LaravelExcel\Queue\ExcelQueueInterface;
use BusinessG\LaravelExcel\Strategy\Token\TokenStrategyInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class Excel implements ExcelInterface
{
    public EventDispatcherInterface $event;
    protected array $config;

    public function __construct(protected ContainerInterface $container, protected ProgressInterface $progress)
    {
        $this->config = config('excel', []);
        $this->event = $container->get(EventDispatcherInterface::class);
    }

    public function export(ExportConfig $config): ExportData
    {
        if (empty($config->getToken())) {
            $config->setToken($this->buildToken());
        }
        $driver = $this->getDriver($config->getDriverName());
        $exportData = new ExportData(['token' => $config->getToken()]);

        try {

            $this->event->dispatch(new BeforeExport($config, $driver));

            if ($config->getIsAsync()) {
                if ($config->getOutPutType() == ExportConfig::OUT_PUT_TYPE_OUT) {
                    throw new ExcelException('Async does not support output type ExportConfig::OUT_PUT_TYPE_OUT');
                }
                $this->pushQueue($config);
                return $exportData;
            }

            $exportData = $driver->export($config);

            $this->event->dispatch(new AfterExport($config, $driver, $exportData));

            return $exportData;

        } catch (ExcelException $exception) {
            $this->event->dispatch(new Error($config, $driver, $exception));
            throw $exception;
        } catch (\Throwable $throwable) {
            $this->event->dispatch(new Error($config, $driver, $throwable));
            throw $throwable;
        }
    }

    public function import(ImportConfig $config): ImportData
    {
        if (empty($config->getToken())) {
            $config->setToken($this->buildToken());
        }
        $importData = new ImportData(['token' => $config->getToken()]);
        $driver = $this->getDriver($config->getDriverName());

        try {
            $this->event->dispatch(new BeforeImport($config, $driver));
            if ($config->getIsAsync()) {
                if ($config->isReturnSheetData) {
                    throw new ExcelException('Asynchronous does not support returning sheet data');
                }
                $this->pushQueue($config);
                return $importData;
            }

            $importData = $driver->import($config);

            $this->event->dispatch(new AfterImport($config, $driver, $importData));

            return $importData;

        } catch (ExcelException $exception) {
            $this->event->dispatch(new Error($config, $driver, $exception));
            throw $exception;
        } catch (\Throwable $throwable) {
            $this->event->dispatch(new Error($config, $driver, $throwable));
            throw $throwable;
        }
    }

    public function getProgressRecord(string $token): ?ProgressRecord
    {
        return $this->progress->getRecordByToken($token);
    }

    public function popMessage(string $token, int $num = 50): array
    {
        return $this->progress->popMessage($token, $num);
    }

    public function pushMessage(string $token, string $message)
    {
        return $this->progress->pushMessage($token, $message);
    }

    public function popMessageAndIsEnd(string $token, int $num = 50, bool &$isEnd = true): array
    {
        $progressRecord = $this->getProgressRecord($token);
        $messages = $this->popMessage($token, $num);
        $isEnd = $this->isEnd($progressRecord) && empty($messages);
        return $messages;
    }

    public function isEnd(?ProgressRecord $progressRecord): bool
    {
        return empty($progressRecord) || in_array($progressRecord->progress->status, [
                ProgressData::PROGRESS_STATUS_COMPLETE,
                ProgressData::PROGRESS_STATUS_FAIL,
            ]);
    }

    public function getDefaultDriver(): DriverInterface
    {
        return $this->container->get(DriverInterface::class);
    }

    public function getDriverByName(string $driverName): DriverInterface
    {
        return $this->container->get(DriverFactory::class)->get($driverName);
    }

    public function getDriver(?string $driverName = null): DriverInterface
    {
        $driver = $this->getDefaultDriver();
        if (!empty($driverName)) {
            $driver = $this->getDriverByName($driverName);
        }
        return $driver;
    }

    /**
     * 推送队列
     *
     * @param BaseConfig $config
     * @return bool
     */
    protected function pushQueue(BaseConfig $config): bool
    {
        return $this->container->get(ExcelQueueInterface::class)->push($config);
    }

    /**
     * token
     *
     * @return string
     */
    protected function buildToken(): string
    {
        return $this->container->get(TokenStrategyInterface::class)->getToken();
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
