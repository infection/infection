<?php

namespace Method_Attribute_Signature;

class SourceClass
{
    public function withoutAttribute(): string
    {
        return 'hello';
    }

    #[Route]
    public function withAttributeOnItsOwnLine(): string
    {
        return 'hello';
    }

    #[Route] public function withAttributeOnTheSameLine(): string
    {
        return 'hello';
    }
}
