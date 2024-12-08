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

namespace Infection\Mutator;

use function end;
use function explode;
use function is_a;
use PhpParser\Node;
use function sprintf;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class MutatorFactory
{
    /**
     * @param array<class-string<Mutator<Node>&ConfigurableMutator<Node>>, mixed[]> $resolvedMutators
     *
     * @return array<string, Mutator<Node>>
     */
    public function create(array $resolvedMutators, bool $useNoopMutators): array
    {
        $mutators = [];

        foreach ($resolvedMutators as $mutatorClassName => $config) {
            Assert::true(
                MutatorResolver::isValidMutator($mutatorClassName),
                sprintf('Unknown mutator "%s"', $mutatorClassName),
            );
            Assert::isArray(
                $config,
                sprintf(
                    'Expected config of the mutator "%s" to be an array. Got "%%s" instead',
                    $mutatorClassName,
                ),
            );

            $settings = (array) ($config['settings'] ?? []);
            /** @var string[] $ignored */
            $ignored = $config['ignore'] ?? [];

            /** @var Mutator<Node> $mutator */
            $mutator
                = is_a($mutatorClassName, ConfigurableMutator::class, true)
                    ? self::getConfigurableMutator($mutatorClassName, $settings)
                    : new $mutatorClassName();

            if ($ignored !== []) {
                $mutator = new IgnoreMutator(new IgnoreConfig($ignored), $mutator);
            }

            if ($useNoopMutators) {
                $mutator = new NoopMutator($mutator);
            }

            $mutators[$mutator->getName()] = $mutator;
        }

        return $mutators;
    }

    public static function getMutatorNameForClassName(string $className): string
    {
        $parts = explode('\\', $className);

        return end($parts);
    }

    /**
     * @param class-string<ConfigurableMutator<Node>> $mutatorClassName
     * @param mixed[] $settings
     *
     * @return ConfigurableMutator<Node>
     */
    private static function getConfigurableMutator(string $mutatorClassName, array $settings): ConfigurableMutator
    {
        $configClassName = $mutatorClassName::getConfigClassName();

        return new $mutatorClassName(new $configClassName($settings));
    }
}
