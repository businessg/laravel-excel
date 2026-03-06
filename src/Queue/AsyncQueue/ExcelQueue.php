<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Queue\AsyncQueue;

use BusinessG\LaravelExcel\Data\BaseConfig;
use BusinessG\LaravelExcel\Data\Export\ExportConfig;
use BusinessG\LaravelExcel\Queue\AsyncQueue\Job\ExportJob;
use BusinessG\LaravelExcel\Queue\AsyncQueue\Job\ImportJob;
use BusinessG\LaravelExcel\Queue\ExcelQueueInterface;
use Psr\Container\ContainerInterface;

class ExcelQueue implements ExcelQueueInterface
{
    protected array $config;

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = config('excel.queue', []);
    }

    public function push(BaseConfig $config)
    {
        $job = $config instanceof ExportConfig ? new ExportJob($config) : new ImportJob($config);
        $connection = $this->config['connection'] ?? $this->config['name'] ?? null;
        if ($connection) {
            $job->onConnection($connection);
        }
        dispatch($job);
        return true;
    }
}
