<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Data\Import;

use BusinessG\LaravelExcel\Data\BaseObject;

/**
 * 导入预检结果
 */
class ImportPreCheckData extends BaseObject
{
    public bool $passed = true;
    public int $totalRows = 0;
    public int $validRows = 0;
    public int $invalidRows = 0;
    /** @var array 格式: [['sheetName'=>string,'rowIndex'=>int,'row'=>array,'errors'=>string[]], ...] */
    public array $errors = [];
    public array $sampleData = [];
    public bool $terminated = false;
}
