<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Command;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Completion\CompletionInput;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Completion\CompletionSuggestions;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Descriptor\ApplicationDescription;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\DescriptorHelper;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputArgument;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputOption;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
class HelpCommand extends Command
{
    private $command;
    protected function configure()
    {
        $this->ignoreValidationErrors();
        $this->setName('help')->setDefinition([new InputArgument('command_name', InputArgument::OPTIONAL, 'The command name', 'help'), new InputOption('format', null, InputOption::VALUE_REQUIRED, 'The output format (txt, xml, json, or md)', 'txt'), new InputOption('raw', null, InputOption::VALUE_NONE, 'To output raw command help')])->setDescription('Display help for a command')->setHelp(<<<'EOF'
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === $this->command) {
            $this->command = $this->getApplication()->find($input->getArgument('command_name'));
        }
        $helper = new DescriptorHelper();
        $helper->describe($output, $this->command, ['format' => $input->getOption('format'), 'raw_text' => $input->getOption('raw')]);
        $this->command = null;
        return 0;
    }
    public function complete(CompletionInput $input, CompletionSuggestions $suggestions) : void
    {
        if ($input->mustSuggestArgumentValuesFor('command_name')) {
            $descriptor = new ApplicationDescription($this->getApplication());
            $suggestions->suggestValues(\array_keys($descriptor->getCommands()));
            return;
        }
        if ($input->mustSuggestOptionValuesFor('format')) {
            $helper = new DescriptorHelper();
            $suggestions->suggestValues($helper->getFormats());
        }
    }
}
