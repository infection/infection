<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\ConsoleOutputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Exception\ProcessFailedException;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
class ProcessHelper extends Helper
{
    public function run(OutputInterface $output, $cmd, string $error = null, callable $callback = null, int $verbosity = OutputInterface::VERBOSITY_VERY_VERBOSE) : Process
    {
        if (!\class_exists(Process::class)) {
            throw new \LogicException('The ProcessHelper cannot be run as the Process component is not installed. Try running "compose require symfony/process".');
        }
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }
        $formatter = $this->getHelperSet()->get('debug_formatter');
        if ($cmd instanceof Process) {
            $cmd = [$cmd];
        }
        if (!\is_array($cmd)) {
            throw new \TypeError(\sprintf('The "command" argument of "%s()" must be an array or a "%s" instance, "%s" given.', __METHOD__, Process::class, \get_debug_type($cmd)));
        }
        if (\is_string($cmd[0] ?? null)) {
            $process = new Process($cmd);
            $cmd = [];
        } elseif (($cmd[0] ?? null) instanceof Process) {
            $process = $cmd[0];
            unset($cmd[0]);
        } else {
            throw new \InvalidArgumentException(\sprintf('Invalid command provided to "%s()": the command should be an array whose first element is either the path to the binary to run or a "Process" object.', __METHOD__));
        }
        if ($verbosity <= $output->getVerbosity()) {
            $output->write($formatter->start(\spl_object_hash($process), $this->escapeString($process->getCommandLine())));
        }
        if ($output->isDebug()) {
            $callback = $this->wrapCallback($output, $process, $callback);
        }
        $process->run($callback, $cmd);
        if ($verbosity <= $output->getVerbosity()) {
            $message = $process->isSuccessful() ? 'Command ran successfully' : \sprintf('%s Command did not run successfully', $process->getExitCode());
            $output->write($formatter->stop(\spl_object_hash($process), $message, $process->isSuccessful()));
        }
        if (!$process->isSuccessful() && null !== $error) {
            $output->writeln(\sprintf('<error>%s</error>', $this->escapeString($error)));
        }
        return $process;
    }
    public function mustRun(OutputInterface $output, $cmd, string $error = null, callable $callback = null) : Process
    {
        $process = $this->run($output, $cmd, $error, $callback);
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return $process;
    }
    public function wrapCallback(OutputInterface $output, Process $process, callable $callback = null) : callable
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }
        $formatter = $this->getHelperSet()->get('debug_formatter');
        return function ($type, $buffer) use($output, $process, $callback, $formatter) {
            $output->write($formatter->progress(\spl_object_hash($process), $this->escapeString($buffer), Process::ERR === $type));
            if (null !== $callback) {
                $callback($type, $buffer);
            }
        };
    }
    private function escapeString(string $str) : string
    {
        return \str_replace('<', '\\<', $str);
    }
    public function getName() : string
    {
        return 'process';
    }
}
