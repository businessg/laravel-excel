<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Event;

use BusinessG\LaravelExcel\Data\BaseConfig;
use BusinessG\LaravelExcel\Driver\Driver;

class Error extends Event
{
    public function __construct(public BaseConfig $config, public Driver $driver, public \Throwable $exception)
    {
        parent::__construct($config, $driver);
    }
}