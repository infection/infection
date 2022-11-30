<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Process;

use _HumbugBox9658796bb9f0\Symfony\Component\Process\Exception\RuntimeException;
/**
@implements
*/
class InputStream implements \IteratorAggregate
{
    private $onEmpty = null;
    private $input = [];
    private $open = \true;
    public function onEmpty(callable $onEmpty = null)
    {
        $this->onEmpty = $onEmpty;
    }
    public function write($input)
    {
        if (null === $input) {
            return;
        }
        if ($this->isClosed()) {
            throw new RuntimeException(\sprintf('"%s" is closed.', static::class));
        }
        $this->input[] = ProcessUtils::validateInput(__METHOD__, $input);
    }
    public function close()
    {
        $this->open = \false;
    }
    public function isClosed()
    {
        return !$this->open;
    }
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        $this->open = \true;
        while ($this->open || $this->input) {
            if (!$this->input) {
                (yield '');
                continue;
            }
            $current = \array_shift($this->input);
            if ($current instanceof \Iterator) {
                yield from $current;
            } else {
                (yield $current);
            }
            if (!$this->input && $this->open && null !== ($onEmpty = $this->onEmpty)) {
                $this->write($onEmpty($this));
            }
        }
    }
}
