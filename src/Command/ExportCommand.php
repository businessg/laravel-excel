<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Command;

use BusinessG\BaseExcel\Console\ExportCommandHandler;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ExportCommand extends Command
{
    public function __construct(protected ExportCommandHandler $handler)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('excel:export')
            ->setDescription('Run export')
            ->addArgument('config', InputArgument::REQUIRED, 'The config of export.')
            ->addOption('progress', 'g', InputOption::VALUE_NEGATABLE, 'The progress of export.', true)
            ->addUsage('excel:export "App\Excel\DemoExportConfig"')
            ->addUsage('excel:export "App\Excel\DemoExportConfig" --no-progress');
    }

    public function handle(): int
    {
        $result = $this->handler->handle(
            $this->argument('config'),
            $this->option('progress'),
            $this->output
        );
        return $result['exitCode'];
    }
}
