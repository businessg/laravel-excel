<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Data\Export;

use BusinessG\LaravelExcel\Data\BaseObject;


class Style extends BaseObject
{
    // 对齐
    public const FORMAT_ALIGN_LEFT = 1;
    public const FORMAT_ALIGN_CENTER = 2;
    public const FORMAT_ALIGN_RIGHT = 3;
    public const FORMAT_ALIGN_FILL = 4;
    public const FORMAT_ALIGN_JUSTIFY = 5;
    public const FORMAT_ALIGN_CENTER_ACROSS = 6;
    public const FORMAT_ALIGN_DISTRIBUTED = 7;
    public const FORMAT_ALIGN_VERTICAL_TOP = 8;
    public const FORMAT_ALIGN_VERTICAL_BOTTOM = 9;
    public const FORMAT_ALIGN_VERTICAL_CENTER = 10;
    public const FORMAT_ALIGN_VERTICAL_JUSTIFY = 11;
    public const FORMAT_ALIGN_VERTICAL_DISTRIBUTED = 12;

    // 下划线
    public const UNDERLINE_SINGLE = 1;            // 单下划线
    public const UNDERLINE_DOUBLE = 2;            // 双下划线
    public const UNDERLINE_SINGLE_ACCOUNTING = 3; // 会计用单下划线
    public const UNDERLINE_DOUBLE_ACCOUNTING = 4; // 会计用双下划线

    // 单元格边框
    public const BORDER_THIN = 1;                // 薄边框风格
    public const BORDER_MEDIUM = 2;              // 中等边框风格
    public const BORDER_DASHED = 3;              // 虚线边框风格
    public const BORDER_DOTTED = 4;              // 虚线边框样式
    public const BORDER_THICK = 5;               // 厚边框风格
    public const BORDER_DOUBLE = 6;              // 双边风格
    public const BORDER_HAIR = 7;                // 头发边框样式
    public const BORDER_MEDIUM_DASHED = 8;       // 中等虚线边框样式
    public const BORDER_DASH_DOT = 9;            // 短划线边框样式
    public const BORDER_MEDIUM_DASH_DOT = 10;     // 中等点划线边框样式
    public const BORDER_DASH_DOT_DOT = 11;        // Dash-dot-dot边框样式
    public const BORDER_MEDIUM_DASH_DOT_DOT = 12; // 中等点划线边框样式
    public const BORDER_SLANT_DASH_DOT = 13;      // 倾斜的点划线边框风格

    // 背景样式
    public const PATTERN_NONE = 1;          // 无填充模式
    public const PATTERN_SOLID = 2;         // 实心填充
    public const PATTERN_MEDIUM_GRAY = 3;   // 中等灰度填充
    public const PATTERN_DARK_GRAY = 4;     // 深灰填充
    public const PATTERN_LIGHT_GRAY = 5;    // 浅灰填充
    public const PATTERN_DARK_HORIZONTAL = 6;   // 深色水平线填充
    public const PATTERN_DARK_VERTICAL = 7;     // 深色垂直线填充
    public const PATTERN_DARK_DOWN = 8;         // 深色向下对角线填充
    public const PATTERN_DARK_UP = 9;           // 深色向上对角线填充
    public const PATTERN_DARK_GRID = 10;        // 深色网格填充
    public const PATTERN_DARK_TRELLIS = 11;     // 深色格子填充
    public const PATTERN_LIGHT_HORIZONTAL = 12; // 浅色水平线填充
    public const PATTERN_LIGHT_VERTICAL = 13;   // 浅色垂直线填充
    public const PATTERN_LIGHT_DOWN = 14;       // 浅色向下对角线填充
    public const PATTERN_LIGHT_UP = 15;         // 浅色向上对角线填充
    public const PATTERN_LIGHT_GRID = 16;       // 浅色网格填充
    public const PATTERN_LIGHT_TRELLIS = 17;    // 浅色格子填充
    public const PATTERN_GRAY_125 = 18;         // 12.5%灰度填充
    public const PATTERN_GRAY_0625 = 19;       // 6.25%灰度填充

    public bool $italic = false; // 斜体
    public array $align = []; // 对齐
    public bool $strikeout = false; // 文本中间划线
    public int $underline = 0; // 下划线
    public bool $wrap = false; // 文本换行
    public int $fontColor = 0; // 字体颜色（16进制）
    public float $fontSize = 0; // 字体大小
    public bool $bold = false; // 粗体
    public int $border = 0; // 边框央视
    public int $backgroundColor = 0;// 背景颜色(16进制)
    public int $backgroundStyle = 0; // 背景样式
    public string $font = ''; // 字体

    public function setItalic(bool $italic): self
    {
        $this->italic = $italic;
        return $this;
    }

    public function setAlign(array $align): self
    {
        $this->align = $align;
        return $this;
    }

    public function setStrikeout(bool $strikeout): self
    {
        $this->strikeout = $strikeout;
        return $this;
    }

    public function setUnderline(int $underline): self
    {
        $this->underline = $underline;
        return $this;
    }

    public function setWrap(bool $wrap): self
    {
        $this->wrap = $wrap;
        return $this;
    }

    public function setFontColor(int $fontColor): self
    {
        $this->fontColor = $fontColor;
        return $this;
    }

    public function setFontSize(float $fontSize): self
    {
        $this->fontSize = $fontSize;
        return $this;
    }

    public function setBold(bool $bold): self
    {
        $this->bold = $bold;
        return $this;
    }

    public function setBorder(int $border): self
    {
        $this->border = $border;
        return $this;
    }

    public function setBackgroundColor(int $backgroundColor): self
    {
        $this->backgroundColor = $backgroundColor;
        return $this;
    }

    public function setBackgroundStyle(int $backgroundStyle): self
    {
        $this->backgroundStyle = $backgroundStyle;
        return $this;
    }

    public function setFont(string $font): self
    {
        $this->font = $font;
        return $this;
    }

}