<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Event;

use BusinessG\LaravelExcel\Data\BaseConfig;
use BusinessG\LaravelExcel\Data\Export\ExportData;
use BusinessG\LaravelExcel\Driver\Driver;

class AfterExportOutput extends Event
{
    public function __construct(public BaseConfig $config, public Driver $driver, public ExportData $data)
    {
    }
}