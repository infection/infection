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

namespace Infection\Tests\Architecture\PHPat\Selector;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\DependencyInjection\Container;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPUnit\Framework\TestCase;
use function sprintf;

abstract class SelectorTestCase extends TestCase
{
    private const string PROJECT_ROOT = __DIR__ . '/../../../../../';

    private const string CONTAINER_CACHE_DIRECTORY = self::PROJECT_ROOT . '/var/cache/phpstan-phpat-selector-test';

    private static ReflectionProvider $reflectionProvider;

    private static Parser $parser;

    private static NodeScopeResolver $nodeScopeResolver;

    private static ScopeFactory $scopeFactory;

    public static function setUpBeforeClass(): void
    {
        $container = self::createContainer();

        self::$reflectionProvider = $container->getByType(ReflectionProvider::class);
        self::$parser = $container->getService('defaultAnalysisParser');
        self::$nodeScopeResolver = $container->getService(
            $container->findServiceNamesByType(NodeScopeResolver::class)[0],
        );
        self::$scopeFactory = $container->getByType(ScopeFactory::class);
    }

    /**
     * @param class-string $className
     */
    final protected function createClassReflection(string $className): ClassReflection
    {
        return self::$reflectionProvider->getClass($className);
    }

    final protected function createAnonymousClassReflectionFromFile(string $file): ClassReflection
    {
        $anonymousClassReflection = null;

        self::$nodeScopeResolver->processNodes(
            nodes: self::$parser->parseFile($file),
            scope: self::$scopeFactory->create(ScopeContext::create($file)),
            nodeCallback: static function (Node $node, Scope $scope) use (&$anonymousClassReflection): void {
                if ($node instanceof New_ && $node->class instanceof Class_) {
                    $anonymousClassReflection = self::$reflectionProvider->getAnonymousClassReflection($node->class, $scope);

                    return;
                }

                if (!$node instanceof Class_ || !$node->isAnonymous()) {
                    return;
                }

                $anonymousClassReflection = self::$reflectionProvider->getAnonymousClassReflection($node, $scope);
            },
        );

        if (!$anonymousClassReflection instanceof ClassReflection) {
            $this->fail(
                sprintf(
                    'Could not find an anonymous class in "%s".',
                    $file,
                ),
            );
        }

        return $anonymousClassReflection;
    }

    private static function createContainer(): Container
    {
        $containerFactory = new ContainerFactory(self::PROJECT_ROOT);

        return $containerFactory->create(
            tempDirectory: self::CONTAINER_CACHE_DIRECTORY,
            additionalConfigFiles: [],
            analysedPaths: [],
        );
    }
}
