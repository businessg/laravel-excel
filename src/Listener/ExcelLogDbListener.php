<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Listener;

use BusinessG\LaravelExcel\Db\ExcelLogInterface;
use BusinessG\LaravelExcel\Event\AfterExport;
use BusinessG\LaravelExcel\Event\AfterExportOutput;
use BusinessG\LaravelExcel\Event\AfterExportSheet;
use BusinessG\LaravelExcel\Event\AfterImport;
use BusinessG\LaravelExcel\Event\AfterImportSheet;
use BusinessG\LaravelExcel\Event\BeforeExport;
use BusinessG\LaravelExcel\Event\BeforeExportOutput;
use BusinessG\LaravelExcel\Event\BeforeImport;
use BusinessG\LaravelExcel\Event\Error;
use BusinessG\LaravelExcel\Event\Event;
use BusinessG\LaravelExcel\Logger\ExcelLoggerInterface;
use Psr\Container\ContainerInterface;

/**
 * 监听输出日志
 */
class ExcelLogDbListener extends BaseListener
{
    protected ExcelLogInterface $excelLog;

    public function __construct(ContainerInterface $container, ExcelLoggerInterface $excelLogger, ExcelLogInterface $excelLog)
    {
        parent::__construct($container, $excelLogger);
        $this->excelLog = $excelLog;
    }

    public function process(object $event): void
    {
        /**
         * @var Event $event
         */
        $enable = $this->excelLog->getConfig()['enable'] ?? true;
        if (!$enable || !$event->config->getIsDbLog()) {
            return;
        }
        parent::process($event);
    }

    function beforeExport(object $event)
    {
        /**
         * @var BeforeExport $event
         */
        $this->excelLog->saveLog($event->config);
    }

    function beforeExportExcel(object $event)
    {
    }

    function beforeExportData(object $event)
    {
    }

    function beforeExportSheet(object $event)
    {
    }

    function beforeExportOutput(object $event)
    {
        /**
         * @var BeforeExportOutput $event
         */
        $this->excelLog->saveLog($event->config);
    }

    function afterExport(object $event)
    {
        /**
         * @var AfterExport $event
         */
        $this->excelLog->saveLog($event->config);
    }

    function afterExportData(object $event)
    {
    }

    function afterExportExcel(object $event)
    {
    }

    function afterExportSheet(object $event)
    {
        /**
         * @var AfterExportSheet $event
         */
        $this->excelLog->saveLog($event->config);
    }

    function afterExportOutput(object $event)
    {
    }

    function beforeImport(object $event)
    {
        /**
         * @var BeforeImport $event
         */
        $this->excelLog->saveLog($event->config);
    }

    function beforeImportExcel(object $event)
    {
    }

    function beforeImportData(object $event)
    {
    }

    function beforeImportSheet(object $event)
    {
    }

    function afterImport(object $event)
    {
        /**
         * @var AfterImport $event
         */
        $this->excelLog->saveLog($event->config);
    }

    function afterImportData(object $event)
    {
    }

    function afterImportExcel(object $event)
    {
    }

    function afterImportSheet(object $event)
    {
        /**
         * @var AfterImportSheet $event
         */
        $this->excelLog->saveLog($event->config);
    }

    function error(object $event)
    {
        /**
         * @var Error $event
         */
        $this->excelLog->saveLog($event->config, [
            'remark' => $event->exception->getMessage(),
        ]);
    }
}
