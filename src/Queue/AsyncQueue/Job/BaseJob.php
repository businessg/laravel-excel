<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Queue\AsyncQueue\Job;

use BusinessG\BaseExcel\Data\BaseConfig;
use BusinessG\BaseExcel\Queue\ExcelJobTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Psr\Container\ContainerInterface;
use Throwable;

abstract class BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, ExcelJobTrait;

    public int $tries = 3;

    public function __construct(BaseConfig $config)
    {
        $this->config = $config;
    }

    protected function getContainer(): ContainerInterface
    {
        return app();
    }

    public function failed(Throwable $e): void
    {
        $this->dispatchError($e);
    }

    abstract public function handle(): void;
}
