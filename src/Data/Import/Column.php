<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Data\Import;

use BusinessG\LaravelExcel\Data\BaseObject;

/**
 * 列配置
 */
class Column extends BaseObject
{
    public const TYPE_STRING = 0x01;    // 字符串
    public const TYPE_INT = 0x02;       // 整型
    public const TYPE_DOUBLE = 0x04;    // 浮点型
    public const TYPE_TIMESTAMP = 0x08; // 时间戳，可以将 xlsx 文件中的格式化时间字符转为时间戳

    /**
     * 列标题
     *
     * @var string
     */
    public string $title;

    /**
     * 数据类型
     *
     * @var int
     */
    public int $type = self::TYPE_STRING;
    /**
     * 字段名
     *
     * @var string
     */
    public string $field = '';

}