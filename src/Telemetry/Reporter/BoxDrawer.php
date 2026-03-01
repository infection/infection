<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Telemetry\Reporter;

use function array_key_exists;
use function array_key_last;
use function array_pop;
use function end;
use function implode;

final class BoxDrawer
{
    private const BOX_DRAWINGS_LIGHT_VERTICAL_AND_RIGHT = '├─';

    private const BOX_DRAWINGS_LIGHT_UP_AND_RIGHT = '└─';

    private const BOX_DRAWINGS_LIGHT_VERTICAL = '│';

    private const BOX_DRAWINGS_LIGHT_DOWN_AND_RIGHT = '┌─';

    private const BOX_DRAWINGS_LIGHT_HORIZONTAL = '─';

    private const INDENT = '    ';

    private const PARTIAL_INDENT = '   ';   // Indent for when there is a vertical line

    private int $drawCount = 0;

    /**
     * @var list<array{positive-int|0, bool}>
     */
    private array $history = [];

    /**
     * @var array<int, string>
     */
    private array $connectorCache = [];

    /**
     * @param positive-int|0 $depth
     */
    public function draw(int $depth, bool $isLast): string
    {
        $result = $this->buildDrawing($depth, $isLast);

        ++$this->drawCount;
        $this->updateHistory($depth, $isLast);

        return $result;
    }

    private function buildDrawing(int $depth, bool $isLast): string
    {
        if ($depth === 0) {
            if ($this->drawCount === 0) {
                return $isLast
                    ? self::BOX_DRAWINGS_LIGHT_HORIZONTAL
                    : self::BOX_DRAWINGS_LIGHT_DOWN_AND_RIGHT;
            }

            return $isLast
                ? self::BOX_DRAWINGS_LIGHT_UP_AND_RIGHT
                : self::BOX_DRAWINGS_LIGHT_VERTICAL_AND_RIGHT;
        }

        $connector = $this->buildConnector($depth);
        $current = $isLast
            ? self::BOX_DRAWINGS_LIGHT_UP_AND_RIGHT
            : self::BOX_DRAWINGS_LIGHT_VERTICAL_AND_RIGHT;

        return $connector . $current;
    }

    private function buildConnector(int $currentDepth): string
    {
        if (array_key_exists($currentDepth, $this->connectorCache)) {
            return $this->connectorCache[$currentDepth];
        }

        $parts = [];

        foreach ($this->history as [$depth, $isLast]) {
            if ($depth >= $currentDepth) {
                break;
            }

            $parts[] = $isLast
                ? self::INDENT
                : self::BOX_DRAWINGS_LIGHT_VERTICAL . self::PARTIAL_INDENT;
        }

        $connector = implode('', $parts);
        $this->connectorCache[$currentDepth] = $connector;

        return $connector;
    }

    private function updateHistory(int $depth, bool $isLast): void
    {
        $lastHistoryEntryKey = array_key_last($this->history);
        [$lastHistoryEntryDepth, $lastHistoryEntryIsLast] = $this->history[$lastHistoryEntryKey] ?? [null, null];

        if (
            $lastHistoryEntryDepth === null
            || $lastHistoryEntryDepth < $depth
        ) {
            $this->history[] = [$depth, $isLast];
            $this->connectorCache = [];
        } elseif ($lastHistoryEntryDepth > $depth) {
            $this->popAllEntriesDeeperThanDepth($depth);

            $this->history[] = [$depth, $isLast];
            $this->connectorCache = [];
        } elseif ($lastHistoryEntryIsLast !== $isLast) {
            $this->history[$lastHistoryEntryKey] = [$depth, $isLast];
        }
    }

    private function popAllEntriesDeeperThanDepth(int $currentDepth): void
    {
        do {
            array_pop($this->history);

            [$lastHistoryEntryDepth] = end($this->history);
        } while (
            $lastHistoryEntryDepth !== null
            && $lastHistoryEntryDepth >= $currentDepth
        );
    }
}
