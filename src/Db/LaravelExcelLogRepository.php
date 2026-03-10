<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Db;

use BusinessG\BaseExcel\Config\ExcelConfig;
use BusinessG\BaseExcel\Db\ExcelLogRepositoryInterface;

class LaravelExcelLogRepository implements ExcelLogRepositoryInterface
{
    private ?string $modelClass;

    public function __construct()
    {
        $excelConfig = ExcelConfig::fromArray(config('excel', []));
        $this->modelClass = $excelConfig->dbLog->model;
    }

    public function upsert(array $data): int
    {
        if (!$this->modelClass) {
            return 0;
        }
        return $this->modelClass::query()->upsert([$data], ['token']);
    }
}
