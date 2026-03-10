<?php

declare(strict_types=1);

return [
    'default' => 'xlswriter',

    'drivers' => [
        'xlswriter' => [
            'class' => \BusinessG\BaseExcel\Driver\XlsWriterDriver::class,
            'disk' => 'local',
            'exportDir' => 'export',
            'tempDir' => null,
        ],
    ],

    'logging' => [
        'channel' => 'stack',
    ],

    'queue' => [
        'connection' => 'default',
        'channel' => 'default',
    ],

    'progress' => [
        'enabled' => true,
        'prefix' => 'LaravelExcel',
        'ttl' => 3600,
        'connection' => 'default',
    ],

    'dbLog' => [
        'enabled' => true,
        'model' => \BusinessG\LaravelExcel\Db\Model\ExcelLog::class,
    ],

    'cleanup' => [
        'enabled' => true,
        'maxAge' => 1800,
        'interval' => 3600,
    ],
];
