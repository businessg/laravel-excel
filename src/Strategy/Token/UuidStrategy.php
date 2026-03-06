<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Strategy\Token;

use BusinessG\LaravelExcel\Helper\Helper;

class UuidStrategy implements TokenStrategyInterface
{

    public function getToken():string
    {
        return Helper::uuid4();
    }
}