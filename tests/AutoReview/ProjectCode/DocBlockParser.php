<?php

declare(strict_types=1);

namespace Infection\Tests\AutoReview\ProjectCode;

use function array_filter;
use function array_map;
use function count;
use function current;
use function end;
use function explode;
use function implode;
use function strlen;
use function substr;
use function trim;

final class DocBlockParser
{
    public static function parse(string $docblock): string
    {
        $docblock = trim($docblock);

        if ('' === $docblock) {
            return '';
        }

        /** @var string[] $lines */
        $lines = array_map(
            'trim',
            explode("\n", $docblock)
        );

        /** @var string $firstLine */
        $firstLine = current($lines);

        $lines[0] = substr($firstLine, 3);

        $nbrOfLines = count($lines);

        for($i = 1; $i < $nbrOfLines; $i++) {
            $lines[$i] = substr($lines[$i], 1);
        }

        end($lines);

        /** @var string $lastLine */
        $lastLine = current($lines);

        $lines[$nbrOfLines - 1] = substr($lastLine, 0, -2);

        return implode(
            "\n",
            array_filter(
                array_map('trim', $lines)
            )
        );
    }
}
