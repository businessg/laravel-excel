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
            ->addOption('precheck', null, InputOption::VALUE_NONE, 'Run pre-check only, do not import.')
            ->addUsage('excel:import "App\Excel\DemoImportConfig" "https://xxx.com/demo.xlsx"')
            ->addUsage('excel:import "App\Excel\DemoImportConfig" "/excel/demo.xlsx"')
            ->addUsage('excel:import "App\Excel\DemoImportConfig" "/excel/demo.xlsx" --no-progress')
            ->addUsage('excel:import "App\Excel\DemoImportConfig" "/excel/demo.xlsx" --precheck');
    }

    public function handle()
    {
        $config = $this->argument('config');
        $path = $this->argument('path');
        $progress = $this->option('progress');
        $precheck = $this->option('precheck');

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

        if ($precheck) {
            $result = $this->excel->importPreCheck($config);
            $this->table(
                ['passed', 'totalRows', 'validRows', 'invalidRows'],
                [[$result->passed ? 'true' : 'false', $result->totalRows, $result->validRows, $result->invalidRows]]
            );
            if (!empty($result->errors)) {
                $this->newLine();
                $this->warn('Errors:');
                foreach ($result->errors as $err) {
                    $this->line(sprintf('  [%s] Row %d: %s', $err['sheetName'], $err['rowIndex'], implode('; ', $err['errors'])));
                }
            }
            return $result->passed ? 0 : 1;
        }

        $data = $this->excel->import($config);

        $this->table(['token'], [[$data->token]]);

        if ($progress) {
            $this->showProgress($data->token);
        }
        return 0;
    }
}
