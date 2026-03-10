<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Command;

use BusinessG\BaseExcel\Console\MessageCommandHandler;
use Illuminate\Console\Command;

class MessageCommand extends Command
{
    public function __construct(protected MessageCommandHandler $handler)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(MessageCommandHandler::getCommandName());
        MessageCommandHandler::configureTo($this);
    }

    public function handle(): int
    {
        return $this->handler->handle(
            $this->input->getArgument('token'),
            (int) $this->input->getOption('num'),
            $this->input->getOption('progress'),
            $this->output
        );
    }
}
