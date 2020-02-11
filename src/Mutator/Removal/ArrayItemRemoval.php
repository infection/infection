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

namespace Infection\Mutator\Removal;

use function array_merge;
use function count;
use Generator;
use function gettype;
use function in_array;
use Infection\Config\Exception\InvalidConfigException;
use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use Infection\Mutator\Util\MutatorConfig;
use function is_numeric;
use function is_scalar;
use function is_string;
use function min;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use function range;
use function strtolower;
use function strtoupper;

/**
 * @internal
 */
final class ArrayItemRemoval implements Mutator
{
    use GetMutatorName;

    private const DEFAULT_SETTINGS = [
        'remove' => 'first',
        'limit' => PHP_INT_MAX,
    ];

    /**
     * @var string first|last|all
     */
    private $remove;

    /**
     * @var int
     */
    private $limit;

    public function __construct(MutatorConfig $config)
    {
        $settings = $this->getResultSettings($config->getMutatorSettings());

        $this->remove = $settings['remove'];
        $this->limit = $settings['limit'];
    }

    public static function getDefinition(): ?Definition
    {
        return new Definition(
            <<<'TXT'
Removes an element of an array literal. For example:

```php
$x = [0, 1, 2];
```

Will be mutated to:

```php
$x = [1, 2];
```

And:

```php
$x = [0, 2];
```

And:

```php
$x = [1, 2];
```

Which elements it removes or how many elements it will attempt to remove will depend on its
configuration.

TXT
            ,
            MutatorCategory::SEMANTIC_REDUCTION,
            null
        );
    }

    /**
     * @param Node\Expr\Array_ $arrayNode
     *
     * @return Generator<Node\Expr\Array_>
     */
    public function mutate(Node $arrayNode): Generator
    {
        foreach ($this->getItemsIndexes($arrayNode->items) as $indexToRemove) {
            $newArrayNode = clone $arrayNode;
            unset($newArrayNode->items[$indexToRemove]);

            yield $newArrayNode;
        }
    }

    public function canMutate(Node $node): bool
    {
        return $node instanceof Node\Expr\Array_ && count($node->items);
    }

    /**
     * @param ArrayItem[] $items
     *
     * @return int[]
     */
    private function getItemsIndexes(array $items): array
    {
        switch ($this->remove) {
            case 'first':
                return [0];
            case 'last':
                return [count($items) - 1];
            default:
                return range(0, min(count($items), $this->limit) - 1);
        }
    }

    /**
     * @param array<string, mixed> $settings
     *
     * @return array{remove: string, limit: int}
     */
    private function getResultSettings(array $settings): array
    {
        $settings = array_merge(self::DEFAULT_SETTINGS, $settings);

        if (!is_string($settings['remove'])) {
            throw $this->configException($settings, 'remove');
        }

        $settings['remove'] = strtolower($settings['remove']);

        if (!in_array($settings['remove'], ['first', 'last', 'all'])) {
            throw $this->configException($settings, 'remove');
        }

        if (!is_numeric($settings['limit']) || $settings['limit'] < 1) {
            throw $this->configException($settings, 'limit');
        }

        return [
            'remove' => $settings['remove'],
            'limit' => (int) $settings['limit'],
        ];
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function configException(array $settings, string $property): InvalidConfigException
    {
        $value = $settings[$property];

        return new InvalidConfigException(sprintf(
            'Invalid configuration of ArrayItemRemoval mutator. Setting `%s` is invalid (%s)',
            $property,
            is_scalar($value) ? $value : '<' . strtoupper(gettype($value)) . '>'
        ));
    }
}
