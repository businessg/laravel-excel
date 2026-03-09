<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Command;

use BusinessG\BaseExcel\Console\ImportCommandHandler;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ImportCommand extends Command
{
    public function __construct(protected ImportCommandHandler $handler)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('excel:import')
            ->setDescription('Run import')
            ->addArgument('config', InputArgument::REQUIRED, 'The config of import.')
            ->addArgument('path', InputArgument::REQUIRED, 'The file path of import.')
            ->addOption('progress', 'g', InputOption::VALUE_NEGATABLE, 'The progress path of import.', true)
            ->addUsage('excel:import "App\Excel\DemoImportConfig" "https://xxx.com/demo.xlsx"')
            ->addUsage('excel:import "App\Excel\DemoImportConfig" "/excel/demo.xlsx"')
            ->addUsage('excel:import "App\Excel\DemoImportConfig" "/excel/demo.xlsx" --no-progress');
    }

    public function handle(): int
    {
        $result = $this->handler->handle(
            $this->argument('config'),
            $this->argument('path'),
            $this->option('progress'),
            $this->output
        );
        return $result['exitCode'];
    }
}
