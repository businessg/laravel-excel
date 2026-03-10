<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel;

use BusinessG\BaseExcel\Contract\FrameworkBridgeInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemOperator;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaravelBridge implements FrameworkBridgeInterface
{
    public function get(string $key, mixed $default = null): mixed
    {
        return config($key, $default);
    }

    public function make(string $class, array $params = []): object
    {
        return app()->make($class, $params);
    }

    public function getRedis(string $connection = 'default'): object
    {
        return Redis::connection($connection)->client();
    }

    public function getLogger(string $channel = 'default'): LoggerInterface
    {
        return Log::channel($channel);
    }

    public function getEventDispatcher(): EventDispatcherInterface
    {
        return new LaravelEventDispatcherAdapter(
            app(\Illuminate\Contracts\Events\Dispatcher::class)
        );
    }

    public function createDownloadResponse(string $filePath, string $fileName, array $headers = []): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($filePath) {
            $stream = fopen($filePath, 'r');
            fpassthru($stream);
            fclose($stream);
            @unlink($filePath);
        });
        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }
        return $response;
    }

    public function getFilesystem(string $disk = 'local'): FilesystemOperator
    {
        /** @var FilesystemOperator $filesystem */
        $filesystem = Storage::disk($disk)->getDriver();
        return $filesystem;
    }

    public function defer(callable $callback): void
    {
        $callback();
    }
}
