<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel;

use BusinessG\BaseExcel\Config\ExcelConfig;
use BusinessG\BaseExcel\Contract\ConfigResolverInterface;
use BusinessG\BaseExcel\Contract\RedisAdapterInterface;
use BusinessG\BaseExcel\Progress\Storage\PhpRedisAdapter;
use Illuminate\Support\Facades\Redis;

/**
 * Laravel-specific Redis adapter that resolves the Redis connection
 * from Laravel's Redis facade, then delegates to PhpRedisAdapter.
 */
class LaravelRedisAdapter implements RedisAdapterInterface
{
    private PhpRedisAdapter $delegate;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $excelConfig = ExcelConfig::fromArray($configResolver->get('excel', []));
        $redis = Redis::connection($excelConfig->progress->connection)->client();
        $this->delegate = new PhpRedisAdapter($redis);
    }

    public function get(string $key): ?string
    {
        return $this->delegate->get($key);
    }

    public function setex(string $key, int $ttl, string $value): void
    {
        $this->delegate->setex($key, $ttl, $value);
    }

    public function eval(string $script, array $keys, array $args): mixed
    {
        return $this->delegate->eval($script, $keys, $args);
    }

    public function rpop(string $key): ?string
    {
        return $this->delegate->rpop($key);
    }

    public function lrange(string $key, int $start, int $stop): array
    {
        return $this->delegate->lrange($key, $start, $stop);
    }
}
