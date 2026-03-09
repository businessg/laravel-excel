<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel;

use BusinessG\LaravelExcel\Command\CleanFileCommand;
use BusinessG\LaravelExcel\Command\ExportCommand;
use BusinessG\LaravelExcel\Command\ImportCommand;
use BusinessG\LaravelExcel\Command\MessageCommand;
use BusinessG\LaravelExcel\Command\ProgressCommand;
use BusinessG\BaseExcel\Db\ExcelLogInterface;
use BusinessG\LaravelExcel\Db\ExcelLogManager;
use BusinessG\LaravelExcel\Driver\DriverFactory;
use BusinessG\BaseExcel\Driver\DriverInterface;
use BusinessG\BaseExcel\Progress\Progress;
use BusinessG\BaseExcel\Progress\ProgressInterface;
use BusinessG\BaseExcel\Progress\ProgressStorageInterface;
use BusinessG\BaseExcel\ExcelInterface;
use BusinessG\BaseExcel\Queue\ExcelQueueInterface;
use BusinessG\BaseExcel\Strategy\Path\ExportPathStrategyInterface;
use BusinessG\BaseExcel\Strategy\Path\DateTimeExportPathStrategy;
use BusinessG\BaseExcel\Strategy\Token\TokenStrategyInterface;
use BusinessG\BaseExcel\Strategy\Token\UuidStrategy;
use BusinessG\BaseExcel\Console\ExportCommandHandler;
use BusinessG\BaseExcel\Console\ImportCommandHandler;
use BusinessG\BaseExcel\Console\MessageCommandHandler;
use BusinessG\BaseExcel\Console\ProgressCommandHandler;
use BusinessG\BaseExcel\Console\ProgressDisplay;
use BusinessG\LaravelExcel\Logger\ExcelLogger;
use BusinessG\BaseExcel\Logger\ExcelLoggerInterface;
use BusinessG\LaravelExcel\Progress\LaravelProgressStorage;
use BusinessG\LaravelExcel\Queue\AsyncQueue\ExcelQueue;
use Illuminate\Support\ServiceProvider;

class ExcelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../publish/excel.php', 'excel');


        $this->app->singleton(DriverFactory::class);
        $this->app->bind(DriverInterface::class, function ($app) {
            return $app->call(ExcelInvoker::class);
        });
        $this->app->singleton(ProgressStorageInterface::class, LaravelProgressStorage::class);
        $this->app->singleton(ProgressInterface::class, function ($app) {
            $config = config('excel.progress', [
                'enable' => true,
                'prefix' => 'LaravelExcel',
                'expire' => 3600,
            ]);
            $storage = $app->make(ProgressStorageInterface::class);
            return new Progress($storage, $config);
        });
        $this->app->singleton(ExcelLogInterface::class, ExcelLogManager::class);
        $this->app->singleton(ExcelInterface::class, Excel::class);
        $this->app->singleton(ExcelLoggerInterface::class, ExcelLogger::class);
        $this->app->singleton(ExcelQueueInterface::class, ExcelQueue::class);
        $this->app->singleton(ExportPathStrategyInterface::class, DateTimeExportPathStrategy::class);
        $this->app->singleton(TokenStrategyInterface::class, UuidStrategy::class);
        $this->app->singleton(ProgressDisplay::class);
        $this->app->singleton(ExportCommandHandler::class);
        $this->app->singleton(ImportCommandHandler::class);
        $this->app->singleton(ProgressCommandHandler::class);
        $this->app->singleton(MessageCommandHandler::class);
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

        $progressListener = $this->app->make(\BusinessG\BaseExcel\Listener\ProgressListener::class);
        foreach ($progressListener->listen() as $eventClass) {
            $dispatcher->listen($eventClass, [$progressListener, 'process']);
        }

        $excelLogDbListener = $this->app->make(\BusinessG\BaseExcel\Listener\ExcelLogDbListener::class);
        foreach ($excelLogDbListener->listen() as $eventClass) {
            $dispatcher->listen($eventClass, [$excelLogDbListener, 'process']);
        }
    }
}
