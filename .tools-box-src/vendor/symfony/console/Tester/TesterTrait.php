<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Tester;

use _HumbugBoxb47773b41c19\PHPUnit\Framework\Assert;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\ConsoleOutput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\StreamOutput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Tester\Constraint\CommandIsSuccessful;
trait TesterTrait
{
    private StreamOutput $output;
    private array $inputs = [];
    private bool $captureStreamsIndependently = \false;
    private InputInterface $input;
    private int $statusCode;
    public function getDisplay(bool $normalize = \false) : string
    {
        if (!isset($this->output)) {
            throw new \RuntimeException('Output not initialized, did you execute the command before requesting the display?');
        }
        \rewind($this->output->getStream());
        $display = \stream_get_contents($this->output->getStream());
        if ($normalize) {
            $display = \str_replace(\PHP_EOL, "\n", $display);
        }
        return $display;
    }
    public function getErrorOutput(bool $normalize = \false) : string
    {
        if (!$this->captureStreamsIndependently) {
            throw new \LogicException('The error output is not available when the tester is run without "capture_stderr_separately" option set.');
        }
        \rewind($this->output->getErrorOutput()->getStream());
        $display = \stream_get_contents($this->output->getErrorOutput()->getStream());
        if ($normalize) {
            $display = \str_replace(\PHP_EOL, "\n", $display);
        }
        return $display;
    }
    public function getInput() : InputInterface
    {
        return $this->input;
    }
    public function getOutput() : OutputInterface
    {
        return $this->output;
    }
    public function getStatusCode() : int
    {
        return $this->statusCode ?? throw new \RuntimeException('Status code not initialized, did you execute the command before requesting the status code?');
    }
    public function assertCommandIsSuccessful(string $message = '') : void
    {
        Assert::assertThat($this->statusCode, new CommandIsSuccessful(), $message);
    }
    public function setInputs(array $inputs) : static
    {
        $this->inputs = $inputs;
        return $this;
    }
    private function initOutput(array $options)
    {
        $this->captureStreamsIndependently = \array_key_exists('capture_stderr_separately', $options) && $options['capture_stderr_separately'];
        if (!$this->captureStreamsIndependently) {
            $this->output = new StreamOutput(\fopen('php://memory', 'w', \false));
            if (isset($options['decorated'])) {
                $this->output->setDecorated($options['decorated']);
            }
            if (isset($options['verbosity'])) {
                $this->output->setVerbosity($options['verbosity']);
            }
        } else {
            $this->output = new ConsoleOutput($options['verbosity'] ?? ConsoleOutput::VERBOSITY_NORMAL, $options['decorated'] ?? null);
            $errorOutput = new StreamOutput(\fopen('php://memory', 'w', \false));
            $errorOutput->setFormatter($this->output->getFormatter());
            $errorOutput->setVerbosity($this->output->getVerbosity());
            $errorOutput->setDecorated($this->output->isDecorated());
            $reflectedOutput = new \ReflectionObject($this->output);
            $strErrProperty = $reflectedOutput->getProperty('stderr');
            $strErrProperty->setValue($this->output, $errorOutput);
            $reflectedParent = $reflectedOutput->getParentClass();
            $streamProperty = $reflectedParent->getProperty('stream');
            $streamProperty->setValue($this->output, \fopen('php://memory', 'w', \false));
        }
    }
    private static function createStream(array $inputs)
    {
        $stream = \fopen('php://memory', 'r+', \false);
        foreach ($inputs as $input) {
            \fwrite($stream, $input . \PHP_EOL);
        }
        \rewind($stream);
        return $stream;
    }
}
