<?php

declare(strict_types=1);


namespace Infection\Differ;


class DiffColorizer
{
    public function colorize(string $diff)
    {
        $lines = array();

        foreach (explode("\n", $diff) as $line) {
            if (0 === strpos($line, '-')) {
                $lines[] = sprintf('<diff-del>%s</diff-del>', $line);
            } elseif (0 === strpos($line, '+')) {
                $lines[] = sprintf('<diff-add>%s</diff-add>', $line);
            } else {
                $lines[] = $line;
            }
        }

        return sprintf("<code>%s%s</code>", "\n", implode("\n", $lines));
    }
}