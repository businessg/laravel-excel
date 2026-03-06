<?php

namespace BusinessG\LaravelExcel;

use BusinessG\LaravelExcel\Driver\DriverFactory;
use Psr\Container\ContainerInterface;

class ExcelInvoker
{
    public function __invoke(ContainerInterface $container)
    {
        $name = config('excel.default', 'xlswriter');
        $factory = $container->get(DriverFactory::class);
        return $factory->get($name);
    }
}
