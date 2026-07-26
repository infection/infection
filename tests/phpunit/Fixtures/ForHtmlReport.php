<?php

declare(strict_types=1);


namespace ExampleTest\Level2;


use function array_keys;

final class ForHtmlReport
{
    public function add(int $a, int $b): int
    {
        $this->inner('3');

        $this->inner(
            '3'
        );

        switch (true) {
            case 0 !== 1:
                break;
            default:
                break;
        }

        $this->innerArray(array_keys(['a' => '1', 'b' => '2']));

        if ($this instanceof ForHtmlReport) {
            // ...
        }

        return $a + $b;
    }

    private function inner(string $a): void
    {
        // do nothing
    }

    /**
     * @param string[] $keys
     */
    private function innerArray(array $keys): void
    {
        // do nothing
    }
}
