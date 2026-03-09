<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Queue\AsyncQueue\Job;

use BusinessG\BaseExcel\Data\BaseConfig;
use BusinessG\LaravelExcel\Driver\DriverFactory;
use BusinessG\BaseExcel\Event\Error;
use BusinessG\LaravelExcel\ExcelInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

abstract class BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public BaseConfig $config;
    public int $tries = 3;

    public function __construct(BaseConfig $config)
    {
        $this->config = $config;
    }

    protected function getExcel(): ExcelInterface
    {
        return app(ExcelInterface::class);
    }

    public function failed(Throwable $e): void
    {
        $excel = $this->getExcel();
        $driver = $excel->getDriver($this->config->getDriverName());
        $excel->event->dispatch(new Error($this->config, $driver, $e));
    }

    abstract function handle();
}
