<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Console;

use function array_merge;
use _HumbugBox9658796bb9f0\Composer\InstalledVersions;
use _HumbugBox9658796bb9f0\Infection\Command\ConfigureCommand;
use _HumbugBox9658796bb9f0\Infection\Command\DescribeCommand;
use _HumbugBox9658796bb9f0\Infection\Command\RunCommand;
use _HumbugBox9658796bb9f0\Infection\Container;
use OutOfBoundsException;
use function preg_quote;
use function _HumbugBox9658796bb9f0\Safe\preg_match;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Application as BaseApplication;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
use function trim;
final class Application extends BaseApplication
{
    public const PACKAGE_NAME = 'infection/infection';
    private const NAME = 'Infection - PHP Mutation Testing Framework';
    private const LOGO = '
    ____      ____          __  _
   /  _/___  / __/__  _____/ /_(_)___  ____
   / // __ \\/ /_/ _ \\/ ___/ __/ / __ \\/ __ \\
 _/ // / / / __/  __/ /__/ /_/ / /_/ / / / /
/___/_/ /_/_/  \\___/\\___/\\__/_/\\____/_/ /_/

<fg=blue>#StandWith</><fg=yellow>Ukraine</>

';
    private Container $container;
    public function __construct(Container $container)
    {
        try {
            $version = (string) InstalledVersions::getPrettyVersion(self::PACKAGE_NAME);
        } catch (OutOfBoundsException $e) {
            if (preg_match('#package .*' . preg_quote(self::PACKAGE_NAME, '#') . '.* not installed#i', $e->getMessage()) === 0) {
                throw $e;
            }
            $version = 'not-installed';
        }
        parent::__construct(self::NAME, $version);
        $this->container = $container;
        $this->setDefaultCommand('run');
    }
    public function getContainer() : Container
    {
        return $this->container;
    }
    public function getLongVersion() : string
    {
        return trim(sprintf('<info>%s</info> version <comment>%s</comment>', $this->getName(), $this->getVersion()));
    }
    public function getHelp() : string
    {
        return self::LOGO . parent::getHelp();
    }
    protected function getDefaultCommands() : array
    {
        $commands = array_merge(parent::getDefaultCommands(), [new ConfigureCommand(), new RunCommand(), new DescribeCommand()]);
        return $commands;
    }
    protected function configureIO(InputInterface $input, OutputInterface $output) : void
    {
        parent::configureIO($input, $output);
        if ($this->getContainer()->getCiDetector()->isCiDetected()) {
            $input->setInteractive(\false);
        }
        OutputFormatterStyleConfigurator::configure($output);
    }
}
