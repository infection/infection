<?php

namespace _HumbugBoxb47773b41c19\Amp;

/**
@template-covariant
@template-implements
*/
final class Producer implements Iterator
{
    /**
    @use
    */
    use CallableMaker, Internal\Producer;
    public function __construct(callable $producer)
    {
        $result = $producer($this->callableFromInstanceMethod("emit"));
        if (!$result instanceof \Generator) {
            throw new \Error("The callable did not return a Generator");
        }
        $coroutine = new Coroutine($result);
        $coroutine->onResolve(function ($exception) {
            if ($this->complete) {
                return;
            }
            if ($exception) {
                $this->fail($exception);
                return;
            }
            $this->complete();
        });
    }
}
