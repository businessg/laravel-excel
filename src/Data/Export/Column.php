<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Data\Export;

use BusinessG\LaravelExcel\Data\BaseObject;

/**
 * 列配置
 */
class Column extends BaseObject
{

    /**
     * 列标识
     *
     * @var string
     */
    public string $key;

    /**
     * 列标题
     *
     * @var string
     */
    public string $title;

    /**
     * 数据类型
     *
     * @var string
     */
    public string $type;
    /**
     * 字段名
     *
     * @var string
     */
    public string $field;

    /**
     * 数据回调
     *
     * @var Callback
     *
     * `
     *      function($row){
     *          return $row['title'];
     *      }
     * `
     *
     */
    public $callback = null;

    /**
     * 设置列宽
     *
     * @var int
     */
    public int $width = 0;

    /**
     * 设置行高度
     *
     * @var int
     */
    public int $height = 0;


    /**
     * 当前所在列数（0开始）
     *
     * @var int
     */
    public int $col = 0;
    /**
     * 当前所在行数（0开始）
     *
     * @var int
     */
    public int $row = 0;
    /**
     * 跨行数
     *
     * @var int
     */
    public int $rowSpan = 0;
    /**
     * 跨列数
     *
     * @var int
     */
    public int $colSpan = 0;


    /**
     * 样式
     *
     * @var null|Style
     */
    public ?Style $style = null;

    /**
     * header样式
     *
     * @var Style|null
     */
    public ?Style $headerStyle = null;

    /**
     * 子列
     *
     * @var Column[]
     */
    public array $children = [];


    public bool $hasChildren = false;

    /**
     * 额外配置
     *
     * @var array
     */
    public array $options = [];


    /**
     * 处理列结构并返回三个部分的数据
     *
     * @param Column[] $columns 列结构数组
     * @return array
     */
    public static function processColumns(array $columns): array
    {
        $maxDepth = static::calculateMaxDepth($columns);

        $result = static::processColumnsRecursive($columns, 0, $maxDepth - 1, 0);

        return [
            $result['leafNodes'], // 叶子节点列集合
            $result['fullStructure'], // 完整列结构数据<扁平化>
            $maxDepth,// 最大深度值
        ];
    }

    /**
     * 递归处理列结构
     *
     * @param array $columns
     * @param int $startRow
     * @param int $endRow
     * @param int $startCol
     * @return array
     */
    protected static function processColumnsRecursive(array $columns, int $startRow, int $endRow, int $startCol): array
    {
        $leafNodes = [];
        $fullStructure = [];
        $currentCol = $startCol;

        foreach ($columns as $column) {
            $hasChildren = !empty($column->children);
            $rowSpan = $hasChildren ? 1 : ($endRow - $startRow + 1);
            $colSpan = $hasChildren ? static::countLeafColumns($column->children) : 1;

            $columnInfo = new Column([
                'title' => $column->title,
                'field' => $column->field,
                'row' => $startRow,
                'width' => $column->width,
                'height' => $column->height,
                'col' => $currentCol,
                'rowSpan' => $rowSpan,
                'colSpan' => $colSpan,
                'style' => $column->style ?? null,
                'headerStyle' => $column->headerStyle ?? null,
                'hasChildren' => $hasChildren,
            ]);

            // 添加到完整结构
            $fullStructure[] = $columnInfo;

            if ($hasChildren) {
                // 处理子列
                $childResult = static::processColumnsRecursive(
                    $column->children,
                    $startRow + 1,
                    $endRow,
                    $currentCol
                );

                $leafNodes = array_merge($leafNodes, $childResult['leafNodes']);
                $fullStructure = array_merge($fullStructure, $childResult['fullStructure']);
                $currentCol += $colSpan;
            } else {
                // 叶子节点添加到叶子节点集合
                $leafNodes[] = $columnInfo;
                $currentCol += $colSpan;
            }
        }

        return [
            'leafNodes' => $leafNodes,
            'fullStructure' => $fullStructure
        ];
    }

    /**
     * 计算列结构的最大深度
     *
     * @param array $columns
     * @return int
     */
    protected static function calculateMaxDepth(array $columns): int
    {
        $maxDepth = 1;
        foreach ($columns as $column) {
            if (!empty($column->children)) {
                $childDepth = static::calculateMaxDepth($column->children) + 1;
                $maxDepth = max($maxDepth, $childDepth);
            }
        }
        return $maxDepth;
    }

    /**
     * 计算叶子列的数量
     *
     * @param array $columns
     * @return int
     */
    protected static function countLeafColumns(array $columns): int
    {
        $count = 0;
        foreach ($columns as $column) {
            if (empty($column->children)) {
                $count++;
            } else {
                $count += static::countLeafColumns($column->children);
            }
        }
        return $count;
    }

}