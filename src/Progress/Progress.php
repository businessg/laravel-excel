<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Progress;

use BusinessG\BaseExcel\Data\BaseConfig;
use BusinessG\BaseExcel\Data\BaseObject;
use BusinessG\BaseExcel\Progress\ProgressData;
use BusinessG\BaseExcel\Progress\ProgressInterface;
use BusinessG\BaseExcel\Progress\ProgressRecord;
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
     * @return BaseProgressRecord
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
     * @return BaseProgressRecord
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
     * @return BaseProgressRecord|null
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
     * @param BaseProgressData $progressData
     * @return BaseProgressData
     */
    public function setSheetProgress(BaseConfig $config, string $sheetName, ProgressData $progressData): ProgressData
    {
        $progressRecord = $this->getRecord($config);
        $sheetProgress = $progressRecord->getProgressBySheet($sheetName);
        $sheetProgress->status = $progressData->status;
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

    public function pushMessage(string $token, string $message): void
    {
        $key = $this->getMessageKey($token);
        $this->lpush($key, $message, intval($this->config['expire'] ?? 3600));
        if (config('excel.progress.debug_message', false)) {
            \Illuminate\Support\Facades\Log::debug('[Excel pushMessage]', ['token' => $token, 'key' => $key, 'message' => $message]);
        }
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

    /**
     * 调试用：读取消息列表（不消费），用于排查「推送失败」还是「获取失败」
     * 返回 Redis 中该 token 对应的消息数量及前 N 条内容
     */
    public function peekMessage(string $token, int $num = 50): array
    {
        $key = $this->getMessageKey($token);
        $list = $this->redis->lRange($key, 0, $num - 1) ?: [];
        return [
            'key' => $key,
            'count' => (int) $this->redis->lLen($key),
            'messages' => $list,
        ];
    }

    protected function setProgressStatus(ProgressRecord $progressRecord): ProgressRecord
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
        $this->redis->lPush($key, $value);
        $this->redis->expire($key, $expire);
    }

    protected function set(string $token, ProgressRecord $progressRecord): void
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
