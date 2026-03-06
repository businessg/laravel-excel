<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Data\Export;

use Psr\Http\Message\ResponseInterface;
use BusinessG\LaravelExcel\Data\BaseObject;

class ExportData extends BaseObject
{
    public ResponseInterface|string $response = '';

    public string $token = '';

    /**
     * @return ResponseInterface|string
     */
    public function getResponse(): string|ResponseInterface
    {
        return $this->response;
    }

    public function __serialize(): array
    {
        return [
            'response' => is_string($this->response) ? $this->response : '',
            'token' => $this->token,
        ];
    }

}