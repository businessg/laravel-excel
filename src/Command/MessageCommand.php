<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Command;

use BusinessG\LaravelExcel\ExcelInterface;
use BusinessG\LaravelExcel\Progress\ProgressInterface;
use Illuminate\Console\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MessageCommand extends AbstractCommand
{
    protected ContainerInterface $container;
    protected ExcelInterface $excel;
    protected ProgressInterface $progress;

    public function __construct(ContainerInterface $container, ExcelInterface $excel)
    {
        $this->container = $container;
        $this->excel = $excel;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('excel:message')
            ->setDescription('View progress messages')
            ->addArgument('token', InputArgument::REQUIRED, 'The token of excel.')
            ->addOption('num', 'c', InputOption::VALUE_REQUIRED, 'The message num of excel.', 50)
            ->addOption('progress', 'g', InputOption::VALUE_NEGATABLE, 'The progress of export.', true)
            ->addUsage('excel:message 168d8baf7fbc435c8ef18239e932b101')
            ->addUsage('excel:message 168d8baf7fbc435c8ef18239e932b101 --no-progress');
    }

    public function handle()
    {
        $token = $this->argument('token');
        $num = (int) $this->option('num');
        $progress = $this->option('progress');

        $this->info("开始获取信息:");
        do {
            $progressRecord = $this->excel->getProgressRecord($token);
            if (!$progressRecord) {
                $this->error('未找到进度记录');
                return 1;
            }
            $isEnd = false;
            $messages = $this->excel->popMessageAndIsEnd($token, $num, $isEnd);
            foreach ($messages as $message) {
                $this->line($message);
            }
            usleep(500000);
        } while (!$isEnd);

        if ($progress) {
            $this->showProgress($token);
        }
        return 0;
    }
}
