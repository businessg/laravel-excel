<?php

declare(strict_types=1);

return [
    'default' => 'xlswriter',
    'drivers' => [
        'xlswriter' => [
            'driver' => \BusinessG\LaravelExcel\Driver\XlsWriterDriver::class,
        ],
    ],
    'options' => [
        // filesystem 配置
        'filesystem' => [
            'storage' => 'local', // 默认本地
        ],
        // 导出配置
        'export' => [
            'rootDir' => 'export', // 导出根目录
        ],
    ],
    // 日志 (使用 Laravel logging 配置中的 channel 名称，如 stack、single 等)
    'logger' => [
        'name' => 'stack',
    ],
    // queue配置
    'queue' => [
        'name' => 'default',
    ],
    // 进度处理
    'progress' => [
        'enable' => true,
        'prefix' => 'LaravelExcel',
        'expire' => 3600, // 数据失效时间
        'redis' => [
            'connection' => 'default',
        ],
    ],
    // db日志
    'dbLog' => [
        'enable' => true,
        'model' => \BusinessG\LaravelExcel\Db\Model\ExcelLog::class
    ],
    // 清除临时文件
    'cleanTempFile' => [
        'enable' => true, // 是否允许
        'time' => 1800, // 文件未操作时间(秒)
        'interval' => 3600,// 间隔检查时间
    ],
];
