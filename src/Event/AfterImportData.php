<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Event;

use BusinessG\LaravelExcel\Data\BaseConfig;
use BusinessG\LaravelExcel\Data\Import\ImportRowCallbackParam;
use BusinessG\LaravelExcel\Driver\Driver;

class AfterImportData extends Event
{
    public function __construct(public BaseConfig $config, public Driver $driver, public ImportRowCallbackParam $importCallbackParam, public ?\Throwable $exception = null)
    {
        parent::__construct($config, $driver);
    }
}