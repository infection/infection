<?php

namespace CountArrayChecks;

final class ArrayChecks
{
    public function compare(array $array): array
    {
        return [
            count($array) === 0,
            0 === count($array),
            count($array) !== 0,
            0 !== count($array),
            count($array) > 0,
            0 < count($array),
            !count($array),
            !count($array) || count($array) > 0,
            \count($this->getArray()) === 0,
            count(value: $array) === 0,
            count($local = $this->getArray()) > 0,
        ];
    }

    public function conditions(array $array): int
    {
        if (count($array)) {
            return 1;
        } elseif (count($this->getArray())) {
            return 2;
        }

        return 0;
    }

    public function localArray(): bool
    {
        $array = $this->getArray();

        return count($array) === 0;
    }

    public function getArray(): array
    {
        return [];
    }
}

?>
-----
<?php

namespace CountArrayChecks;

final class ArrayChecks
{
    public function compare(array $array): array
    {
        return [
            $array === [],
            $array === [],
            $array !== [],
            $array !== [],
            $array !== [],
            $array !== [],
            $array === [],
            $array === [] || $array !== [],
            $this->getArray() === [],
            $array === [],
            ($local = $this->getArray()) !== [],
        ];
    }

    public function conditions(array $array): int
    {
        if ($array !== []) {
            return 1;
        } elseif ($this->getArray() !== []) {
            return 2;
        }

        return 0;
    }

    public function localArray(): bool
    {
        $array = $this->getArray();

        return $array === [];
    }

    public function getArray(): array
    {
        return [];
    }
}

?>
