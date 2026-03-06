<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Command;

use BusinessG\LaravelExcel\Data\Import\ImportConfig;
use BusinessG\LaravelExcel\ExcelInterface;
use Illuminate\Console\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ImportCommand extends AbstractCommand
{
    protected ContainerInterface $container;
    protected ExcelInterface $excel;

    public function __construct(ContainerInterface $container, ExcelInterface $excel)
    {
        $this->container = $container;
        $this->excel = $excel;
        parent::__construct();
    }

    protected function configure()
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

    public function handle()
    {
        $config = $this->argument('config');
        $path = $this->argument('path');
        $progress = $this->option('progress');

        /**
         * @var ImportConfig $config
         */
        $config = new $config([]);
        if (!$config instanceof ImportConfig) {
            $this->error('Invalid config: expected instance of ' . ImportConfig::class);
            return 1;
        }
        if ($path) {
            $config->setPath($path);
        }
        $data = $this->excel->import($config);

        $this->table(['token'], [[$data->token]]);

        if ($progress) {
            $this->showProgress($data->token);
        }
        return 0;
    }
}
