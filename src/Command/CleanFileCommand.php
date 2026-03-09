<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Command;

use BusinessG\LaravelExcel\Driver\DriverFactory;
use BusinessG\BaseExcel\Helper\Helper;
use BusinessG\LaravelExcel\Logger\ExcelLoggerInterface;
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

        $dirs = [];
        foreach ($configs['drivers'] ?? [] as $key => $item) {
            try {
                $driver = $driverFactory->get($key);
                $dir = $driver->getTempDir();
                if (!$dir || !is_dir($dir) || in_array($dir, $dirs)) {
                    continue;
                }
                $deleted = $this->cleanTempFile($dir, $configs);
                $dirs[] = $dir;
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
        $deletedFiles = [];
        $currentTime = time();

        $files = scandir($directory);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $directory . DIRECTORY_SEPARATOR . $file;

            if (is_file($filePath)) {
                $fileTime = filemtime($filePath);
                $ageSeconds = $currentTime - $fileTime;

                if ($ageSeconds > $maxAgeSeconds) {
                    if (Helper::deleteFile($filePath)) {
                        $deletedFiles[] = $filePath;
                    }
                }
            }
        }
        return $deletedFiles;
    }
}
