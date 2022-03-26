<?php

namespace tomdkd\SymfonyBundleGenerationBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use tomdkd\SymfonyBundleGenerationBundle\Controller\SymfonyBundleGenerationController;

class SymfonyBundleGenerationGenerateCommand extends Command
{
    private SymfonyBundleGenerationController $controller;

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

        $this->introduction($output);

        $helper             = $this->getHelper('question');
        $bundleNameQuestion = new Question("What is your bundle name? Ex: Foo \n");
        $pseudoNameQuestion = new Question("Which pseudo do you want to use? \n");
        $bundleName         = $this->controller->formatBundleName(sprintf('%sBundle', ucfirst($helper->ask($input, $output, $bundleNameQuestion))));
        $bundleFolderName   = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $bundleName));

        $this->validate($bundleName, 'Bundle name');

        if (!$this->controller->validateBundleName(trim($bundleName))) {
            $output->writeln('<error>The bundle name is invalid. Bundle name should contains only alphabetics caracters.</error>');
            return Command::FAILURE;
        }

        $pseudoName = $helper->ask($input, $output, $pseudoNameQuestion);
        $namespace  = sprintf('%s\%s', $pseudoName, $bundleName);

        $this->validate($pseudoName, 'Pseudo');

        if (!$this->controller->generateLocalBundleFolder()) {
            $output->writeln('<error>Error during local_bundles folder creation.</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>local_bundles folder successfully created.</info>');

        if (!$this->controller->generateBundleFolder($bundleName)) {
            $output->writeln('<error>Error during bundle project folder creation.</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Main bundle project folder successfully created.</info>');

        if (!$this->controller->generateBaseBundleFile($namespace)) {
            $output->writeln('<error>Error during base file creation.</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Base file successfully created.</info>');

        $this->controller->activateBundle();
        $output->writeln("<info>bundles.php updated.</info>\n\n");

        $this->done($output, $namespace, $bundleFolderName);

        return Command::SUCCESS;
    }

    /**
     * Check if value gave by helper is empty or not.
     *
     * @param $value
     * @param $element
     * @return bool
     * @throws \Exception
     */
    private function validate($value, $element): bool
    {
        if (trim($value) == '') {
            throw new \Exception(sprintf('%s can\'t be empty', $element));
        }

        return true;
    }

    /**
     * Display introduction message
     *
     * @param OutputInterface $output
     * @return void
     */
    private function introduction(OutputInterface $output): void
    {
        $output->writeln([
            '<info>[Bundle generation]</info>',
            '<info>You\'ll be help during the generation process.</info>',
        ]);
    }

    /**
     * Display last message before end of process.
     *
     * @param OutputInterface $output
     * @param $namespace
     * @return void
     */
    private function done(OutputInterface $output, $namespace, $bundleFolderName): void
    {
        $output->writeln([
            '<info>[Generate composer.json for your bundle]</info>',
            'Use <info>composer init</info> inside your bundle folder and follow steps',
            "\n",
            '<info>[Overload your bundle]</info>',
            'Inside your root dir, update your composer.json',
            'In <info>autoload/psr-4</info> add this line :',
            sprintf('   <info>"%s": "local_bundles/%s/src"</info>', $namespace, $bundleFolderName),
            "\n",
            '<info>Enjoy your new bundle and do something amazing!</info>'
        ]);
    }
}