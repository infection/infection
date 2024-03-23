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

namespace Utils\Rector\Rector;

use function array_filter;
use function array_merge;
use function count;
use function explode;
use function implode;
use function in_array;
use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ReflectionProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversNothing;
use function preg_replace;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use function strtolower;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function trim;
use Utils\Rector\Tests\Rector\AddCoversClassAttributeRector\AddCoversClassAttributeRectorTest;

/**
 * @see AddCoversClassAttributeRectorTest
 */
final class AddCoversClassAttributeRector extends AbstractRector
{
    private ReflectionProvider $reflectionProvider;

    private PhpAttributeGroupFactory $phpAttributeGroupFactory;

    private PhpAttributeAnalyzer $phpAttributeAnalyzer;

    private TestsNodeAnalyzer $testsNodeAnalyzer;

    public function __construct(
        ReflectionProvider $reflectionProvider,
        PhpAttributeGroupFactory $phpAttributeGroupFactory,
        PhpAttributeAnalyzer $phpAttributeAnalyzer,
        TestsNodeAnalyzer $testsNodeAnalyzer,
    ) {
        $this->reflectionProvider = $reflectionProvider;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Adds #[CoversClass(...)] attribute to test files guessing source class name.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    class SomeService
                    {
                    }

                    use PHPUnit\Framework\TestCase;

                    class SomeServiceTest extends TestCase
                    {
                    }
                    CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
                    class SomeService
                    {
                    }

                    use PHPUnit\Framework\TestCase;
                    use PHPUnit\Framework\Attributes\CoversClass;

                    #[CoversClass(SomeService::class)]
                    class SomeServiceTest extends TestCase
                    {
                    }
                    CODE_SAMPLE,
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $className = $this->getName($node);

        if ($className === null) {
            return null;
        }

        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }

        if ($this->phpAttributeAnalyzer->hasPhpAttributes($node, [
            CoversNothing::class,
            CoversClass::class,
            CoversFunction::class,
        ])) {
            return null;
        }

        $possibleTestClassNames = $this->resolveSourceClassNames($className);
        $matchingTestClassName = $this->matchExistingClassName($possibleTestClassNames);

        if ($matchingTestClassName === null) {
            return null;
        }

        $coversAttributeGroup = $this->createAttributeGroup('\\' . $matchingTestClassName);

        $node->attrGroups = array_merge($node->attrGroups, [$coversAttributeGroup]);

        return $node;
    }

    /**
     * @return string[]
     */
    public function resolveSourceClassNames(string $className): array
    {
        $classNameParts = explode('\\', $className);
        $partCount = count($classNameParts);
        $classNameParts[$partCount - 1] = preg_replace('#Test$#', '', $classNameParts[$partCount - 1]);

        $possibleTestClassNames = [implode('\\', $classNameParts)];

        $partsWithoutTests = array_filter(
            $classNameParts,
            static fn (string $part): bool => !in_array(strtolower($part), ['test', 'tests'], true),
        );

        $possibleTestClassNames[] = implode('\\', $partsWithoutTests);

        return $possibleTestClassNames;
    }

    /**
     * @param string[] $classNames
     */
    private function matchExistingClassName(array $classNames): ?string
    {
        foreach ($classNames as $className) {
            if (!$this->reflectionProvider->hasClass($className)) {
                continue;
            }

            return $className;
        }

        return null;
    }

    private function createAttributeGroup(string $annotationValue): AttributeGroup
    {
        $attributeClass = 'PHPUnit\\Framework\\Attributes\\CoversClass';
        $attributeValue = trim($annotationValue) . '::class';

        return $this->phpAttributeGroupFactory->createFromClassWithItems($attributeClass, [$attributeValue]);
    }
}
