<?php

declare(strict_types=1);

namespace Infection\Telemetry\Reporter;

use function array_pop;
use function end;
use function str_repeat;

final class BoxDrawer
{
    private const INDENT = '  ';

    private const BOX_DRAWINGS_LIGHT_VERTICAL_AND_RIGHT = '├─';
    private const BOX_DRAWINGS_LIGHT_UP_AND_RIGHT = '└─';
    private const BOX_DRAWINGS_LIGHT_VERTICAL = '│';
    private const BOX_DRAWINGS_LIGHT_DOWN_AND_RIGHT = '┌─';
    private const BOX_DRAWINGS_LIGHT_DOWN_AND_LEFT = '┐';
    private const BOX_DRAWINGS_LIGHT_UP_AND_LEFT = '┘';
    private const BOX_DRAWINGS_LIGHT_UP_AND_HORIZONTAL = '┴─';
    private const BOX_DRAWINGS_LIGHT_DOWN_AND_HORIZONTAL = '┬─';
    private const BOX_DRAWINGS_LIGHT_VERTICAL_AND_HORIZONTAL = '┼─';
    private const BOX_DRAWINGS_LIGHT_HORIZONTAL = '─';

    private int $drawCount = 0;
    private array $history = [];
    private string|null $connector = '';

    public function draw(int $depth, bool $isLast): string
    {
        $this->calculateConnectorIfNecessary($depth);

        $result = $this->doDraw($depth, $isLast);

        $this->drawCount++;

        $this->recordHistory($depth, $isLast);

        return $result;
    }

    private function doDraw(int $depth, bool $isLast): string
    {
        if ($depth === 0) {
            if (0 === $this->drawCount) {
                return $isLast
                    ? self::BOX_DRAWINGS_LIGHT_HORIZONTAL
                    : self::BOX_DRAWINGS_LIGHT_DOWN_AND_RIGHT;
            }

            return $isLast
                ? self::BOX_DRAWINGS_LIGHT_UP_AND_RIGHT
                : self::BOX_DRAWINGS_LIGHT_VERTICAL_AND_RIGHT;
        }

        $current = $isLast
            ? self::BOX_DRAWINGS_LIGHT_UP_AND_RIGHT
            : self::BOX_DRAWINGS_LIGHT_VERTICAL_AND_RIGHT;

        return $this->connector.$current;
    }

    private function recordHistory(int $depth, bool $isLast): void
    {
        [$previousDepth] = end($this->history);

        if ($previousDepth === null) {
            $this->history[] = [$depth, $isLast];
        } elseif ($previousDepth < $depth) {
            $this->history[] = [$depth, $isLast];
        } elseif ($previousDepth > $depth) {
            array_pop($this->history);
        }
    }

    private function calculateConnectorIfNecessary(int $currentDepth): void
    {
        [$previousDepth] = end($this->history);

        $this->calculateConnector($currentDepth);
    }

    private function calculateConnector(int $currentDepth): void
    {
        $this->connector = '';

        foreach ($this->history as [$depth, $isLast]) {
            if ($depth >= $currentDepth) {
                break;
            }

            $this->connector .= $isLast
                ? '    '
                : self::BOX_DRAWINGS_LIGHT_VERTICAL. '   ';
        }
    }
}
