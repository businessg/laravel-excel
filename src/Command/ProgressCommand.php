<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Command;

use BusinessG\BaseExcel\Console\ProgressCommandHandler;
use Illuminate\Console\Command;

class ProgressCommand extends Command
{
    public function __construct(protected ProgressCommandHandler $handler)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(ProgressCommandHandler::getCommandName());
        ProgressCommandHandler::configureTo($this);
    }

    public function handle(): int
    {
        return $this->handler->handle($this->input->getArgument('token'), $this->output);
    }
}
