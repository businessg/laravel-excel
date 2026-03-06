<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Logger;

use Psr\Log\LoggerInterface;

interface ExcelLoggerInterface
{
    public function getLogger(): LoggerInterface;
}