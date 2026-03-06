<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Data\Import;

use BusinessG\LaravelExcel\Data\BaseObject;
use BusinessG\LaravelExcel\Exception\ExcelException;

class Sheet extends BaseObject
{


    // 读取sheet下标/名称
    public const SHEET_READ_TYPE_NAME = 'name';
    public const SHEET_READ_TYPE_INDEX = 'index';

    /**
     * 读取类型
     *
     * @var string
     */
    public string $readType = self::SHEET_READ_TYPE_NAME;

    /**
     * 页下标
     *
     * @var int
     */
    public int $index = 0;

    /**
     * 页名
     *
     * @var string
     */
    public string $name = 'sheet1';

    /**
     * 列配置
     *
     * @var Column[]
     */
    public array $columns = [];

    /**
     * 列头数据行下标（从1开始）
     *      0 则不设置列头
     *
     * @var int
     */
    public int $headerIndex = 1;

    /**
     * 是否全量返回整页数据
     *
     * @var bool
     */
    public bool $isReturnSheetData = false;

    /**
     * 跳过空白行
     *
     * @var bool
     */
    public bool $skipEmptyRow = true;

    /**
     * 跳过指定行
     *
     * @var int
     */
    public bool $skipRowIndex = false;


    /**
     * 游标读取回调
     * `
     *    function($row){
     *          // 执行业务代码
     *    }
     * `
     * @var
     */
    public $callback;

    /**
     * 获取name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取列数据类型
     *
     * @param array $header
     * @return array
     */
    public function getColumnTypes(array $header = []): array
    {
        $types = [];
        $columnTypes = [];
        foreach ($this->columns as $column) {
            $columnTypes[$column->title] = $column->type ?: Column::TYPE_STRING;
        }
        $types = array_values($columnTypes);
        if (!empty($header)) {
            $types = array_map(function ($title) use ($columnTypes) {
                return $columnTypes[$title] ?? Column::TYPE_STRING;
            }, $header);
        }
        return $types;
    }

    /**
     * 格式化数据
     *
     * @param $sheetData
     * @param $header
     * @return array|array[]
     */
    public function formatSheetDataByHeader($sheetData, $header)
    {
        return array_map(function ($n) use ($header) {
            return $this->formatRowByHeader($n, $header);
        }, $sheetData);
    }

    /**
     * 格式化行数据
     *
     * @param $row
     * @param $header
     * @return array
     * @throws ExcelException
     */
    public function formatRowByHeader($row, $header)
    {
        $data = [];
        /**
         * @var  Column $column
         */
        if (!empty($this->columns)) {
            $header = array_flip($header);
            foreach ($this->columns as $k => $column) {
                $key = $column->field ?: $column->title;
                if ($header && !isset($header[$column->title])) {
                    throw new ExcelException("The corresponding column header does not exist for [{$column->title}]");
                }
                $headerKey = $column->title ? ($header[$column->title] ?? $k) : $k;
                $value = $row[$headerKey] ?? null;
                if (!empty($key)) {
                    $data[$key] = $value;
                } else {
                    $data[] = $row[$headerKey] ?? null;
                }
            }
        } else {
            $data = $header ? array_combine($header, $row) : $row;
        }
        return $data;
    }


    /**
     * 校验header头正确性
     *
     * @param array $header
     * @return void
     * @throws ExcelException
     */
    public function validateHeader(array $header = [])
    {
        foreach ($this->columns as $column) {
            if (!in_array($column->title, $header)) {
                throw  new ExcelException("The column header does not exist in [{$column->title}]");
            }
        }
    }
}