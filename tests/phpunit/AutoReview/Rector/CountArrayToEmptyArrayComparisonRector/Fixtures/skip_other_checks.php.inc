<?php

namespace OtherCountChecks;

final class OtherChecks
{
    public function compare(array $array, \Countable $countable, array|\Countable $union, mixed $unknown): array
    {
        return [
            count($array) >= 0,
            count($array) < 0,
            0 > count($array),
            count($array) === 1,
            count($array) > 1,
            1 < count($array),
            count($array) === 0.0,
            count($array) === count($array),
            count($array),
            count($array, COUNT_RECURSIVE) === 0,
            count($array, $this->getMode()) > 0,
            count(...[$array]) === 0,
            count(...),
            count($countable) === 0,
            count($countable) > 0,
            !count($countable),
            count($union) === 0,
            count($unknown) === 0,
            strlen('') === 0,
            $countable->count() === 0,
        ];
    }

    /** @param array $phpDocOnly */
    public function phpDocOnly($phpDocOnly): bool
    {
        return count($phpDocOnly) === 0;
    }

    public function condition(\Countable $countable): int
    {
        if (count($countable)) {
            return 1;
        } elseif (count($countable)) {
            return 2;
        }

        return 0;
    }

    public function getMode(): int
    {
        return COUNT_NORMAL;
    }
}
