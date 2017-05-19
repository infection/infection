<?php

declare(strict_types=1);

namespace Infection\Command;

use Infection\Config\ConsoleHelper;
use Infection\Config\ValueProvider\ExcludeDirsProvider;
use Infection\Config\ValueProvider\PhpUnitPathProvider;
use Infection\Config\ValueProvider\SourceDirsProvider;
use Infection\Config\ValueProvider\TimeoutProvider;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Infection\Config\InfectionConfig;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigureCommand extends Command
{
    /**
     * @var ConsoleHelper
     */
    private $consoleHelper;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->consoleHelper = new ConsoleHelper($this->getHelper('formatter'));

        $this->consoleHelper->writeSection($output, 'Welcome to the Infection config generator');

        $output->writeln([
            '',
            'We did not find configuration file. The following questions will help us to generate it for you.',
            '',
        ]);

        $dirsInCurrentDir = array_filter(glob('*'), 'is_dir');
        $testFrameworkConfigLocator = new TestFrameworkConfigLocator('.');

        $phpUnitConfigPathProvider = new PhpUnitPathProvider($testFrameworkConfigLocator, $this->consoleHelper, $this->getQuestionHelper());
        $phpUnitConfigPath = $phpUnitConfigPathProvider->get($input, $output, $dirsInCurrentDir, $input->getOption('test-framework'));

        $sourceDirsProvider = new SourceDirsProvider($this->consoleHelper, $this->getQuestionHelper());
        $sourceDirs = $sourceDirsProvider->get($input, $output, $dirsInCurrentDir);

        if (empty($sourceDirs)) {
            $output->writeln('A source directory was not provided. Unable to generate "infection.json.dist".');

            return 1;
        }

        $excludeDirsProvider = new ExcludeDirsProvider($this->consoleHelper, $this->getQuestionHelper());
        $excludedDirs = $excludeDirsProvider->get($input, $output, $dirsInCurrentDir, $sourceDirs);

        $timeoutProvider = new TimeoutProvider($this->consoleHelper, $this->getQuestionHelper());
        $timeout = $timeoutProvider->get($input, $output);

        $this->saveConfig($sourceDirs, $excludedDirs, $timeout, $phpUnitConfigPath);

        return 0;
    }

    protected function configure()
    {
        $this
            ->setName('configure')
            ->setDescription('Configure ....')
            ->addOption(
                'test-framework',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the Test framework to use (phpunit, phpspec)',
                'phpunit'
            )
        ;
    }

    private function saveConfig(array $sourceDirs, array $excludedDirs, int $timeout, string $phpUnitConfigPath = null)
    {
        $configObject = new \stdClass();

        $configObject->timeout = $timeout;
        $configObject->source = new \stdClass();

        if ($sourceDirs) {
            $configObject->source->directories = $sourceDirs;
        }

        if ($excludedDirs) {
            $configObject->source->exclude = $excludedDirs;
        }

        if ($phpUnitConfigPath) {
            $configObject->phpUnit = new \stdClass();
            $configObject->phpUnit->configDir = $phpUnitConfigPath;
        }

        $config = json_encode($configObject, JSON_PRETTY_PRINT);

        file_put_contents(InfectionConfig::CONFIG_FILE_NAME, $config);
    }

    private function getQuestionHelper()
    {
        return $this->getHelper('question');
    }
}