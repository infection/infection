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
                $lines[] = sprintf('<fg=red>%s</>', $line);
            } elseif (0 === strpos($line, '+')) {
                $lines[] = sprintf('<fg=green>%s</>', $line);
            } else {
                $lines[] = $line;
            }
        }

        return sprintf("<fg=white>%s%s</>", "\n", implode("\n", $lines));
    }
}