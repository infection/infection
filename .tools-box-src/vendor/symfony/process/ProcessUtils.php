<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Process;

use _HumbugBoxb47773b41c19\Symfony\Component\Process\Exception\InvalidArgumentException;
class ProcessUtils
{
    private function __construct()
    {
    }
    public static function validateInput(string $caller, mixed $input) : mixed
    {
        if (null !== $input) {
            if (\is_resource($input)) {
                return $input;
            }
            if (\is_string($input)) {
                return $input;
            }
            if (\is_scalar($input)) {
                return (string) $input;
            }
            if ($input instanceof Process) {
                return $input->getIterator($input::ITER_SKIP_ERR);
            }
            if ($input instanceof \Iterator) {
                return $input;
            }
            if ($input instanceof \Traversable) {
                return new \IteratorIterator($input);
            }
            throw new InvalidArgumentException(\sprintf('"%s" only accepts strings, Traversable objects or stream resources.', $caller));
        }
        return $input;
    }
}
