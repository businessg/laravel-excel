<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Command;

use BusinessG\BaseExcel\Console\ProgressCommandHandler;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class ProgressCommand extends Command
{
    public function __construct(protected ProgressCommandHandler $handler)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('excel:progress')
            ->setDescription('View progress information')
            ->addArgument('token', InputArgument::REQUIRED, 'The token of excel.')
            ->addUsage('excel:progress 168d8baf7fbc435c8ef18239e932b101');
    }

    public function handle(): int
    {
        return $this->handler->handle($this->argument('token'), $this->output);
    }
}
