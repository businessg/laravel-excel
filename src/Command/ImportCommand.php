<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Command;

use BusinessG\BaseExcel\Console\ImportCommandHandler;
use Illuminate\Console\Command;

class ImportCommand extends Command
{
    public function __construct(protected ImportCommandHandler $handler)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(ImportCommandHandler::getCommandName());
        ImportCommandHandler::configureTo($this);
    }

    public function handle(): int
    {
        return $this->handler->handle(
            $this->input->getArgument('config'),
            $this->input->getArgument('path'),
            $this->input->getOption('progress'),
            $this->output
        )['exitCode'];
    }
}
