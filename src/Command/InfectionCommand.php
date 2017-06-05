<?php

declare(strict_types=1);

namespace Infection\Command;

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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container['infection.config'] = function (Container $c) : InfectionConfig {
            try {
                $infectionConfigFile = $c['locator']->locateAnyOf(['infection.json', 'infection.json.dist']);
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
     * @throws \Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->setOutputFormatterStyles($output);

        $configExists = file_exists(InfectionConfig::CONFIG_FILE_NAME) ||
            file_exists(InfectionConfig::CONFIG_FILE_NAME . '.dist');

        if (! $configExists) {
            $configureCommand = $this->getApplication()->find('configure');

            $args = [
                '--test-framework' => $input->getOption('test-framework')
            ];

            $result = $configureCommand->run(new ArrayInput($args), $output);

            if ($result !== 0) {
                throw new \Exception('Configuration aborted');
            }
        }

        if (!defined('HHVM_VERSION') && !extension_loaded('xdebug')) {
            throw new \Exception('You need to install and enable xDebug in order to allow for code coverage generation.');
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
    }
}