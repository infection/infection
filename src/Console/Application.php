<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Console;

use Infection\Command;
use Infection\Config\InfectionConfig;
use Infection\Php\ConfigBuilder;
use Infection\Php\XdebugHandler;
use Pimple\Container;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Application extends BaseApplication
{
    const NAME = 'Infection - PHP Mutation Testing Framework';
    const VERSION = '@package_version@';
    const RUNNING_WITH_NOTE = 'You are running Infection with %s enabled.';
    const LOGO = <<<'ASCII'
    ____      ____          __  _
   /  _/___  / __/__  _____/ /_(_)___  ____ 
   / // __ \/ /_/ _ \/ ___/ __/ / __ \/ __ \
 _/ // / / / __/  __/ /__/ /_/ / /_/ / / / /
/___/_/ /_/_/  \___/\___/\__/_/\____/_/ /_/
 
ASCII;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var bool
     */
    private $isXdebugLoaded;

    /**
     * @var bool
     */
    private $isDebuggerDisabled;

    public function __construct(Container $container, string $name = self::NAME, string $version = self::VERSION)
    {
        $this->container = $container;
        $this->isDebuggerDisabled = ('' === trim((string) getenv(XdebugHandler::ENV_DISABLE_XDEBUG)));
        $this->isXdebugLoaded = \extension_loaded('xdebug');

        parent::__construct($name, $version);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }

        $this->io = new SymfonyStyle($input, $output);

        if (PHP_SAPI === 'phpdbg') {
            $this->io->writeln(sprintf(self::RUNNING_WITH_NOTE, PHP_SAPI));
        } elseif ($this->isXdebugLoaded) {
            $this->io->writeln(sprintf(self::RUNNING_WITH_NOTE, 'xdebug'));
        }

        $xdebug = new XdebugHandler(new ConfigBuilder(sys_get_temp_dir()));
        $xdebug->check();

        if (PHP_SAPI !== 'phpdbg' && $this->isDebuggerDisabled && !$this->isXdebugLoaded) {
            $this->io->error([
                'Neither phpdbg or xdebug has been found. One of those is required by Infection in order to generate coverage data. Either:',
                '- Enable xdebug and run infection again' . PHP_EOL . '- Use phpdbg: phpdbg -qrr infection',
            ]);

            return 1;
        }

        return parent::run($input, $output);
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->buildDynamicDependencies($input);

        $output->writeln(self::LOGO);

        return parent::doRun($input, $output);
    }

    protected function getDefaultCommands()
    {
        $commands = array_merge(parent::getDefaultCommands(), [
            new Command\ConfigureCommand(),
            new Command\InfectionCommand(),
        ]);

        if (0 === strpos(__FILE__, 'phar:')) {
            $commands[] = new Command\SelfUpdateCommand();
        }

        return $commands;
    }

    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        parent::configureIO($input, $output);

        $output->getFormatter()->setStyle('with-error', new OutputFormatterStyle('green'));
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

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getIO(): SymfonyStyle
    {
        return $this->io;
    }

    private function buildDynamicDependencies(InputInterface $input)
    {
        $this->container['infection.config'] = function (Container $c) use ($input): InfectionConfig {
            try {
                $configPaths = [];
                $customConfigPath = $input->getOption('configuration');

                if ($customConfigPath) {
                    $configPaths[] = $customConfigPath;
                }

                array_push(
                    $configPaths,
                    InfectionConfig::CONFIG_FILE_NAME,
                    InfectionConfig::CONFIG_FILE_NAME . '.dist'
                );

                $infectionConfigFile = $c['locator']->locateAnyOf($configPaths);
                $configLocation = \pathinfo($infectionConfigFile, PATHINFO_DIRNAME);
                $json = file_get_contents($infectionConfigFile);
            } catch (\Exception $e) {
                $json = '{}';
                $configLocation = getcwd();
            }

            return new InfectionConfig(json_decode($json), $c['filesystem'], $configLocation);
        };

        $this->container['coverage.path'] = function (Container $c) use ($input): string {
            $existingCoveragePath = trim($input->getOption('coverage'));

            if ($existingCoveragePath === '') {
                return $c['tmp.dir'];
            }

            return $c['filesystem']->isAbsolutePath($existingCoveragePath)
                ? $existingCoveragePath
                : sprintf('%s/%s', getcwd(), $existingCoveragePath);
        };
    }
}
