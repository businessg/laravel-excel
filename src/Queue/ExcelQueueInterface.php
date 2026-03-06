<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Queue;

use BusinessG\LaravelExcel\Data\BaseConfig;

interface ExcelQueueInterface
{
    public function push(BaseConfig $config);
}