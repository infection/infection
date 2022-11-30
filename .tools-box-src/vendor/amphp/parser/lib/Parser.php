<?php

namespace _HumbugBoxb47773b41c19\Amp\Parser;

class Parser
{
    private $generator;
    private $buffer = '';
    private $delimiter;
    public function __construct(\Generator $generator)
    {
        $this->generator = $generator;
        $this->delimiter = $this->generator->current();
        if (!$this->generator->valid()) {
            $this->generator = null;
            return;
        }
        if ($this->delimiter !== null && (!\is_int($this->delimiter) || $this->delimiter <= 0) && (!\is_string($this->delimiter) || !\strlen($this->delimiter))) {
            throw new InvalidDelimiterError($generator, \sprintf("Invalid value yielded: Expected NULL, an int greater than 0, or a non-empty string; %s given", \is_object($this->delimiter) ? \sprintf("instance of %s", \get_class($this->delimiter)) : \gettype($this->delimiter)));
        }
    }
    public final function cancel() : string
    {
        $this->generator = null;
        return $this->buffer;
    }
    public final function isValid() : bool
    {
        return $this->generator !== null;
    }
    public final function push(string $data)
    {
        if ($this->generator === null) {
            throw new \Error("The parser is no longer writable");
        }
        $this->buffer .= $data;
        $end = \false;
        try {
            while ($this->buffer !== "") {
                if (\is_int($this->delimiter)) {
                    if (\strlen($this->buffer) < $this->delimiter) {
                        break;
                    }
                    $send = \substr($this->buffer, 0, $this->delimiter);
                    $this->buffer = \substr($this->buffer, $this->delimiter);
                } elseif (\is_string($this->delimiter)) {
                    if (($position = \strpos($this->buffer, $this->delimiter)) === \false) {
                        break;
                    }
                    $send = \substr($this->buffer, 0, $position);
                    $this->buffer = \substr($this->buffer, $position + \strlen($this->delimiter));
                } else {
                    $send = $this->buffer;
                    $this->buffer = "";
                }
                $this->delimiter = $this->generator->send($send);
                if (!$this->generator->valid()) {
                    $end = \true;
                    break;
                }
                if ($this->delimiter !== null && (!\is_int($this->delimiter) || $this->delimiter <= 0) && (!\is_string($this->delimiter) || !\strlen($this->delimiter))) {
                    throw new InvalidDelimiterError($this->generator, \sprintf("Invalid value yielded: Expected NULL, an int greater than 0, or a non-empty string; %s given", \is_object($this->delimiter) ? \sprintf("instance of %s", \get_class($this->delimiter)) : \gettype($this->delimiter)));
                }
            }
        } catch (\Throwable $exception) {
            $end = \true;
            throw $exception;
        } finally {
            if ($end) {
                $this->generator = null;
            }
        }
    }
}
