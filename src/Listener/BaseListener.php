<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Listener;

use BusinessG\LaravelExcel\Event\AfterExport;
use BusinessG\LaravelExcel\Event\AfterExportData;
use BusinessG\LaravelExcel\Event\AfterExportExcel;
use BusinessG\LaravelExcel\Event\AfterExportOutput;
use BusinessG\LaravelExcel\Event\AfterExportSheet;
use BusinessG\LaravelExcel\Event\AfterImport;
use BusinessG\LaravelExcel\Event\AfterImportData;
use BusinessG\LaravelExcel\Event\AfterImportExcel;
use BusinessG\LaravelExcel\Event\AfterImportSheet;
use BusinessG\LaravelExcel\Event\BeforeExport;
use BusinessG\LaravelExcel\Event\BeforeExportData;
use BusinessG\LaravelExcel\Event\BeforeExportExcel;
use BusinessG\LaravelExcel\Event\BeforeExportOutput;
use BusinessG\LaravelExcel\Event\BeforeExportSheet;
use BusinessG\LaravelExcel\Event\AfterPreCheck;
use BusinessG\LaravelExcel\Event\AfterPreCheckData;
use BusinessG\LaravelExcel\Event\AfterPreCheckSheet;
use BusinessG\LaravelExcel\Event\BeforeImport;
use BusinessG\LaravelExcel\Event\BeforeImportData;
use BusinessG\LaravelExcel\Event\BeforeImportExcel;
use BusinessG\LaravelExcel\Event\BeforeImportSheet;
use BusinessG\LaravelExcel\Event\BeforePreCheck;
use BusinessG\LaravelExcel\Event\BeforePreCheckData;
use BusinessG\LaravelExcel\Event\BeforePreCheckSheet;
use BusinessG\LaravelExcel\Event\Error;
use BusinessG\LaravelExcel\Logger\ExcelLoggerInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * 监听输出日志
 */
abstract class BaseListener
{
    protected ContainerInterface $container;
    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container, ExcelLoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger->getLogger();
    }

    public function listen(): array
    {
        return [
            BeforeExport::class,
            BeforeExportExcel::class,
            BeforeExportData::class,
            BeforeExportSheet::class,
            BeforeExportOutput::class,

            AfterExport::class,
            AfterExportData::class,
            AfterExportExcel::class,
            AfterExportSheet::class,
            AfterExportOutput::class,

            BeforeImport::class,
            BeforeImportExcel::class,
            BeforeImportData::class,
            BeforeImportSheet::class,

            AfterImport::class,
            AfterImportData::class,
            AfterImportExcel::class,
            AfterImportSheet::class,

            BeforePreCheck::class,
            BeforePreCheckData::class,
            BeforePreCheckSheet::class,
            AfterPreCheck::class,
            AfterPreCheckData::class,
            AfterPreCheckSheet::class,

            Error::class,
        ];
    }

    protected function getEventClass(object $event)
    {
        return lcfirst(basename(str_replace('\\', '/', get_class($event))));
    }

    public function process(object $event): void
    {
        $className = $this->getEventClass($event);
        $this->{$className}($event);
    }

    abstract function beforeExport(object $event);

    abstract function beforeExportExcel(object $event);

    abstract function beforeExportData(object $event);

    abstract function beforeExportSheet(object $event);

    abstract function beforeExportOutput(object $event);

    abstract function afterExport(object $event);

    abstract function afterExportData(object $event);

    abstract function afterExportExcel(object $event);

    abstract function afterExportSheet(object $event);

    abstract function afterExportOutput(object $event);

    abstract function beforeImport(object $event);

    abstract function beforeImportExcel(object $event);

    abstract function beforeImportData(object $event);

    abstract function beforeImportSheet(object $event);

    abstract function afterImport(object $event);

    abstract function afterImportData(object $event);

    abstract function afterImportExcel(object $event);

    abstract function afterImportSheet(object $event);

    abstract function beforePreCheck(object $event);

    abstract function beforePreCheckData(object $event);

    abstract function beforePreCheckSheet(object $event);

    abstract function afterPreCheck(object $event);

    abstract function afterPreCheckData(object $event);

    abstract function afterPreCheckSheet(object $event);

    abstract function error(object $event);
}
