<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Queue\AsyncQueue;

use BusinessG\BaseExcel\Config\ExcelConfig;
use BusinessG\BaseExcel\Data\BaseConfig;
use BusinessG\BaseExcel\Data\Export\ExportConfig;
use BusinessG\BaseExcel\Queue\ExcelQueueInterface;
use BusinessG\LaravelExcel\Queue\AsyncQueue\Job\ExportJob;
use BusinessG\LaravelExcel\Queue\AsyncQueue\Job\ImportJob;
use Psr\Container\ContainerInterface;

class ExcelQueue implements ExcelQueueInterface
{
    protected ExcelConfig $excelConfig;

    public function __construct(protected ContainerInterface $container)
    {
        $this->excelConfig = ExcelConfig::fromArray(config('excel', []));
    }

    public function push(BaseConfig $config): void
    {
        $job = $config instanceof ExportConfig ? new ExportJob($config) : new ImportJob($config);
        if ($this->excelConfig->queue->tries !== null) {
            $job->tries = $this->excelConfig->queue->tries;
        }

        $connection = $this->excelConfig->queue->connection;
        if ($connection) {
            $job->onConnection($connection);
        }

        $channel = $this->excelConfig->queue->channel;
        if ($channel && $channel !== 'default') {
            $job->onQueue($channel);
        }

        dispatch($job);
    }
}
