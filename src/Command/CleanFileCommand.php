<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Command;

use BusinessG\LaravelExcel\Driver\DriverFactory;
use BusinessG\BaseExcel\Helper\Helper;
use BusinessG\BaseExcel\Logger\ExcelLoggerInterface;
use Illuminate\Console\Command;

class CleanFileCommand extends Command
{
    protected $signature = 'excel:clean-temp-files';

    protected $description = 'Clean temporary Excel files';

    public function handle(DriverFactory $driverFactory, ExcelLoggerInterface $logger): int
    {
        $configs = config('excel', []);
        if (!($configs['cleanTempFile']['enable'] ?? true)) {
            $this->info('Clean temp file is disabled.');
            return 0;
        }

        $dirs = Helper::getDirectoriesToClean($driverFactory);
        foreach ($dirs as $dir) {
            try {
                $deleted = $this->cleanTempFile($dir, $configs);
                if (!empty($deleted)) {
                    $this->info('Cleaned ' . count($deleted) . ' files from ' . $dir);
                }
            } catch (\Throwable $exception) {
                $logger->getLogger()->error('Cleaning temporary files failed: ' . $exception->getMessage());
                $this->error($exception->getMessage());
            }
        }
        return 0;
    }

    protected function cleanTempFile(string $directory, array $configs): array
    {
        $maxAgeSeconds = $configs['cleanTempFile']['time'] ?? 1800;
        return Helper::cleanTempDirectory($directory, $maxAgeSeconds);
    }
}
