<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Command;

use BusinessG\LaravelExcel\Data\Export\ExportConfig;
use BusinessG\LaravelExcel\ExcelInterface;
use Illuminate\Console\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ExportCommand extends AbstractCommand
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
        $this->setName('excel:export')
            ->setDescription('Run export')
            ->addArgument('config', InputArgument::REQUIRED, 'The config of export.')
            ->addOption('progress', 'g', InputOption::VALUE_NEGATABLE, 'The progress of export.', true)
            ->addUsage('excel:export "App\Excel\DemoExportConfig"')
            ->addUsage('excel:export "App\Excel\DemoExportConfig" --no-progress');
    }

    public function handle()
    {
        $config = $this->argument('config');
        $progress = $this->option('progress');

        /**
         * @var ExportConfig $config
         */
        $config = new $config([]);
        if (!$config instanceof ExportConfig) {
            $this->error('Invalid config: expected instance of ' . ExportConfig::class);
            return 1;
        }

        $data = $this->excel->export($config);

        $this->table(['token'], [[$data->token]]);

        if ($progress) {
            $this->showProgress($data->token);
        }
        return 0;
    }
}
