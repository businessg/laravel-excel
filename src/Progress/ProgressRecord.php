<?php

namespace BusinessG\LaravelExcel\Progress;

use BusinessG\LaravelExcel\Data\BaseObject;

/**
 * 进度信息
 */
class ProgressRecord extends BaseObject
{
    /**
     * 页码进度信息
     *
     * @var ProgressData[]|null
     */
    public ?array $sheetListProgress = [];

    /**
     * 进度信息
     *
     * @var ProgressData|null
     */
    public ?ProgressData $progress = null;

    /**
     * 数据
     *
     */
    public  $data;

    /**
     * 获取页码进度
     *
     * @param string $sheetName
     * @return ProgressData
     */
    public function getProgressBySheet(string $sheetName): ProgressData
    {
        return $this->sheetListProgress[$sheetName] ?? new ProgressData();
    }

    /**
     * 设置页进度
     *
     * @param string $sheetName
     * @param ProgressData $progress
     * @return ProgressRecord
     */
    public function setProgressBySheet(string $sheetName, ProgressData $progress): static
    {
        $this->sheetListProgress[$sheetName] = $progress;
        return $this;
    }


}