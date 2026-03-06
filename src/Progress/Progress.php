<?php

namespace BusinessG\LaravelExcel\Progress;

use BusinessG\LaravelExcel\Data\BaseConfig;
use BusinessG\LaravelExcel\Data\BaseObject;
use BusinessG\LaravelExcel\Driver\Driver;
use Illuminate\Support\Facades\Redis;
use Psr\Container\ContainerInterface;

class Progress implements ProgressInterface
{
    protected $redis;
    protected array $config;

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = config('excel.progress', [
            'enable' => true,
            'prefix' => 'LaravelExcel',
            'expire' => 3600,
            'redis' => [
                'connection' => 'default',
            ]
        ]);
        $connection = $this->config['redis']['connection'] ?? 'default';
        $this->redis = Redis::connection($connection);
    }

    /**
     * 初始化配置
     *
     * @param BaseConfig $config
     * @return ProgressRecord
     */
    public function initRecord(BaseConfig $config): ProgressRecord
    {
        $sheetListProgress = [];
        foreach ($config->getSheets() as $sheet) {
            $sheetListProgress[$sheet->name] = new ProgressData();
        }
        $progressRecord = new ProgressRecord([
            'sheetListProgress' => $sheetListProgress,
            'progress' => new ProgressData(),
        ]);
        $this->set($config->getToken(), $progressRecord);

        return $progressRecord;
    }

    /**
     * 获取进度记录
     *
     * @param BaseConfig $config
     * @return ProgressRecord
     */
    public function getRecord(BaseConfig $config): ProgressRecord
    {
        if (!$record = $this->get($config->getToken())) {
            $record = $this->initRecord($config);
        }
        return $record;
    }

    /**
     * 获取进度记录<token>
     *
     * @param string $token
     * @return ProgressRecord|null
     */
    public function getRecordByToken(string $token): ?ProgressRecord
    {
        return $this->get($token);
    }

    /**
     * 设置页面进度
     *
     * @param BaseConfig $config
     * @param string $sheetName
     * @param ProgressData $progressData
     * @return ProgressData
     */
    public function setSheetProgress(BaseConfig $config, string $sheetName, ProgressData $progressData): ProgressData
    {
        $progressRecord = $this->getRecord($config);
        $sheetProgress = $progressRecord->getProgressBySheet($sheetName);
        $sheetProgress->status = $progressData->status;
        if (!empty($progressData->message)) {
            $sheetProgress->message = $progressData->message;
        }
        if ($progressData->total > 0) {
            $sheetProgress->total = $progressData->total;
        }
        if ($progressData->progress > 0) {
            $sheetProgress->progress += $progressData->progress;
            $progressRecord->progress->progress += $progressData->progress;
            if ($sheetProgress->progress == $sheetProgress->total) {
                $sheetProgress->status = ProgressData::PROGRESS_STATUS_END;
            }
        }
        if ($progressData->success > 0) {
            $sheetProgress->success += $progressData->success;
            $progressRecord->progress->success += $progressData->progress;
        }
        if ($progressData->fail > 0) {
            $sheetProgress->fail += $progressData->fail;
            $progressRecord->progress->fail += $progressData->progress;
        }
        $progressRecord = $this->setProgressStatus($progressRecord);
        $progressRecord->setProgressBySheet($sheetName, $sheetProgress);
        $this->set($config->getToken(), $progressRecord);
        return $sheetProgress;
    }

    public function setProgress(BaseConfig $config, ProgressData $progressData, BaseObject $data = null): ProgressRecord
    {
        $progressRecord = $this->getRecord($config);
        $progressRecord->progress->status = $progressData->status;
        if ($progressData->total > 0) {
            $progressRecord->progress->total = $progressData->total;
        }
        if ($progressData->progress > 0) {
            $progressRecord->progress->progress += $progressData->progress;
        }
        if ($progressData->success > 0) {
            $progressRecord->progress->success += $progressData->progress;
        }
        if ($progressData->fail > 0) {
            $progressRecord->progress->fail += $progressData->progress;
        }
        if (!empty($progressData->message)) {
            $progressRecord->progress->message = $progressData->message;
        }
        if ($data) {
            $progressRecord->data = $data;
        }
        $this->set($config->getToken(), $progressRecord);
        return $progressRecord;
    }

    public function pushMessage(string $token, string $message)
    {
        $this->lpush($this->getMessageKey($token), $message, intval($this->config['expire'] ?? 3600));
    }

    public function popMessage(string $token, int $num): array
    {
        $messages = [];
        $key = $this->getMessageKey($token);
        for ($i = 0; $i < $num; $i++) {
            if ($message = $this->redis->rPop($key)) {
                $messages[] = $message;
            }
        }
        return $messages;
    }

    protected function setProgressStatus(ProgressRecord $progressRecord)
    {
        $total = 0;
        $status = array_map(function ($item) use (&$total) {
            $total += $item->total;
            return $item->status;
        }, $progressRecord->sheetListProgress);
        $status = array_unique($status);
        $count = count($status);
        if ($count <= 1) {
            $progressRecord->progress->status = current($status);
        } else {
            $progressRecord->progress->status = ProgressData::PROGRESS_STATUS_PROCESS;
        }
        $progressRecord->progress->total = $total;
        return $progressRecord;
    }

    protected function lpush(string $key, string $value, int $expire)
    {
        $luaScript = <<<LUA
        redis.call('LPUSH', KEYS[1], ARGV[1])
        redis.call('EXPIRE', KEYS[1], ARGV[2])
        return 1
LUA;
        $this->redis->eval($luaScript, 1, $key, $value, (string) $expire);
    }

    protected function set(string $token, ProgressRecord $progressRecord)
    {
        $key = $this->getProgressKey($token);
        $expire = intval($this->config['expire'] ?? 3600);
        $this->redis->setex($key, $expire, serialize($progressRecord));
    }

    protected function get(string $token): ?ProgressRecord
    {
        $record = $this->redis->get($this->getProgressKey($token));
        if (!$record) {
            return null;
        }
        return unserialize($record);
    }

    protected function getProgressKey(string $token): string
    {
        return sprintf('%s_progress:%s', $this->config['prefix'] ?? 'LaravelExcel', $token);
    }

    protected function getMessageKey(string $token): string
    {
        return sprintf('%s_message:%s', $this->config['prefix'] ?? 'LaravelExcel', $token);
    }

    public function getConfig()
    {
        return $this->config;
    }
}
