<?php

namespace Infection\CodeSamples;

class ReturnTypes
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

    public function withNewerReturnType(): object
    {
        return $this;
    }

    public function withVoidReturnType(): void
    {
        return;
    }
}
