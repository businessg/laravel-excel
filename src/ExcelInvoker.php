<?php

namespace BusinessG\LaravelExcel;

use BusinessG\BaseExcel\ExcelInvoker as BaseExcelInvoker;
use BusinessG\LaravelExcel\Driver\DriverFactory;
use Psr\Container\ContainerInterface;

class ExcelInvoker extends BaseExcelInvoker
{
    protected function getDefaultDriverName(ContainerInterface $container): string
    {
        return (string) config('excel.default', 'xlswriter');
    }

    protected function getDriverFactoryClass(): string
    {
        return DriverFactory::class;
    }
}
