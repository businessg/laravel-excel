<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Event;

use BusinessG\LaravelExcel\Data\BaseConfig;
use BusinessG\LaravelExcel\Driver\Driver;

class BeforePreCheck extends Event
{
    public function __construct(public BaseConfig $config, public Driver $driver)
    {
        parent::__construct($config, $driver);
    }
}
