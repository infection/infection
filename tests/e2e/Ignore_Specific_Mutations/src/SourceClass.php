<?php

declare(strict_types=1);

namespace Ignore_Specific_Mutations;

final class SourceClass
{
    /** @infection-ignore ArrayItemRemoval, FalseValue, UnknownMutator */
    public function selectivelyIgnored(): array
    {
        return [false, true];
    }

    public function notIgnored(): array
    {
        return [false, true];
    }
}
