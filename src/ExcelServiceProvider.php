<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel;

use BusinessG\BaseExcel\AbstractExcel;
use BusinessG\BaseExcel\Console\ExportCommandHandler;
use BusinessG\BaseExcel\Console\ImportCommandHandler;
use BusinessG\BaseExcel\Console\MessageCommandHandler;
use BusinessG\BaseExcel\Console\ProgressCommandHandler;
use BusinessG\BaseExcel\Console\ProgressDisplay;
use BusinessG\BaseExcel\Contract\ConfigResolverInterface;
use BusinessG\BaseExcel\Contract\DeferInterface;
use BusinessG\BaseExcel\Contract\FilesystemResolverInterface;
use BusinessG\BaseExcel\Contract\FrameworkBridgeInterface;
use BusinessG\BaseExcel\Contract\LoggerResolverInterface;
use BusinessG\BaseExcel\Contract\ObjectFactoryInterface;
use BusinessG\BaseExcel\Contract\RedisAdapterInterface;
use BusinessG\BaseExcel\Contract\RedisResolverInterface;
use BusinessG\BaseExcel\Contract\ResponseFactoryInterface;
use BusinessG\BaseExcel\Db\ExcelLogInterface;
use BusinessG\BaseExcel\Db\ExcelLogManager;
use BusinessG\BaseExcel\Driver\DriverFactory;
use BusinessG\BaseExcel\Driver\DriverInterface;
use BusinessG\BaseExcel\ExcelInterface;
use BusinessG\BaseExcel\ExcelInvoker;
use BusinessG\BaseExcel\Listener\AbstractBaseListener;
use BusinessG\BaseExcel\Listener\ListenerRegistrar;
use BusinessG\BaseExcel\Logger\ExcelLogger;
use BusinessG\BaseExcel\Logger\ExcelLoggerInterface;
use BusinessG\BaseExcel\Progress\Progress;
use BusinessG\BaseExcel\Progress\ProgressInterface;
use BusinessG\BaseExcel\Progress\ProgressStorageInterface;
use BusinessG\BaseExcel\Progress\Storage\BridgeProgressStorage;
use BusinessG\BaseExcel\Queue\ExcelQueueInterface;
use BusinessG\BaseExcel\Strategy\Path\DateTimeExportPathStrategy;
use BusinessG\BaseExcel\Strategy\Path\ExportPathStrategyInterface;
use BusinessG\BaseExcel\Strategy\Token\TokenStrategyInterface;
use BusinessG\BaseExcel\Strategy\Token\UuidStrategy;
use BusinessG\LaravelExcel\Command\CleanFileCommand;
use BusinessG\LaravelExcel\Command\ExportCommand;
use BusinessG\LaravelExcel\Command\ImportCommand;
use BusinessG\LaravelExcel\Command\MessageCommand;
use BusinessG\LaravelExcel\Command\ProgressCommand;
use BusinessG\LaravelExcel\Queue\AsyncQueue\ExcelQueue;
use Illuminate\Support\ServiceProvider;
use Psr\EventDispatcher\EventDispatcherInterface;

class ExcelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../publish/excel.php', 'excel');

        \BusinessG\BaseExcel\ExcelFunctions::setContainerResolver(fn () => app());

        $bridge = new LaravelBridge();
        $this->app->singleton(FrameworkBridgeInterface::class, fn () => $bridge);
        $this->app->singleton(ConfigResolverInterface::class, fn () => $bridge);
        $this->app->singleton(ObjectFactoryInterface::class, fn () => $bridge);
        $this->app->singleton(RedisResolverInterface::class, fn () => $bridge);
        $this->app->singleton(LoggerResolverInterface::class, fn () => $bridge);
        $this->app->singleton(ResponseFactoryInterface::class, fn () => $bridge);
        $this->app->singleton(FilesystemResolverInterface::class, fn () => $bridge);
        $this->app->singleton(DeferInterface::class, fn () => $bridge);
        $this->app->singleton(EventDispatcherInterface::class, fn () => $bridge->getEventDispatcher());

        $this->app->singleton(RedisAdapterInterface::class, LaravelRedisAdapter::class);

        $this->app->singleton(DriverFactory::class);
        $this->app->bind(DriverInterface::class, function ($app) {
            return $app->make(ExcelInvoker::class)($app);
        });
        $this->app->singleton(ProgressStorageInterface::class, BridgeProgressStorage::class);
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
        $this->app->singleton(ExcelInterface::class, AbstractExcel::class);
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

        foreach (ListenerRegistrar::getDefaultListeners() as $listenerClass) {
            /** @var AbstractBaseListener $listener */
            $listener = $this->app->make($listenerClass);
            foreach ($listener->listen() as $eventClass) {
                $dispatcher->listen($eventClass, [$listener, 'process']);
            }
        }
    }
}
