<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Logger;

use BusinessG\BaseExcel\Logger\AbstractExcelLogger;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

class ExcelLogger extends AbstractExcelLogger
{
    protected function resolveConfig(): array
    {
        return config('excel.logger', ['name' => 'stack']);
    }

    protected function resolveLogger(): LoggerInterface
    {
        return Log::channel($this->config['name'] ?? 'stack');
    }
}
