<?php

namespace tomdkd\SymfonyBundleGenerationBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use tomdkd\SymfonyBundleGenerationBundle\Controller\SymfonyBundleGenerationController;

class SymfonyBundleGenerationGenerateCommand extends Command
{
    private $controller;

    public function __construct(string $name, SymfonyBundleGenerationController $controller)
    {
        $this->controller = $controller;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setHelp('Use this command to generate the base of your new Symfony bundle.');
        $this->setDescription('Use this command to generate the base of your new Symfony bundle.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '<info>[Bundle generation]</info>',
            '<info>You\'ll be help during the generation process.</info>',
        ]);

        $helper             = $this->getHelper('question');
        $bundleNameQuestion = new Question("What is your bundle name? Ex: Foo \n");
        $pseudoNameQuestion = new Question("Which pseudo do you want to use? \n");

        $bundleNameQuestion->setValidator(function ($value) {
            if (trim($value) == '') {
                throw new \Exception('Bundle name cannot be empty');
            }

            return $value;
        });

        $pseudoNameQuestion->setValidator(function ($value) {
            if (trim($value) == '') {
                throw new \Exception('Pseudo name cannot be empty');
            }

            return $value;
        });

        $bundleName                = sprintf('%sBundle', ucfirst($helper->ask($input, $output, $bundleNameQuestion)));
        $pseudoName                = $helper->ask($input, $output, $pseudoNameQuestion);
        $namespace                 = sprintf('%s\%s', $pseudoName, $bundleName);

        if (!$this->controller->generateLocalBundleFolder()) {
            $output->writeln('<error>Error during local_bundles folder creation.</error>');
        }

        $output->writeln('<info>local_bundles folder successfully created.</info>');

        if (!$this->controller->generateBundleFolder($bundleName)) {
            $output->writeln('<error>Error during bundle project folder creation.</error>');
        }

        $output->writeln('<info>Main bundle project folder successfully created.</info>');

        if (!$this->controller->generateBaseBundleFile($namespace)) {
            $output->writeln('<error>Error during base file creation.</error>');
        }

        $output->writeln('<info>Base file successfully created.</info>');

        return Command::FAILURE;
    }
}