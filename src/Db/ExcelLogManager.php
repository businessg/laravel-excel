<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Db;

use BusinessG\BaseExcel\Db\AbstractExcelLogManager;
use BusinessG\LaravelExcel\Db\Model\ExcelLog as ExcelLogModel;

class ExcelLogManager extends AbstractExcelLogManager
{
    protected function resolveConfig(): array
    {
        return config('excel.dbLog', [
            'enable' => true,
            'model' => ExcelLogModel::class,
        ]);
    }

    protected function getDefaultModelClass(): string
    {
        return ExcelLogModel::class;
    }
}
