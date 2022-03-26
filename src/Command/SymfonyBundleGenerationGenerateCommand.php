<?php

namespace tomdkd\SymfonyBundleGenerationBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SymfonyBundleGenerationGenerateCommand extends Command
{
    protected static $defaultName = 'bundle:generate';

    protected function configure(): void
    {
        $this->setHelp('Use this command to generate the base of your new Symfony bundle.');
        $this->setDescription('Use this command to generate the base of your new Symfony bundle.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return Command::SUCCESS;
    }
}