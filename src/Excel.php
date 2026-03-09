<?php

namespace BusinessG\LaravelExcel;

use BusinessG\BaseExcel\AbstractExcel;
use BusinessG\LaravelExcel\Driver\DriverFactory;
use Illuminate\Contracts\Events\Dispatcher as EventsDispatcher;

class Excel extends AbstractExcel implements \BusinessG\BaseExcel\ExcelInterface
{
    protected function resolveConfig(): array
    {
        return config('excel', []);
    }

    protected function resolveEventDispatcher(): object
    {
        return $this->container->get(EventsDispatcher::class);
    }

    protected function getDriverFactoryClass(): string
    {
        return DriverFactory::class;
    }
}
