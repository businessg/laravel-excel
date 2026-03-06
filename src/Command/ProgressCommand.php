<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Command;

use Illuminate\Console\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

class ProgressCommand extends AbstractCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('excel:progress')
            ->setDescription('View progress information')
            ->addArgument('token', InputArgument::REQUIRED, 'The token of excel.')
            ->addUsage('excel:progress 168d8baf7fbc435c8ef18239e932b101');
    }

    public function handle()
    {
        $token = $this->argument('token');
        $this->showProgress($token);
        return 0;
    }
}
