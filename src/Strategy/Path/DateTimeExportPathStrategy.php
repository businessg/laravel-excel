<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Strategy\Path;

use BusinessG\LaravelExcel\Data\Export\ExportConfig;

class DateTimeExportPathStrategy implements ExportPathStrategyInterface
{

    public function getPath(ExportConfig $config, string $fileExt = 'xlsx'): string
    {
        return $config->getServiceName() . '_' . date('YmdHis') . '.' . $fileExt;
    }
}