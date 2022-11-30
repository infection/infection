<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Process\Pipes;

use _HumbugBoxb47773b41c19\Symfony\Component\Process\Exception\InvalidArgumentException;
abstract class AbstractPipes implements PipesInterface
{
    public array $pipes = [];
    private $inputBuffer = '';
    private $input;
    private $blocked = \true;
    private $lastError;
    public function __construct(mixed $input)
    {
        if (\is_resource($input) || $input instanceof \Iterator) {
            $this->input = $input;
        } elseif (\is_string($input)) {
            $this->inputBuffer = $input;
        } else {
            $this->inputBuffer = (string) $input;
        }
    }
    public function close()
    {
        foreach ($this->pipes as $pipe) {
            if (\is_resource($pipe)) {
                \fclose($pipe);
            }
        }
        $this->pipes = [];
    }
    protected function hasSystemCallBeenInterrupted() : bool
    {
        $lastError = $this->lastError;
        $this->lastError = null;
        return null !== $lastError && \false !== \stripos($lastError, 'interrupted system call');
    }
    protected function unblock()
    {
        if (!$this->blocked) {
            return;
        }
        foreach ($this->pipes as $pipe) {
            \stream_set_blocking($pipe, 0);
        }
        if (\is_resource($this->input)) {
            \stream_set_blocking($this->input, 0);
        }
        $this->blocked = \false;
    }
    protected function write() : ?array
    {
        if (!isset($this->pipes[0])) {
            return null;
        }
        $input = $this->input;
        if ($input instanceof \Iterator) {
            if (!$input->valid()) {
                $input = null;
            } elseif (\is_resource($input = $input->current())) {
                \stream_set_blocking($input, 0);
            } elseif (!isset($this->inputBuffer[0])) {
                if (!\is_string($input)) {
                    if (!\is_scalar($input)) {
                        throw new InvalidArgumentException(\sprintf('"%s" yielded a value of type "%s", but only scalars and stream resources are supported.', \get_debug_type($this->input), \get_debug_type($input)));
                    }
                    $input = (string) $input;
                }
                $this->inputBuffer = $input;
                $this->input->next();
                $input = null;
            } else {
                $input = null;
            }
        }
        $r = $e = [];
        $w = [$this->pipes[0]];
        if (\false === @\stream_select($r, $w, $e, 0, 0)) {
            return null;
        }
        foreach ($w as $stdin) {
            if (isset($this->inputBuffer[0])) {
                $written = \fwrite($stdin, $this->inputBuffer);
                $this->inputBuffer = \substr($this->inputBuffer, $written);
                if (isset($this->inputBuffer[0])) {
                    return [$this->pipes[0]];
                }
            }
            if ($input) {
                while (\true) {
                    $data = \fread($input, self::CHUNK_SIZE);
                    if (!isset($data[0])) {
                        break;
                    }
                    $written = \fwrite($stdin, $data);
                    $data = \substr($data, $written);
                    if (isset($data[0])) {
                        $this->inputBuffer = $data;
                        return [$this->pipes[0]];
                    }
                }
                if (\feof($input)) {
                    if ($this->input instanceof \Iterator) {
                        $this->input->next();
                    } else {
                        $this->input = null;
                    }
                }
            }
        }
        if (!isset($this->inputBuffer[0]) && !($this->input instanceof \Iterator ? $this->input->valid() : $this->input)) {
            $this->input = null;
            \fclose($this->pipes[0]);
            unset($this->pipes[0]);
        } elseif (!$w) {
            return [$this->pipes[0]];
        }
        return null;
    }
    public function handleError(int $type, string $msg)
    {
        $this->lastError = $msg;
    }
}
