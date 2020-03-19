<?php

namespace Infection\CodeSamples;

use ArrayObject;

class ReturnTypes extends ArrayObject
{
    public function withReturnType(): int
    {
        return 3;
    }

    public function nullableReturnType(): ?int
    {
        return null;
    }

    public function withoutReturnType()
    {
        return 3;
    }

    public function selfReturn(): self
    {
        return $this;
    }

    public function nullableSelfReturn(): ?self
    {
        return $this;
    }

    public function selfReturnWithoutReturnType()
    {
        return $this;
    }

    public function parentReturn(): parent
    {
        return $this;
    }

    public function nullableParentReturn(): ?parent
    {
        return $this;
    }

    public function withNewerReturnType(): object
    {
        return $this;
    }

    public function withVoidReturnType(): void
    {
        return;
    }
}
