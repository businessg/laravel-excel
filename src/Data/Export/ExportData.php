<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Data\Export;

use BusinessG\LaravelExcel\Data\BaseObject;

class ExportData extends BaseObject
{
    /** @var \Psr\Http\Message\ResponseInterface|\Symfony\Component\HttpFoundation\Response|string */
    public $response = '';

    public string $token = '';

    /**
     * @return \Psr\Http\Message\ResponseInterface|\Symfony\Component\HttpFoundation\Response|string
     */
    public function getResponse(): mixed
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