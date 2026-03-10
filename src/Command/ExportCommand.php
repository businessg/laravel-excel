<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Command;

use BusinessG\BaseExcel\Console\ExportCommandHandler;
use Illuminate\Console\Command;

class ExportCommand extends Command
{
    public function __construct(protected ExportCommandHandler $handler)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(ExportCommandHandler::getCommandName());
        ExportCommandHandler::configureTo($this);
    }

    public function handle(): int
    {
        return $this->handler->handle(
            $this->input->getArgument('config'),
            $this->input->getOption('progress'),
            $this->output
        )['exitCode'];
    }
}
