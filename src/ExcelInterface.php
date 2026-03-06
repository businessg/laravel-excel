<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel;

use BusinessG\LaravelExcel\Data\Export\ExportConfig;
use BusinessG\LaravelExcel\Data\Export\ExportData;
use BusinessG\LaravelExcel\Data\Import\ImportConfig;
use BusinessG\LaravelExcel\Data\Import\ImportData;
use BusinessG\LaravelExcel\Data\Import\ImportPreCheckData;
use BusinessG\LaravelExcel\Driver\DriverInterface;
use BusinessG\LaravelExcel\Progress\ProgressRecord;

interface ExcelInterface
{
    public function export(ExportConfig $config): ExportData;

    public function import(ImportConfig $config): ImportData;

    public function importPreCheck(ImportConfig $config): ImportPreCheckData;

    public function getProgressRecord(string $token): ?ProgressRecord;

    public function popMessage(string $token, int $num = 50): array;

    public function popMessageAndIsEnd(string $token, int $num = 50, bool &$isEnd = true): array;

    public function pushMessage(string $token, string $message);

    public function getDefaultDriver(): DriverInterface;

    public function getDriverByName(string $driverName): DriverInterface;

    public function getDriver(?string $driverName = null): DriverInterface;

    public function getConfig(): array;

}