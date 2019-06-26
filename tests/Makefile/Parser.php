<?php

declare(strict_types=1);

namespace Infection\Tests\Makefile;

use a;
use function array_filter;
use function array_keys;
use function array_map;
use function array_merge;
use function array_pop;
use function array_values;
use function count;
use function current;
use function end;
use function explode;
use function ltrim;
use function preg_match;
use function strpos;
use function substr;
use function trim;
use const PHP_EOL;

final class Parser
{
    /**
     * @return array<string[]&string[][]>
     */
    public function parse(string $makeFileContents): array
    {
        $targets = [];

        $multiline = false;
        $target = null;

        foreach (explode(PHP_EOL, $makeFileContents) as $line) {
            if (0 === strpos($line, "\t")
                || preg_match('/^\S+=.+$/u', $line)) {
                continue;
            }

            $line = trim($line);

            $previousMultiline = $multiline;

            if (false !== strpos($line, ':=')
                || 0 === strpos($line, '#')
            ) {
                continue;
            }

            $multiline = '\\' === substr($line, -1);

            if (false === $previousMultiline) {
                $targetParts = explode(':', $line);

                if (count($targetParts) !== 2) {
                    continue;
                }

                $target = $targetParts[0];

                $dependencies = self::parseDependencies($targetParts[1], $multiline);
            } else {
                $lastEntry = array_pop($targets);

                $target = $lastEntry[0];

                $dependencies = array_merge(
                    $lastEntry[1],
                    self::parseDependencies($line, $multiline)
                );
            }

            $targets[] = [$target, $dependencies];
        }

        return $targets;
    }

    /**
     * @return string[]
     */
    private static function parseDependencies(string $dependencies, bool $multiline): array
    {
        if (false !== strpos($dependencies, '##')) {
            return [trim($dependencies)];
        }

        return array_values(
            array_filter(
                array_map(
                    static function (string $dependency) use ($multiline): string {
                        return trim(
                            $multiline ? ltrim($dependency, '\\') : $dependency
                        );
                    },
                    explode(' ', $dependencies)
                )
            )
        );
    }
}