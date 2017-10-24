<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Command;

use Infection\Console\LogVerbosity;
use Infection\InfectionApplication;
use Infection\Config\InfectionConfig;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InfectionCommand extends Command
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Runs the mutation testing.')
            ->addOption(
                'test-framework',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the Test framework to use (phpunit, phpspec)',
                'phpunit'
            )
            ->addOption(
                'test-framework-options',
                null,
                InputOption::VALUE_REQUIRED,
                'Options to be passed to the test framework'
            )
            ->addOption(
                'threads',
                null,
                InputOption::VALUE_REQUIRED,
                'Threads count',
                1
            )
            ->addOption(
                'only-covered',
                null,
                InputOption::VALUE_NONE,
                'Mutate only covered by tests lines of code'
            )
            ->addOption(
                'show-mutations',
                's',
                InputOption::VALUE_NONE,
                'Show mutations to the console'
            )
            ->addOption(
                'configuration',
                'c',
                InputOption::VALUE_REQUIRED,
                'Custom configuration file path'
            )
            ->addOption(
                'mutators',
                null,
                InputOption::VALUE_REQUIRED,
                'Specify particular mutators. Example: --mutators=Plus,PublicVisibility'
            )
            ->addOption(
                'filter',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter which files to mutate',
                ''
            )
            ->addOption(
                'formatter',
                null,
                InputOption::VALUE_REQUIRED,
                'Output formatter. Possible values: dot, progress',
                'dot'
            )
            ->addOption(
                'min-msi',
                null,
                InputOption::VALUE_REQUIRED,
                'Minimum Mutation Score Indicator (MSI) percentage value. Should be used in CI server.'
            )
            ->addOption(
                'min-covered-msi',
                null,
                InputOption::VALUE_REQUIRED,
                'Minimum Covered Code Mutation Score Indicator (MSI) percentage value. Should be used in CI server.'
            )
            ->addOption(
                'log-verbosity',
                null,
                InputOption::VALUE_OPTIONAL,
                'Log verbosity level. 1 - full logs format, 2 - short logs format.',
                LogVerbosity::DEBUG
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container['infection.config'] = function (Container $c) use ($input): InfectionConfig {
            try {
                $configPaths = [
                    InfectionConfig::CONFIG_FILE_NAME,
                    InfectionConfig::CONFIG_FILE_NAME . '.dist',
                ];
                $customConfigPath = $input->getOption('configuration');
                if ($customConfigPath) {
                    $configPaths[] = $customConfigPath;
                }
                $infectionConfigFile = $c['locator']->locateAnyOf($configPaths);
                $json = file_get_contents($infectionConfigFile);
            } catch (\Exception $e) {
                $json = '{}';
            }

            return new InfectionConfig(json_decode($json));
        };

        $app = new InfectionApplication($this->container, $input, $output);

        return $app->run();
    }

    /**
     * Run configuration command if config does not exist
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->setOutputFormatterStyles($output);

        $customConfigPath = $input->getOption('configuration');
        $configExists = $customConfigPath && file_exists($customConfigPath);

        if (!$configExists) {
            $configExists =
                file_exists(InfectionConfig::CONFIG_FILE_NAME) ||
                file_exists(InfectionConfig::CONFIG_FILE_NAME . '.dist');
        }

        if (!$configExists) {
            $configureCommand = $this->getApplication()->find('configure');

            $args = [
                '--test-framework' => $input->getOption('test-framework'),
            ];

            $result = $configureCommand->run(new ArrayInput($args), $output);

            if ($result !== 0) {
                throw new \Exception('Configuration aborted');
            }
        }

        if (PHP_SAPI !== 'phpdbg' && !defined('HHVM_VERSION') && !extension_loaded('xdebug')) {
            throw new \Exception('You need to use phpdbg or install and enable xDebug in order to allow for code coverage generation.');
        }
    }

    private function setOutputFormatterStyles(OutputInterface $output)
    {
        $output->getFormatter()->setStyle('with-error', new OutputFormatterStyle('red'));
        $output->getFormatter()->setStyle('uncovered', new OutputFormatterStyle('blue', null, ['bold']));
        $output->getFormatter()->setStyle('timeout', new OutputFormatterStyle('yellow'));
        $output->getFormatter()->setStyle('escaped', new OutputFormatterStyle('red', null, ['bold']));
        $output->getFormatter()->setStyle('killed', new OutputFormatterStyle('green'));
        $output->getFormatter()->setStyle('code', new OutputFormatterStyle('white'));

        $output->getFormatter()->setStyle('diff-add', new OutputFormatterStyle('green'));
        $output->getFormatter()->setStyle('diff-del', new OutputFormatterStyle('red'));

        $output->getFormatter()->setStyle('low', new OutputFormatterStyle('red', null, ['bold']));
        $output->getFormatter()->setStyle('medium', new OutputFormatterStyle('yellow', null, ['bold']));
        $output->getFormatter()->setStyle('high', new OutputFormatterStyle('green', null, ['bold']));
    }
}
