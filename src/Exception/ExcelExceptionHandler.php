<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Exception;

use BusinessG\BaseExcel\Exception\ExcelException;
use BusinessG\BaseExcel\Service\ExcelBusinessService;
use Illuminate\Http\JsonResponse;

/**
 * Laravel ExcelException 异常处理器。
 *
 * 捕获 ExcelException 并按 http 配置的字段格式返回 JSON 错误响应。
 * 在 ExcelServiceProvider 中自动注册，无需手动配置。
 */
class ExcelExceptionHandler
{
    public function __construct(protected ExcelBusinessService $service)
    {
    }

    public function render(ExcelException $e): JsonResponse
    {
        $code = $e->getCode() ?: 500;
        return response()->json(
            $this->service->errorResponse($code, $e->getMessage())
        );
    }
}
