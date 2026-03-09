<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Command;

use BusinessG\BaseExcel\Console\MessageCommandHandler;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MessageCommand extends Command
{
    public function __construct(protected MessageCommandHandler $handler)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('excel:message')
            ->setDescription('View progress messages')
            ->addArgument('token', InputArgument::REQUIRED, 'The token of excel.')
            ->addOption('num', 'c', InputOption::VALUE_REQUIRED, 'The message num of excel.', 50)
            ->addOption('progress', 'g', InputOption::VALUE_NEGATABLE, 'The progress of export.', true)
            ->addUsage('excel:message 168d8baf7fbc435c8ef18239e932b101')
            ->addUsage('excel:message 168d8baf7fbc435c8ef18239e932b101 --no-progress');
    }

    public function handle(): int
    {
        return $this->handler->handle(
            $this->argument('token'),
            (int) $this->option('num'),
            $this->option('progress'),
            $this->output
        );
    }
}
