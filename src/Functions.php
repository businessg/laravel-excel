<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel;

use BusinessG\LaravelExcel\Data\Export\ExportConfig;
use BusinessG\LaravelExcel\Data\Export\ExportData;
use BusinessG\LaravelExcel\Data\Import\ImportConfig;
use BusinessG\LaravelExcel\Data\Import\ImportData;
use BusinessG\LaravelExcel\Progress\ProgressRecord;
use RuntimeException;

function excel_export(ExportConfig $config): ExportData
{
    $container = app();

    if (!$container->has(ExcelInterface::class)) {
        throw new RuntimeException('ExcelInterface is missing in container.');
    }

    return $container->get(ExcelInterface::class)->export($config);
}

function excel_import(ImportConfig $config): ImportData
{
    $container = app();

    if (!$container->has(ExcelInterface::class)) {
        throw new RuntimeException('ExcelInterface is missing in container.');
    }
    return $container->get(ExcelInterface::class)->import($config);
}

function excel_progress_pop_message(string $token, int $num = 50, bool &$isEnd = true): array
{
    $container = app();

    if (!$container->has(ExcelInterface::class)) {
        throw new RuntimeException('ExcelInterface is missing in container.');
    }
    return $container->get(ExcelInterface::class)->popMessageAndIsEnd($token, $num, $isEnd);
}

function excel_progress_push_message(string $token, string $message)
{
    $container = app();

    if (!$container->has(ExcelInterface::class)) {
        throw new RuntimeException('ExcelInterface is missing in container.');
    }
    return $container->get(ExcelInterface::class)->pushMessage($token, $message);
}

function excel_progress(string $token): ?ProgressRecord
{
    $container = app();

    if (!$container->has(ExcelInterface::class)) {
        throw new RuntimeException('ExcelInterface is missing in container.');
    }
    return $container->get(ExcelInterface::class)->getProgressRecord($token);
}
