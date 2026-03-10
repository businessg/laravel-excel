<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | 默认驱动
    |--------------------------------------------------------------------------
    |
    | 指定 Excel 处理使用的默认驱动，对应下方 drivers 数组中的 key。
    | 目前支持: xlswriter (需安装 ext-xlswriter 扩展)
    |
    */
    'default' => 'xlswriter',

    /*
    |--------------------------------------------------------------------------
    | 驱动配置
    |--------------------------------------------------------------------------
    |
    | 每个驱动的具体配置项：
    |  - class:     驱动类名
    |  - disk:      文件系统磁盘名，对应 config/filesystems.php 中的 disks key
    |  - exportDir: 导出文件存放目录（相对于 disk 根路径）
    |  - tempDir:   临时文件目录，null 则使用系统临时目录 sys_get_temp_dir()
    |
    */
    'drivers' => [
        'xlswriter' => [
            'class' => \BusinessG\BaseExcel\Driver\XlsWriterDriver::class,
            'disk' => 'local',
            'exportDir' => 'export',
            'tempDir' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 日志配置
    |--------------------------------------------------------------------------
    |
    | channel: 日志通道名称，对应 config/logging.php 中的 channels key
    |          例如 'stack', 'single', 'daily' 等
    |
    */
    'logging' => [
        'channel' => 'stack',
    ],

    /*
    |--------------------------------------------------------------------------
    | 异步队列配置
    |--------------------------------------------------------------------------
    |
    | 当导入/导出配置为异步 (isAsync=true) 时，任务将推送到队列执行。
    |  - connection: 队列连接名，对应 config/queue.php 中的 connections key
    |  - channel:    队列名称，对应 Laravel 的 queue name (onQueue)
    |
    */
    'queue' => [
        'connection' => 'default',
        'channel' => 'default',
    ],

    /*
    |--------------------------------------------------------------------------
    | 进度追踪配置
    |--------------------------------------------------------------------------
    |
    | 基于 Redis 的实时进度追踪，前端可通过 token 轮询获取进度。
    |  - enabled:    是否启用进度追踪
    |  - prefix:     Redis key 前缀，最终 key 格式: {prefix}:{token}
    |  - ttl:        进度数据过期时间（秒）
    |  - connection: Redis 连接名，对应 config/database.php 中 redis.connections 的 key
    |
    */
    'progress' => [
        'enabled' => true,
        'prefix' => 'LaravelExcel',
        'ttl' => 3600,
        'connection' => 'default',
    ],

    /*
    |--------------------------------------------------------------------------
    | 数据库日志配置
    |--------------------------------------------------------------------------
    |
    | 将每次导入/导出操作记录到数据库，便于追溯和统计。
    |  - enabled: 是否启用数据库日志
    |  - model:   Eloquent Model 类名，用于日志持久化
    |             也可通过容器绑定 ExcelLogRepositoryInterface 自定义实现
    |
    */
    'dbLog' => [
        'enabled' => true,
        'model' => \BusinessG\LaravelExcel\Db\Model\ExcelLog::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | 临时文件清理配置
    |--------------------------------------------------------------------------
    |
    | 导入/导出过程中产生的临时文件自动清理策略。
    |  - enabled:  是否启用自动清理
    |  - maxAge:   文件最大存活时间（秒），超时未修改的文件将被删除
    |  - interval: 清理任务执行间隔（秒）
    |
    */
    'cleanup' => [
        'enabled' => true,
        'maxAge' => 1800,
        'interval' => 3600,
    ],
];
