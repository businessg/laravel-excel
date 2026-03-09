<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Listener;

use BusinessG\BaseExcel\Listener\ExcelLogDbListener as BaseExcelLogDbListener;

/**
 * Laravel DB 日志监听器，直接使用 base-excel 实现
 */
class ExcelLogDbListener extends BaseExcelLogDbListener
{
}
