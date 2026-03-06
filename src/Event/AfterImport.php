<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Event;

use BusinessG\LaravelExcel\Data\BaseConfig;
use BusinessG\LaravelExcel\Data\Import\ImportData;
use BusinessG\LaravelExcel\Driver\Driver;

class AfterImport extends Event
{

    public function __construct(public BaseConfig $config, public Driver $driver,public ImportData $data)
    {
    }
}