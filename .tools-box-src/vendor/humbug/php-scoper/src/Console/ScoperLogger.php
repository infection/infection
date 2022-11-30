<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Console;

use _HumbugBoxb47773b41c19\Fidry\Console\Application\Application as FidryApplication;
use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Throwable\Exception\ParsingException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper\ProgressBar;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\NullOutput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
use function count;
use function memory_get_peak_usage;
use function memory_get_usage;
use function microtime;
use function round;
use function sprintf;
class ScoperLogger
{
    private readonly float $startTime;
    private ProgressBar $progressBar;
    public function __construct(private readonly FidryApplication $application, private readonly IO $io)
    {
        $this->startTime = microtime(\true);
        $this->progressBar = new ProgressBar(new NullOutput());
    }
    public function outputScopingStart(?string $prefix, array $paths) : void
    {
        $this->io->writeln($this->application->getHelp());
        $newLine = 1;
        if ($this->io->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            $this->io->section('Input');
            $this->io->writeln(sprintf('Prefix: %s', $prefix));
            $this->io->write('Paths:');
            if (0 === count($paths)) {
                $this->io->writeln(' Loaded from config');
            } else {
                $this->io->writeln('');
                $this->io->listing($paths);
            }
            $this->io->section('Processing');
            $newLine = 0;
        }
        $this->io->newLine($newLine);
    }
    public function outputFileCount(int $count) : void
    {
        if (OutputInterface::VERBOSITY_NORMAL === $this->io->getVerbosity()) {
            $this->progressBar = $this->io->createProgressBar($count);
            $this->progressBar->start();
        } elseif ($this->io->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->progressBar = new ProgressBar(new NullOutput());
        }
    }
    public function outputSuccess(string $path) : void
    {
        if ($this->io->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->io->writeln(sprintf(' * [<info>OK</info>] %s', $path));
        }
        $this->progressBar->advance();
    }
    public function outputWarnOfFailure(string $path, ParsingException $exception) : void
    {
        if ($this->io->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->io->writeln(sprintf(' * [<error>NO</error>] %s', $path));
        }
        if ($this->io->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $this->io->writeln(sprintf("\t" . '%s: %s', $exception->getMessage(), (string) $exception->getPrevious()));
        }
        $this->progressBar->advance();
    }
    public function outputScopingEnd() : void
    {
        $this->finish(\false);
    }
    public function outputScopingEndWithFailure() : void
    {
        $this->finish(\true);
    }
    private function finish(bool $failed) : void
    {
        $this->progressBar->finish();
        $this->io->newLine(2);
        if (!$failed) {
            $this->io->success(sprintf('Successfully prefixed %d files.', $this->progressBar->getMaxSteps()));
        }
        if ($this->io->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $this->io->comment(sprintf('<info>Memory usage: %.2fMB (peak: %.2fMB), time: %.2fs<info>', round(memory_get_usage() / 1024 / 1024, 2), round(memory_get_peak_usage() / 1024 / 1024, 2), round(microtime(\true) - $this->startTime, 2)));
        }
    }
}
