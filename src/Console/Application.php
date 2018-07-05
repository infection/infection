<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Console;

use Composer\XdebugHandler\XdebugHandler;
use Infection\Command;
use Infection\Console\ConsoleOutput as InfectionConsoleOutput;
use Infection\Console\Util\PhpProcess;
use PackageVersions\Versions;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
final class Application extends BaseApplication
{
    const NAME = 'Infection - PHP Mutation Testing Framework';
    const VERSION = '@package_version@';

    const INFECTION_PREFIX = 'INFECTION';

    const LOGO = <<<'ASCII'
    ____      ____          __  _
   /  _/___  / __/__  _____/ /_(_)___  ____ 
   / // __ \/ /_/ _ \/ ___/ __/ / __ \/ __ \
 _/ // / / / __/  __/ /__/ /_/ / /_/ / / / /
/___/_/ /_/_/  \___/\___/\__/_/\____/_/ /_/
 
ASCII;

    /**
     * @var InfectionContainer
     */
    private $container;

    /**
     * @var InfectionConsoleOutput
     */
    private $consoleOutput;

    public function __construct(InfectionContainer $container, string $name = self::NAME, string $version = self::VERSION)
    {
        parent::__construct($name, $version);

        $this->container = $container;
        $this->setDefaultCommand('run');
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }

        $this->consoleOutput = new InfectionConsoleOutput(new SymfonyStyle($input, $output));

        $this->logRunningWithDebugger($input);

        if (!$this->isAutoExitEnabled()) {
            // When we're not in control of exit codes, that means it's the caller
            // responsibility to disable xdebug if it isn't needed. As of writing
            // that's only the case during E2E testing. Show a warning nevertheless.

            $this->consoleOutput->logNotInControlOfExitCodes();

            return parent::run($input, $output);
        }

        $xdebug = new XdebugHandler(self::INFECTION_PREFIX, '--ansi');
        $xdebug->check();

        /*
         * If we're skipping Xdebug, setup a default Xdebug-free environment for all subprocesses
         */
        if ('' !== XdebugHandler::getSkippedVersion()) {
            PhpProcess::setupXdebugFreeEnvironment();
        }

        return parent::run($input, $output);
    }

    private function logRunningWithDebugger(InputInterface $input)
    {
        if (!$input->hasParameterOption(array('--quiet', '-q'), true)) {
            if (\PHP_SAPI === 'phpdbg') {
                $this->consoleOutput->logRunningWithDebugger(\PHP_SAPI);
            } elseif (\extension_loaded('xdebug')) {
                $this->consoleOutput->logRunningWithDebugger('xdebug');
            }
        }
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->buildDynamicDependencies($input);

        $output->writeln(self::LOGO);

        return parent::doRun($input, $output);
    }

    public function getLongVersion()
    {
        if (self::VERSION === $this->getVersion()) {
            $version = Versions::getVersion('infection/infection');

            return sprintf('%s <info>%s</info>', $this->getName(), explode('@', $version)[0]);
        }

        return parent::getLongVersion();
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

    public function getContainer(): InfectionContainer
    {
        return $this->container;
    }

    public function getConsoleOutput(): InfectionConsoleOutput
    {
        return $this->consoleOutput;
    }
}
