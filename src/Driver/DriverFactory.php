<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Driver;

use BusinessG\BaseExcel\Driver\AbstractDriverFactory;
use BusinessG\BaseExcel\Driver\DriverInterface;

class DriverFactory extends AbstractDriverFactory
{
    protected function getConfigValue(string $key, mixed $default = null): mixed
    {
        return config($key, $default);
    }

    protected function makeDriver(string $class, array $params): DriverInterface
    {
        return app()->make($class, $params);
    }
}
