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

namespace Infection\DevTools\PHPStan\Rules;

use Infection\Container\Container;
use Infection\Testing\SingletonContainer;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Symfony\Component\Filesystem\Path;
use function sprintf;

/**
 * @implements Rule<New_>
 */
final class InfectionContainerRule implements Rule {
    /**
     * @var string[]|null
     */
    private static $containerFiles;

    public function getNodeType(): string {
        return New_::class;
    }

    public function processNode(Node $node, Scope $scope): array {
        if (
            $node->class instanceof Node\Name
            && $node->class->toString() === Container::class
            && !in_array($scope->getFile(), $this->getContainerFiles(), true)
        ) {
            return [RuleErrorBuilder::message(
                sprintf(
                    'Did not expect to find a usage of the Infection container. Please use "%s::getContainer() instead.',
                    SingletonContainer::class,
                ))->identifier('infection.container')->build()];
        }

        return [];
    }

    /**
     * @return string[]
     */
    private function getContainerFiles(): array
    {
        return self::$containerFiles ??= array_map(
            static fn (string $path): string => Path::canonicalize($path),
            [
                __DIR__ . '/../../../tests/phpunit/Container/ContainerTest.php',
                __DIR__ . '/../../../tests/phpunit/MockedContainer.php',
            ],
        );
    }
}
