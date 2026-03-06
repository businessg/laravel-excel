<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Strategy\Token;

interface TokenStrategyInterface
{
    public function getToken(): string;
}