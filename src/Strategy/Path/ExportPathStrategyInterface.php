<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Strategy\Path;

use BusinessG\LaravelExcel\Data\Export\ExportConfig;

interface ExportPathStrategyInterface
{
    public function getPath(ExportConfig $config, string $fileExt = 'xlsx'): string;
}