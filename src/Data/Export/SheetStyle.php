<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Data\Export;

use BusinessG\LaravelExcel\Data\BaseObject;

class SheetStyle extends BaseObject
{
    // 网格线
    const GRIDLINES_HIDE_ALL = 0; // 隐藏 屏幕网格线 和 打印网格线
    const GRIDLINES_SHOW_SCREEN = 1; // 显示屏幕网格线
    const GRIDLINES_SHOW_PRINT = 2; // 显示打印网格线
    const GRIDLINES_SHOW_ALL = 3; // 显示 屏幕网格线 和 打印网格线

    public ?int $gridline = null; // 网格线
    public ?int $zoom = null; // 缩放
    public bool $hide = false; // 是否隐藏
    public bool $isFirst = false; // 是否为选中

    public function setGridline(int $gridline)
    {
        $this->gridline = $gridline;
        return $this;
    }

    public function setZoom(int $zoom)
    {
        $this->zoom = $zoom;
        return $this;
    }

    public function setHide(bool $isHide)
    {
        $this->hide = $isHide;
        return $this;
    }

    public function setIsFirst(bool $isFirst)
    {
        $this->isFirst = $isFirst;
        return $this;
    }
}