<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Driver;

use BusinessG\BaseExcel\Data\Export\ExportConfig;
use BusinessG\BaseExcel\Driver\XlsWriterDriver as BaseXlsWriterDriver;
use BusinessG\LaravelExcel\LaravelEventDispatcherAdapter;
use BusinessG\BaseExcel\Helper\Helper;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemOperator;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class XlsWriterDriver extends BaseXlsWriterDriver
{
    public function __construct(ContainerInterface $container, array $config, string $name)
    {
        $event = $container->has(EventDispatcherInterface::class)
            ? $container->get(EventDispatcherInterface::class)
            : new LaravelEventDispatcherAdapter($container->get(\Illuminate\Contracts\Events\Dispatcher::class));

        $storage = Storage::disk($config['filesystem']['storage'] ?? 'local');
        /** @var FilesystemOperator $filesystem */
        $filesystem = $storage->getDriver();

        parent::__construct($container, $config, $name, $event, $filesystem);
    }

    protected function exportOutPutStream(ExportConfig $config, string $filePath, string $path): StreamedResponse
    {
        $fileName = basename($path);
        $response = new StreamedResponse(function () use ($filePath) {
            $stream = fopen($filePath, 'r');
            fpassthru($stream);
            fclose($stream);
            $this->deleteFile($filePath);
        });
        foreach (Helper::getExportResponseHeaders($fileName, $filePath) as $name => $value) {
            $response->headers->set($name, $value);
        }
        return $response;
    }

    protected function getTempDirSuffix(): string
    {
        return 'laravel-excel';
    }
}
