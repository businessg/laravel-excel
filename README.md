# laravel-excel

[![php](https://img.shields.io/badge/php-%3E=8.1-brightgreen.svg?maxAge=2592000)](https://github.com/php/php-src)
[![Latest Stable Version](https://img.shields.io/packagist/v/businessg/laravel-excel)](https://packagist.org/packages/businessg/laravel-excel)
[![License](https://img.shields.io/packagist/l/businessg/laravel-excel)](https://github.com/businessg/laravel-excel)

## 📌 概述

Excel 同步/异步智能配置导入导出组件，为 Laravel 框架提供强大的 Excel 处理能力。

## ✨ 组件能力


- ✅ **异步处理** - 支持异步导入导出
- 🧩 **复杂表头** - 支持`无限极`、`跨行`、`跨列`的复杂表头设计
- 🎨 **样式定制** - 可配置`页码样式`、`表头样式`、`列样式`
- 📊 **进度追踪** - 实时获取处理进度信息
- 💬 **消息系统** - 支持构建查询消息
- 📄 **格式支持** - 支持 `xlsx` 格式
- ⚙️ **驱动支持** - 基于 `xlswriter` 驱动

## 🚀 安装

### 前置准备

1. 安装依赖拓展 [xlswriter](https://xlswriter-docs.viest.me/zh-cn/an-zhuang)

```bash
pecl install xlswriter
```

2. Laravel 项目需配置：
    - Redis（用于进度追踪）
    - 队列（用于异步导入导出，可选）
    - 文件系统（Storage）

### 安装组件

```shell
composer require businessg/laravel-excel
```

### 发布配置

```shell
php artisan vendor:publish --tag=excel-config
```

### 执行迁移

```shell
php artisan migrate
```

## 🛠 使用指南

- excel对象

```php
$excel = app(\BusinessG\LaravelExcel\ExcelInterface::class);
// 或使用辅助函数
$exportData = \excel_export(new DemoExportConfig([...]));
$importData = \excel_import(new DemoImportConfig()->setPath('/d/xxx.xlsx'));
```

- 导出

```php
/**
 * @var \BusinessG\LaravelExcel\ExcelInterface $excel 
 */
$exportData = $excel->export(new DemoExportConfig([
    // 额外参数
    'params'=> $request->all(),
]));
```

- 导入

```php
/**
 * @var \BusinessG\LaravelExcel\ExcelInterface $excel 
 */
$importData = $excel->import(new DemoImportConfig()->setPath('/d/xxx.xlsx'));
```

- 获取进度

```php
/**
 * @var \BusinessG\LaravelExcel\ExcelInterface $excel 
 * @var \BusinessG\LaravelExcel\Progress\ProgressRecord $progressRecord
 */
$progressRecord = $excel->getProgressRecord($token);
```

- 获取输出消息

```php
/**
 * @var \BusinessG\LaravelExcel\ExcelInterface $excel 
 */
$isEnd = false; // 是否结束
$messages = $excel->popMessageAndIsEnd($token, 50, $isEnd);
```

## ⚙️配置类配置

### 导出

- config

```php
<?php

namespace App\Excel\Export;

use BusinessG\LaravelExcel\Data\Export\ExportConfig;
use BusinessG\LaravelExcel\Data\Export\Column;
use BusinessG\LaravelExcel\Data\Export\ExportCallbackParam;
use BusinessG\LaravelExcel\Data\Export\Sheet;
use BusinessG\LaravelExcel\Data\Export\SheetStyle;

class DemoExportConfig extends ExportConfig
{
    public string $serviceName = 'demo';

    // 是否异步
    public bool $isAsync = true;

    // 输出类型  
    // OUT_PUT_TYPE_UPLOAD  导出并上传
    // OUT_PUT_TYPE_OUT     直接同步输出
    public string $outPutType = self::OUT_PUT_TYPE_UPLOAD;

    // 页码配置
    public function getSheets(): array
    {
        $this->setSheets([
            new Sheet([
                'name' => 'sheet1',
                'columns' => [
                    new Column([
                        'title' => '用户名',
                        'field' => 'username',
                        // 子列
                        'children' => []
                    ]),
                    new Column([
                        'title' => '姓名',
                        'field' => 'name',
                    ]),
                    new Column([
                        'title' => '年龄',
                        'field' => 'age',
                    ]),
                    // ...
                ],
                'count' => $this->getDataCount(), // 数据数量
                'data' => [$this, 'getData'], // 数据
                'pageSize' => 500, // 每页导出数量<分批导出>
                'style'=> new SheetStyle(), // 页码样式
            ])
        ]);
        return $this->sheets;
    }

    /**
     * 获取数据数量
     *
     * @return int
     */
    public function getDataCount(): int
    {
        // 测试数据 <实际业务可能是查询数据库>
        return 1000;
    }

    /**
     * 获取数据
     *
     * @param ExportCallbackParam $exportCallbackParam
     * @return array
     */
    public function getData(ExportCallbackParam $exportCallbackParam): array
    {
      // $exportCallbackParam->page; // 当前页码
      // $exportCallbackParam->pageSize;// 页码数量
      
      usleep(500000);
      var_dump($this->params);
      // 测试数据 <实际业务可能是查询数据库>
      for ($i = 0; $i < $exportCallbackParam->pageSize; $i++) {
          $d[] = [
              'username' => '哈哈',
              'name' => '测试'
              'age' => 11,
          ];
      }
      
      // 输出信息
      $progress = app(\BusinessG\LaravelExcel\Progress\ProgressInterface::class);
      $progress->pushMessage($this->token,"页码:".$exportCallbackParam->page .",数量：". $exportCallbackParam->pageSize);
      return $d ?? [];
    }
}

```

- Sheet 页码

```php
 new Sheet([
       // 页码名
      'name' => 'sheet1',
      // 列配置
      'columns' => [ 
         new \BusinessG\LaravelExcel\Data\Export\Column([]),
      ],
      // 数据数量
      'count' => 0, 
      // 数据(array|callback)
      'data' => function(\BusinessG\LaravelExcel\Data\Export\ExportCallbackParam $callbackParam){
            return [];
      }, 
      // 分批导出数
      'pageSize' => 1, 
      // 页码样式
      'style'=> new \BusinessG\LaravelExcel\Data\Export\SheetStyle([]);
]),
```

- Column 列

```php
 new Column([
      // 列名
      'title' => "一级列", 
       // 宽度
      //'width' => 32,
      // 高度
      'height' => 58,
      // header 单元样式
      'headerStyle' => new Style([
          'wrap' => true,
          'fontColor' => 0x2972F4,
          'font' => '等线',
          'align' => [Style::FORMAT_ALIGN_LEFT, Style::FORMAT_ALIGN_VERTICAL_CENTER],
          'fontSize' => 10,
      ]),
      // 子列 <自动跨列>
      'children' => [
          new Column([
              'title' => '二级列1',
              'field' => 'key1', // 数据字段名
              'width' => 32, // 宽度
              // 头部单元格样式
              'headerStyle' => new Style([
                  'align' => [Style::FORMAT_ALIGN_CENTER],
                  'bold' => true,
              ]),
          ]),
          // ...
      ],
]),
```

- sheetStyle <页码样式>

```php
new \BusinessG\LaravelExcel\Data\Export\SheetStyle([
   // 网格线
   'gridline'=> \Vartruexuan\HyperfExcel\Data\Export\SheetStyle::GRIDLINES_HIDE_ALL,
   // 缩放 (10 <= $scale <= 400)
   'zoom'=> 50,  
   // 隐藏当前页码 
   'hide' => false, 
   // 选中当前页码
   'isFirst' => true,
])
```

- style <列|单元格样式>

```php
new Style([
  'wrap' => true,
  'fontColor' => 0x2972F4,
  'font' => '等线',
  'align' => [Style::FORMAT_ALIGN_LEFT, Style::FORMAT_ALIGN_VERTICAL_CENTER],
  'fontSize' => 10,
])
```

### 导入

- config

```php
<?php

namespace App\Excel\Import;

use BusinessG\LaravelExcel\Data\Import\ImportConfig;
use App\Exception\BusinessException;
use Illuminate\Support\Arr;
use BusinessG\LaravelExcel\Data\Import\ImportRowCallbackParam;
use BusinessG\LaravelExcel\Data\Import\Sheet;
use BusinessG\LaravelExcel\Data\Import\Column;

class DemoImportConfig extends ImportConfig
{
    public string $serviceName = 'demo';

    // 是否异步 <默认 async-queue>
    public bool $isAsync = true;
    
    public function getSheets(): array
    {
        $this->setSheets([
            new Sheet([
                'name' => 'sheet1',
                'headerIndex' => 1, // 列头下标<0则无列头>
                'columns' => [
                      new Column([
                          'title' => '用户名', // excel中列头
                          'field' => 'username', // 映射字段名
                          'type' => Column::TYPE_STRING, // 数据类型(默认 string)
                      ]),
                      new Column([
                          'title' => '年龄',
                          'field' => 'age',
                          'type' => Column::TYPE_INT,
                      ]),
                      new Column([
                          'title' => '身高',
                          'field' => 'height',
                          'type' => Column::TYPE_INT,
                      ]),
                ],
                // 数据回调
                'callback' => [$this, 'rowCallback']
            ])
        ]);
        return parent::getSheets();
    }

    public function rowCallback(ImportRowCallbackParam $importRowCallbackParam)
    {
       // $importRowCallbackParam->row; // 行数据
       // $importRowCallbackParam->rowIndex; // 行下标
       // $importRowCallbackParam->sheet;// 当前页码
        try {
             // 参数校验
             // 业务操作
             var_dump($importRowCallbackParam->row);
        } catch (\Throwable $throwable) {
            // 异常信息将会推入进度消息中 | 自动归为失败数
            throw new BusinessException(ResultCode::FAIL, '第' . $param->rowIndex . '行:' . $throwable->getMessage());
        }
    }
}
```

- sheet

```php
new Sheet([
    // 页码名
    'name' => 'sheet1',
    // 列头下标<0则无列头>
    'headerIndex' => 1, 
    // 列配置
    'columns' => [
          new Column([
              'title' => '用户名', // excel中列头
              'field' => 'username', // 映射字段名
              'type' => Column::TYPE_STRING, // 数据类型(默认 string)
          ]),
    ],
    // 数据回调
    'callback' => function(\BusinessG\LaravelExcel\Data\Import\ImportRowCallbackParam $callbackParam){}
])

```

- column

```php
new Column([
    // 列头
    'title' => '身高',
    // 映射字段名
    'field' => 'height',
    // 读取类型
    'type' => Column::TYPE_INT,
]),
```

## 组件配置

```php
<?php

declare(strict_types=1);

return [
    'default' => 'xlswriter',
    'drivers' => [
        'xlswriter' => [
            'driver' => \BusinessG\LaravelExcel\Driver\XlsWriterDriver::class,
        ]
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
    // 日志
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
        'redis' => ['connection' => 'default'],
    ],
    // db日志
    'dbLog' => [
        'enable' => true,
        'model' => \BusinessG\LaravelExcel\Db\Model\ExcelLog::class,
    ],
    // 清除临时文件
    'cleanTempFile' => [
        'enable' => true, // 是否允许
        'time' => 1800, // 文件未操作时间(秒)
        'interval' => 3600,// 间隔检查时间
    ],
];
```

## 📜命令行

- 导出

```bash
php artisan excel:export "\App\Excel\DemoExportConfig"
```

- 导入

```bash
# 本地文件
php artisan excel:import "\App\Excel\DemoImportConfig" "/d/xxx.xlsx"
# 远程文件
php artisan excel:import "\App\Excel\DemoImportConfig" "https://xxx.com/xxx.xlsx"
```

- 进度查询

```bash
php artisan excel:progress 424ee1bd6db248e09b514231edea5f04
```

- 获取输出消息

```bash
php artisan excel:message 424ee1bd6db248e09b514231edea5f04
```

- 清理临时文件（可配置到 Laravel 任务调度）

```bash
php artisan excel:clean-temp-files
```

## 服务绑定

组件通过 `ExcelServiceProvider` 自动注册以下服务，可在 `AppServiceProvider` 中覆盖：

- Token 生成策略：`BusinessG\LaravelExcel\Strategy\Token\TokenStrategyInterface` → `UuidStrategy`
- 导出路径策略：`BusinessG\LaravelExcel\Strategy\Path\ExportPathStrategyInterface` → `DateTimeExportPathStrategy`
- 队列：`BusinessG\LaravelExcel\Queue\ExcelQueueInterface` → `ExcelQueue`

## 监听器

`ProgressListener` 和 `ExcelLogDbListener` 已由 ServiceProvider 自动注册。

### 自定义监听器

继承 `BusinessG\LaravelExcel\Listener\BaseListener` 并在 ServiceProvider 的 `boot()` 中注册事件。

## License

MIT
