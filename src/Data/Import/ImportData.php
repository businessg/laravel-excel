<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Data\Import;

use BusinessG\LaravelExcel\Data\BaseObject;

/**
 * 导入数据
 */
class ImportData extends BaseObject
{
    public string $token = '';

    /**
     * 页码数据
     *
     * @var array
     */
    public array $sheetData = [];
}