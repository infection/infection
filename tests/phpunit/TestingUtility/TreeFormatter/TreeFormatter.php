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

namespace Infection\Tests\TestingUtility\TreeFormatter;

use Closure;
use function count;
use Infection\Tests\TestingUtility\TreeFormatter\UnicodeTreeDiagramDrawer\UnicodeTreeDiagramDrawer;
use function sprintf;

/**
 * Service to render a list of items as an ASCII tree.
 *
 * @template T
 */
final readonly class TreeFormatter
{
    /**
     * @param Closure(T):string $renderer
     * @param Closure(T): iterable<T> $childrenProvider
     */
    public function __construct(
        private UnicodeTreeDiagramDrawer $drawer,
        private Closure $renderer,
        private Closure $childrenProvider,
    ) {
    }

    /**
     * @param iterable<T> $items
     *
     * @return iterable<string>
     */
    public function render(iterable $items): iterable
    {
        yield from $this->renderItems($items, depth: 0);

        yield '';
    }

    /**
     * @param iterable<T> $items
     * @param positive-int|0 $depth
     *
     * @return iterable<string>
     */
    private function renderItems(iterable $items, int $depth): iterable
    {
        $itemsList = [...$items];
        $itemsCount = count($itemsList);

        foreach ($itemsList as $index => $item) {
            $isLast = $index === $itemsCount - 1;

            $prefix = $this->drawer->draw($depth, $isLast);
            $content = ($this->renderer)($item);

            yield sprintf('%s %s', $prefix, $content);

            $children = ($this->childrenProvider)($item);

            yield from $this->renderItems($children, $depth + 1);
        }
    }
}
