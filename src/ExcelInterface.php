<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel;

use BusinessG\BaseExcel\Data\Export\ExportConfig;
use BusinessG\BaseExcel\Data\Export\ExportData;
use BusinessG\BaseExcel\Data\Import\ImportConfig;
use BusinessG\BaseExcel\Data\Import\ImportData;
use BusinessG\BaseExcel\Driver\DriverInterface;
use BusinessG\BaseExcel\Progress\ProgressRecord;

/**
 * Laravel Excel 接口，继承 base-excel 并扩展 peekMessage
 */
interface ExcelInterface extends \BusinessG\BaseExcel\ExcelInterface
{
    public function peekMessage(string $token, int $num = 50): array;
}