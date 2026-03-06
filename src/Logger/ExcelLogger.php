<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Logger;

use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ExcelLogger implements ExcelLoggerInterface
{
    protected LoggerInterface $logger;
    protected array $config;

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = config('excel.logger', [
            'name' => 'stack',
        ]);
        $channel = $this->config['name'] ?? 'stack';
        $this->logger = Log::channel($channel);
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
