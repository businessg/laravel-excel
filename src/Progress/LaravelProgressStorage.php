<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Progress;

use BusinessG\BaseExcel\Progress\Storage\AbstractProgressStorage;
use Illuminate\Support\Facades\Redis;

/**
 * Laravel Redis 进度存储实现
 */
class LaravelProgressStorage extends AbstractProgressStorage
{
    protected $redis;

    public function __construct()
    {
        $config = config('excel.progress', []);
        $redisConfig = $config['redis'] ?? [];
        $connection = $redisConfig['connection'] ?? 'default';
        $this->redis = Redis::connection($connection);
    }

    public function get(string $key): ?string
    {
        $value = $this->redis->get($key);
        return $value !== null ? (string) $value : null;
    }

    public function set(string $key, string $value, int $ttl): void
    {
        $this->redis->setex($key, $ttl, $value);
    }

    public function lpush(string $key, string $value, int $ttl): void
    {
        $this->redis->eval(static::getLpushLuaScript(), 1, $key, $value, $ttl);
    }

    public function rpop(string $key): ?string
    {
        $value = $this->redis->rPop($key);
        return $value !== null ? (string) $value : null;
    }

    public function lrange(string $key, int $start, int $stop): array
    {
        $result = $this->redis->lRange($key, $start, $stop);
        return $result !== null ? array_map('strval', $result) : [];
    }
}
