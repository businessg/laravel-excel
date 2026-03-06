<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Queue\AsyncQueue\Job;

class ImportJob extends BaseJob
{
    public function handle()
    {
        $this->getExcel()->import($this->config->setIsAsync(false));
    }
}
