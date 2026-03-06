<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Data\Import;

use BusinessG\LaravelExcel\Data\BaseConfig;

class ImportConfig extends BaseConfig
{
    /**
     * 是否直接返回sheet数据
     *
     * @var bool
     */
    public bool $isReturnSheetData = false;

    /**
     * 预检时预览数据行数（0 表示不返回预览）
     */
    public int $preCheckSampleLimit = 0;

    /**
     * 导入地址
     *
     * @var string
     */
    public string $path = '';

    /**
     * 读取页
     * @var Sheet[]
     */
    public array $sheets = [];

    /**
     * 临时文件地址
     *
     * @var string
     */
    private string $tempPath = '';

    /**
     * 获取地址
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * 是否异步
     *
     * @return bool
     */
    public function getIsAsync(): bool
    {
        return $this->isAsync;
    }

    /**
     * 设置导入地址
     *
     * @param string $path
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * 设置临时文件地址
     *
     * @param string $tempPath
     * @return $this
     */
    final public function setTempPath(string $tempPath)
    {
        $this->tempPath = $tempPath;
        return $this;
    }

    /**
     * 获取临时文件地址
     *
     * @return string
     */
    final public function getTempPath(): string
    {
        return $this->tempPath;
    }

    /**
     * 序列化
     *
     * @return array
     */
    public function __serialize(): array
    {
        return [
            'serviceName' => $this->getServiceName(),
            'path' => $this->getPath(),
            'isAsync' => $this->getIsAsync(),
            'token' => $this->getToken(),
        ];
    }
}