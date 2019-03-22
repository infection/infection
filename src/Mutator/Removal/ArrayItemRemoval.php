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

use Generator;
use Infection\Config\Exception\InvalidConfigException;
use Infection\Mutator\Util\BaseMutator;
use Infection\Mutator\Util\MutatorConfig;
use PhpParser\Node;

/**
 * @internal
 */
final class ArrayItemRemoval extends BaseMutator
{
    private const DEFAULT_SETTINGS = [
        'remove' => 'first',
        'limit' => PHP_INT_MAX,
    ];

    /** @var string first|last|all */
    private $remove;
    /** @var int */
    private $limit;

    public function __construct(MutatorConfig $config)
    {
        parent::__construct($config);

        $settings = $this->getResultSettings();

        $this->remove = $settings['remove'];
        $this->limit = $settings['limit'];
    }

    /**
     * @param Node&Node\Expr\Array_  $arrayNode
     */
    public function mutate(Node $arrayNode): Generator
    {
        foreach ($this->getItemsIndexes($arrayNode->items) as $indexToRemove) {
            $newArrayNode = clone $arrayNode;
            unset($newArrayNode->items[$indexToRemove]);

            yield $newArrayNode;
        }
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Expr\Array_ && \count($node->items);
    }

    private function getItemsIndexes(array $items): array
    {
        switch ($this->remove) {
            case 'first':
                return [0];
            case 'last':
                return [\count($items) - 1];
            default:
                return \range(0, \min(\count($items), $this->limit) - 1);
        }
    }

    private function getResultSettings(): array
    {
        $settings = \array_merge(self::DEFAULT_SETTINGS, $this->getSettings());

        if (!\is_string($settings['remove'])) {
            $this->throwConfigException('remove');
        }

        $settings['remove'] = \strtolower($settings['remove']);

        if (!\in_array($settings['remove'], ['first', 'last', 'all'])) {
            $this->throwConfigException('remove');
        }

        if (!\is_numeric($settings['limit']) || $settings['limit'] < 1) {
            $this->throwConfigException('limit');
        }

        return $settings;
    }

    private function throwConfigException(string $property): void
    {
        $value = $this->getSettings()[$property];

        throw new InvalidConfigException(sprintf(
            'Invalid configuration of ArrayItemRemoval mutator. Setting `%s` is invalid (%s)',
            $property,
            \is_scalar($value) ? $value : '<' . \strtoupper(\gettype($value)) . '>'
        ));
    }
}
