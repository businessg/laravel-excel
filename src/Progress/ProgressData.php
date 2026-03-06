<?php

namespace BusinessG\LaravelExcel\Progress;

use BusinessG\LaravelExcel\Data\BaseObject;

class ProgressData extends BaseObject
{

    /**
     * 进度状态
     */
    public const PROGRESS_STATUS_AWAIT = 1; // 待处理
    public const PROGRESS_STATUS_PROCESS = 2; // 处理中
    public const PROGRESS_STATUS_END = 3; // 处理完成
    public const PROGRESS_STATUS_FAIL = 4; // 处理失败
    public const PROGRESS_STATUS_OUTPUT = 5; // 正在输出
    public const PROGRESS_STATUS_COMPLETE = 6; // 完成

    public int $total = 0;
    public int $progress = 0;
    public int $success = 0;
    public int $fail = 0;

    public int $status = self::PROGRESS_STATUS_AWAIT;

    public string $message = '';
}