<?php

declare(strict_types=1);

namespace Infection\Tests\TestingUtility\TreeFormatter;

use Closure;
use Infection\Tests\TestingUtility\TreeFormatter\UnicodeTreeDiagramDrawer\UnicodeTreeDiagramDrawer;
use function count;
use function sprintf;

/**
 * Service to render a list of items as an ASCII tree.
 *
 * @template T
 */
final readonly class TreeFormatter
{
    /**
     * @param UnicodeTreeDiagramDrawer $drawer
     * @param Closure(T):string                  $renderer
     * @param Closure(T): iterable<T> $childrenProvider
     */
    public function __construct(
        private UnicodeTreeDiagramDrawer $drawer,
        private Closure $renderer,
        private Closure $childrenProvider,
    ) {}

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

            yield sprintf("%s %s", $prefix, $content);

            $children = ($this->childrenProvider)($item);

            yield from $this->renderItems($children, $depth + 1);
        }
    }
}
