<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Event;

use BusinessG\LaravelExcel\Data\BaseConfig;
use BusinessG\LaravelExcel\Data\Export\Sheet;
use BusinessG\LaravelExcel\Driver\Driver;

class BeforeExportSheet extends Event
{
    public function __construct(public BaseConfig $config, public Driver $driver, public Sheet $sheet)
    {
        parent::__construct($config, $driver);
    }

}