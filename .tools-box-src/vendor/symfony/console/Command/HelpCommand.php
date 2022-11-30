<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Command;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Descriptor\ApplicationDescription;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper\DescriptorHelper;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputArgument;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputOption;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
class HelpCommand extends Command
{
    private Command $command;
    protected function configure()
    {
        $this->ignoreValidationErrors();
        $this->setName('help')->setDefinition([new InputArgument('command_name', InputArgument::OPTIONAL, 'The command name', 'help', function () {
            return \array_keys((new ApplicationDescription($this->getApplication()))->getCommands());
        }), new InputOption('format', null, InputOption::VALUE_REQUIRED, 'The output format (txt, xml, json, or md)', 'txt', function () {
            return (new DescriptorHelper())->getFormats();
        }), new InputOption('raw', null, InputOption::VALUE_NONE, 'To output raw command help')])->setDescription('Display help for a command')->setHelp(<<<'EOF'
The <info>%command.name%</info> command displays help for a given command:

  <info>%command.full_name% list</info>

You can also output the help in other formats by using the <comment>--format</comment> option:

  <info>%command.full_name% --format=xml list</info>

To display the list of available commands, please use the <info>list</info> command.
EOF
);
    }
    public function setCommand(Command $command)
    {
        $this->command = $command;
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->command ??= $this->getApplication()->find($input->getArgument('command_name'));
        $helper = new DescriptorHelper();
        $helper->describe($output, $this->command, ['format' => $input->getOption('format'), 'raw_text' => $input->getOption('raw')]);
        unset($this->command);
        return 0;
    }
}
