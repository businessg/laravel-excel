<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel;

use BusinessG\LaravelExcel\Command\CleanFileCommand;
use BusinessG\LaravelExcel\Command\ExportCommand;
use BusinessG\LaravelExcel\Command\ImportCommand;
use BusinessG\LaravelExcel\Command\MessageCommand;
use BusinessG\LaravelExcel\Command\ProgressCommand;
use BusinessG\LaravelExcel\Db\ExcelLogInterface;
use BusinessG\LaravelExcel\Db\ExcelLogManager;
use BusinessG\LaravelExcel\Driver\DriverInterface;
use BusinessG\LaravelExcel\Listener\ExcelLogDbListener;
use BusinessG\LaravelExcel\Listener\ProgressListener;
use BusinessG\LaravelExcel\Logger\ExcelLogger;
use BusinessG\LaravelExcel\Logger\ExcelLoggerInterface;
use BusinessG\LaravelExcel\Progress\Progress;
use BusinessG\LaravelExcel\Progress\ProgressInterface;
use BusinessG\LaravelExcel\Queue\ExcelQueue;
use BusinessG\LaravelExcel\Queue\ExcelQueueInterface;
use BusinessG\LaravelExcel\Strategy\Path\DateTimeExportPathStrategy;
use BusinessG\LaravelExcel\Strategy\Path\ExportPathStrategyInterface;
use BusinessG\LaravelExcel\Strategy\Token\TokenStrategyInterface;
use BusinessG\LaravelExcel\Strategy\Token\UuidStrategy;
use Illuminate\Support\ServiceProvider;

class ExcelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../publish/excel.php', 'excel');

        $this->app->bind(
            \Psr\EventDispatcher\EventDispatcherInterface::class,
            \Illuminate\Contracts\Events\Dispatcher::class
        );

        $this->app->bind(DriverInterface::class, function ($app) {
            return $app->call(ExcelInvoker::class);
        });
        $this->app->singleton(ProgressInterface::class, Progress::class);
        $this->app->singleton(ExcelLogInterface::class, ExcelLogManager::class);
        $this->app->singleton(ExcelInterface::class, Excel::class);
        $this->app->singleton(ExcelLoggerInterface::class, ExcelLogger::class);
        $this->app->singleton(ExcelQueueInterface::class, ExcelQueue::class);
        $this->app->singleton(ExportPathStrategyInterface::class, DateTimeExportPathStrategy::class);
        $this->app->singleton(TokenStrategyInterface::class, UuidStrategy::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ExportCommand::class,
                ImportCommand::class,
                ProgressCommand::class,
                MessageCommand::class,
                CleanFileCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../publish/excel.php' => config_path('excel.php'),
            ], 'excel-config');

            $this->loadMigrationsFrom(__DIR__ . '/migrations');
        }

        $dispatcher = $this->app->make(\Illuminate\Contracts\Events\Dispatcher::class);

        $progressListener = $this->app->make(ProgressListener::class);
        foreach ($progressListener->listen() as $eventClass) {
            $dispatcher->listen($eventClass, [$progressListener, 'process']);
        }

        $excelLogDbListener = $this->app->make(ExcelLogDbListener::class);
        foreach ($excelLogDbListener->listen() as $eventClass) {
            $dispatcher->listen($eventClass, [$excelLogDbListener, 'process']);
        }
    }
}
