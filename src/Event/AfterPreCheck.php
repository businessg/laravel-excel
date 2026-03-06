<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Event;

use BusinessG\LaravelExcel\Data\BaseConfig;
use BusinessG\LaravelExcel\Data\Import\ImportPreCheckData;
use BusinessG\LaravelExcel\Driver\Driver;

class AfterPreCheck extends Event
{
    public function __construct(public BaseConfig $config, public Driver $driver, public ImportPreCheckData $data)
    {
        parent::__construct($config, $driver);
    }
}
