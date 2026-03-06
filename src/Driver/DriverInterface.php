<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Driver;

use BusinessG\LaravelExcel\Data\Export\ExportConfig;
use BusinessG\LaravelExcel\Data\Export\ExportData;
use BusinessG\LaravelExcel\Data\Import\ImportConfig;
use BusinessG\LaravelExcel\Data\Import\ImportData;

interface DriverInterface
{
    public function export(ExportConfig $config): ExportData;

    public function import(ImportConfig $config): ImportData;

    public function getConfig(): array;

    public function getTempDir(): string;

    public function getTempFileName():string;

}