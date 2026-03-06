<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Event;

use BusinessG\LaravelExcel\Data\BaseConfig;
use BusinessG\LaravelExcel\Data\Export\ExportCallbackParam;
use BusinessG\LaravelExcel\Driver\Driver;

class BeforeExportData extends Event
{

    public function __construct(public BaseConfig $config, public Driver $driver, public ExportCallbackParam $exportCallbackParam)
    {
        parent::__construct($config, $driver);
    }
}