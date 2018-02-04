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
use Pimple\Container;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    const NAME = 'Infection - PHP Mutation Testing Framework';
    const VERSION = '@package_version@';
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

    public function __construct(Container $container, string $name = self::NAME, string $version = self::VERSION)
    {
        $this->container = $container;

        parent::__construct($name, $version);
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
                $json = file_get_contents($infectionConfigFile);
            } catch (\Exception $e) {
                $json = '{}';
            }

            return new InfectionConfig(json_decode($json));
        };

        $this->container['tmp.dir'] = function (Container $c): string {
            return $c['tmp.dir.creator']->createAndGet($c['infection.config']->getTmpDir());
        };
    }
}
