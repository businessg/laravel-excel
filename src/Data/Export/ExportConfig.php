<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Data\Export;

use BusinessG\LaravelExcel\Data\BaseConfig;

class ExportConfig extends BaseConfig
{
    public const OUT_PUT_TYPE_UPLOAD = 'upload'; // 上传第三方
    public const OUT_PUT_TYPE_OUT = 'out'; // 直接输出

    /**
     * 输出类型
     *
     * @var string
     */
    public string $outPutType = self::OUT_PUT_TYPE_OUT;

    public array $params = [];

    /**
     * @var Sheet[]
     */
    public array $sheets = [];


    public function getOutPutType(): string
    {
        return $this->outPutType;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function __serialize(): array
    {
        return [
            'serviceName' => $this->getServiceName(),
            'token' => $this->getToken(),
            'isAsync' => $this->getIsAsync(),
            'outPutType' => $this->getOutPutType(),
            'params' => $this->getParams(),
        ];
    }
}