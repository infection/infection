<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Command;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Completion\CompletionInput;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Completion\CompletionSuggestions;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputArgument;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputOption;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\ConsoleOutputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
final class DumpCompletionCommand extends Command
{
    protected static $defaultName = 'completion';
    protected static $defaultDescription = 'Dump the shell completion script';
    public function complete(CompletionInput $input, CompletionSuggestions $suggestions) : void
    {
        if ($input->mustSuggestArgumentValuesFor('shell')) {
            $suggestions->suggestValues($this->getSupportedShells());
        }
    }
    protected function configure()
    {
        $fullCommand = $_SERVER['PHP_SELF'];
        $commandName = \basename($fullCommand);
        $fullCommand = @\realpath($fullCommand) ?: $fullCommand;
        $this->setHelp(<<<EOH
The <info>%command.name%</> command dumps the shell completion script required
to use shell autocompletion (currently only bash completion is supported).

<comment>Static installation
-------------------</>

Dump the script to a global completion file and restart your shell:

    <info>%command.full_name% bash | sudo tee /etc/bash_completion.d/{$commandName}</>

Or dump the script to a local file and source it:

    <info>%command.full_name% bash > completion.sh</>

    <comment># source the file whenever you use the project</>
    <info>source completion.sh</>

    <comment># or add this line at the end of your "~/.bashrc" file:</>
    <info>source /path/to/completion.sh</>

<comment>Dynamic installation
--------------------</>

Add this to the end of your shell configuration file (e.g. <info>"~/.bashrc"</>):

    <info>eval "\$({$fullCommand} completion bash)"</>
EOH
)->addArgument('shell', InputArgument::OPTIONAL, 'The shell type (e.g. "bash"), the value of the "$SHELL" env var will be used if this is not given')->addOption('debug', null, InputOption::VALUE_NONE, 'Tail the completion debug log');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $commandName = \basename($_SERVER['argv'][0]);
        if ($input->getOption('debug')) {
            $this->tailDebugLog($commandName, $output);
            return self::SUCCESS;
        }
        $shell = $input->getArgument('shell') ?? self::guessShell();
        $completionFile = __DIR__ . '/../Resources/completion.' . $shell;
        if (!\file_exists($completionFile)) {
            $supportedShells = $this->getSupportedShells();
            ($output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output)->writeln(\sprintf('<error>Detected shell "%s", which is not supported by Symfony shell completion (supported shells: "%s").</>', $shell, \implode('", "', $supportedShells)));
            return self::INVALID;
        }
        $output->write(\str_replace(['{{ COMMAND_NAME }}', '{{ VERSION }}'], [$commandName, $this->getApplication()->getVersion()], \file_get_contents($completionFile)));
        return self::SUCCESS;
    }
    private static function guessShell() : string
    {
        return \basename($_SERVER['SHELL'] ?? '');
    }
    private function tailDebugLog(string $commandName, OutputInterface $output) : void
    {
        $debugFile = \sys_get_temp_dir() . '/sf_' . $commandName . '.log';
        if (!\file_exists($debugFile)) {
            \touch($debugFile);
        }
        $process = new Process(['tail', '-f', $debugFile], null, null, null, 0);
        $process->run(function (string $type, string $line) use($output) : void {
            $output->write($line);
        });
    }
    private function getSupportedShells() : array
    {
        return \array_map(function ($f) {
            return \pathinfo($f, \PATHINFO_EXTENSION);
        }, \glob(__DIR__ . '/../Resources/completion.*'));
    }
}
