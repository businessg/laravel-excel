<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Db;

use BusinessG\BaseExcel\Data\BaseConfig;
use BusinessG\BaseExcel\Data\Export\ExportConfig;
use BusinessG\BaseExcel\Progress\ProgressData;
use BusinessG\BaseExcel\Progress\ProgressInterface;
use BusinessG\BaseExcel\Progress\ProgressRecord;
use BusinessG\LaravelExcel\Db\Model\ExcelLog as ExcelLogModel;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ExcelLogManager implements ExcelLogInterface
{
    public string $model;
    public const TYPE_EXPORT = 'export';
    public const TYPE_IMPORT = 'import';

    protected array $config;

    public function __construct(protected ContainerInterface $container, protected ProgressInterface $progress)
    {
        $this->config = config('excel.dbLog', [
            'enable' => true,
            'model' => ExcelLogModel::class,
        ]);
        $this->model = $this->config['model'] ?? ExcelLogModel::class;
    }

    /**
     * 保存记录信息
     *
     * @param BaseConfig $config
     * @param array $saveParam
     * @return int
     */
    public function saveLog(BaseConfig $config, array $saveParam = []): int
    {
        $token = $config->getToken();

        $type = $config instanceof ExportConfig ? static::TYPE_EXPORT : static::TYPE_IMPORT;

        $progressRecord = $this->getProgressByToken($token);

        $saveParam = array_merge($saveParam, [
            'token' => $token,
            'config_class' => get_class($config),
            'config' => json_encode($config->__serialize()),
            'type' => $type,
            'service_name' => $config->serviceName,
            'progress' => json_encode($progressRecord?->progress),
            'sheet_progress' => json_encode($progressRecord?->sheetListProgress),
            'status' => $progressRecord?->progress->status ?: ProgressData::PROGRESS_STATUS_AWAIT,
            'data' => json_encode($progressRecord?->data ?: []),
        ]);
        if ($type == static::TYPE_EXPORT) {
            $saveParam['url'] = $progressRecord?->data?->response ?? "";
        } else {
            $saveParam['url'] = $config->getPath();
        }
        return $this->model::query()->upsert([$saveParam], ['token']);
    }


    /**
     * 获取进度
     *
     * @param string $token
     * @return ProgressRecord|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getProgressByToken(string $token): ?ProgressRecord
    {
        return $this->progress->getRecordByToken($token);
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
