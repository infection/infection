<?php

namespace PHPStan_Integration;

class SourceClass
{
    /**
     * @template T
     * @param array<T> $values
     * @return list<T>
     */
    public function makeAList(array $values): array
    {
        return array_values($values);
    }
}
