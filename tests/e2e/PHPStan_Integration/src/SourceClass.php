<?php

namespace PHPStan_Integration;

use function is_string;

class SourceClass
{
    /**
     * @template T
     * @param array<T> $values
     * @return list<T>
     */
    public function makeAList(array $values): array
    {
        // some code to generate more mutations

        $strings = ['1'];

        $ints = array_map(function ($value): int {
            return (int) $value;
        }, $strings);

        $nonEmptyArray = ['1'];

        $nonEmptyArrayFromMethod = $this->returnNonEmptyArray();

        $inlineNonEmpty = ['1'];

        return array_values($values);
    }

    /**
     * @return non-empty-array<int, string>
     */
    private function returnNonEmptyArray(): array
    {
        return ['test'];
    }
}
