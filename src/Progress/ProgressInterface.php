<?php

namespace BusinessG\LaravelExcel\Progress;

use BusinessG\LaravelExcel\Data\BaseConfig;

interface ProgressInterface
{
    public function initRecord(BaseConfig $config): ProgressRecord;
    public function getRecord(BaseConfig $config): ProgressRecord;
    public function getRecordByToken(string $token): ?ProgressRecord;
    public function setSheetProgress(BaseConfig $config, string $sheetName, ProgressData $progressData): ProgressData;
    public function setProgress(BaseConfig $config, ProgressData $progressData): ProgressRecord;
    public function pushMessage(string $token, string $message);
    public function popMessage(string $token, int $num): array;

}