<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Data\Export;

use BusinessG\LaravelExcel\Data\BaseObject;

/**
 * excel页
 */
class Sheet extends BaseObject
{
    /**
     * sheet名称
     *
     * @var string
     */
    public string $name = 'sheet1';

    /**
     * 列头配置
     *
     * @var Column[]
     */
    public array $columns = [];

    /**
     * 数据总数量
     *
     * @var int
     */
    public int $count = 0;

    /**
     * 分页导出时/每页的数据量
     *
     * @var int
     */
    public int $pageSize = 2000;

    /**
     * 页码样式
     *
     * @var null|SheetStyle
     */
    public ?SheetStyle $style = null;

    /**
     * 数据
     *   Closure 数据回调
     *   `
     *      function(ExportCallbackParam $callbackParam){
     *         // 执行业务数据查询
     *      }
     *  `
     *  array. 数据
     * @var \Closure|array
     */
    public \Closure|array $data = [];

    /**
     * 额外配置
     *
     * @var array
     */
    public array $options = [];


    /**
     * 获取列配置
     *
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * 获取页码名
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * 获取数据
     *
     * @return array|\Closure
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 获取数据数量
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * 获取每页导出数量
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;

    }

    /**
     * 获取头部信息
     *
     * @param Column[] $columns
     * @return array|string[]
     */
    public function getHeaders(array $columns)
    {
        return array_map(function (Column $col) {
            return $col->title;
        }, $columns);
    }

    /**
     * 格式行数据
     *
     * @param $row
     * @param array $columns
     * @return Column[]
     */
    public function formatRow($row,array $columns)
    {
        $newRow = [];
        foreach ($columns as $column) {
            $newRow[$column->field] = $row[$column->field] ?? '';
            if (is_callable($column->callback)) {
                $newRow[$column->field] = call_user_func($column->callback, $row);
            }
        }
        return $newRow;
    }

    /**
     * 格式化多行数据
     *
     * @param $list
     * @param Column[] $columns
     * @return array
     */
    public function formatList($list, array $columns)
    {
        return array_map(function ($item) use ($columns) {
            return $this->formatRow($item, $columns);
        }, $list ?? []);
    }
}