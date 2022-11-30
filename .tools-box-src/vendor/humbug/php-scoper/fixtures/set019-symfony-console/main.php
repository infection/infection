<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19;

use _HumbugBoxb47773b41c19\PhpParser\NodeDumper;
use _HumbugBoxb47773b41c19\PhpParser\ParserFactory;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Application;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\Command;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
require \file_exists(__DIR__ . '/vendor/scoper-autoload.php') ? __DIR__ . '/vendor/scoper-autoload.php' : __DIR__ . '/vendor/autoload.php';
class HelloWorldCommand extends Command
{
    protected function configure() : void
    {
        $this->setName('hello:world')->setDescription('Outputs \'Hello World\'');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln('Hello world!');
        return self::SUCCESS;
    }
}
\class_alias('_HumbugBoxb47773b41c19\\HelloWorldCommand', 'HelloWorldCommand', \false);
$command = new HelloWorldCommand();
$application = new Application();
$application->add($command);
$application->setDefaultCommand($command->getName());
$application->run();
