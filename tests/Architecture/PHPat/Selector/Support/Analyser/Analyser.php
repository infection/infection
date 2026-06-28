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

namespace Infection\Tests\Architecture\PHPat\Selector\Support\Analyser;

use function array_filter;
use Infection\FileSystem\FileSystem;
use Infection\Framework\ClassName;
use Infection\Tests\Architecture\PHPat\Selector\Support\ConcreteClassReflection;
use Infection\Tests\Architecture\PHPat\Selector\Support\PHPUnitTestClassAnalysis;
use PhpParser\ErrorHandler\Throwing;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\Parser;
use PHPStan\Reflection\ClassReflection;
use function sprintf;
use Webmozart\Assert\Assert;

final class Analyser
{
    /**
     * @var array<string, AnalysisResult>
     */
    private array $analysisResultCache = [];

    public function __construct(
        private readonly Parser $parser,
        private readonly FileSystem $fileSystem,
    ) {
    }

    public function analyse(
        ClassReflection $classReflection,
        bool $analyseNonConcreteClasses = false,
    ): AnalysisResult {
        $cacheKey = self::createAnalysisResultCacheKey(
            $classReflection,
            $analyseNonConcreteClasses,
        );

        if (isset($this->analysisResultCache[$cacheKey])) {
            return $this->analysisResultCache[$cacheKey];
        }

        $isConcreteClass = ConcreteClassReflection::isConcreteClass($classReflection);

        if (
            !$isConcreteClass
            && !$analyseNonConcreteClasses
        ) {
            return $this->analysisResultCache[$cacheKey] = new AnalysisResult(
                hasTrivialImplementation: false,
                usesIo: false,
                isAConcretePHPUnitTestCase: false,
                hasCoversNothing: PHPUnitTestClassAnalysis::hasCoversNothing($classReflection),
                belongsToIntegrationGroup: PHPUnitTestClassAnalysis::belongsToIntegrationGroup($classReflection),
            );
        }

        $nodes = $this->parse($classReflection);

        return $this->analysisResultCache[$cacheKey] = $this->visit(
            $classReflection,
            $nodes,
        );
    }

    /**
     * @return Node[]
     */
    private function parse(ClassReflection $classReflection): array
    {
        $fileName = $classReflection->getFileName();
        Assert::notNull(
            $fileName,
            sprintf(
                'Expected the class "%s" to have a file name.',
                $classReflection->getName(),
            ),
        );

        $nodes = $this->parser->parse(
            $this->fileSystem->readFile($fileName),
            new Throwing(),
        );
        Assert::notNull($nodes);

        return $nodes;
    }

    /**
     * @param Node[] $nodes
     */
    private function visit(
        ClassReflection $classReflection,
        array $nodes,
    ): AnalysisResult {
        $meaningfulImplementationVisitor = ConcreteClassReflection::isConcreteClass($classReflection)
            ? new DetectConcreteClassMeaningfulImplementationVisitor(
                ClassName::getShortClassName($classReflection->getName()),
            )
            : null;
        $ioCodeDetectorVisitor = IoCodeDetectorVisitor::create();

        $this
            ->createTraverser(
                $meaningfulImplementationVisitor,
                $ioCodeDetectorVisitor,
            )
            ->traverse($nodes);

        return new AnalysisResult(
            hasTrivialImplementation: !($meaningfulImplementationVisitor?->hasMeaningfulImplementation() ?? true),
            usesIo: $ioCodeDetectorVisitor->hasIoOperations(),
            isAConcretePHPUnitTestCase: PHPUnitTestClassAnalysis::isPHPUnitTestCase($classReflection),
            hasCoversNothing: PHPUnitTestClassAnalysis::hasCoversNothing($classReflection),
            belongsToIntegrationGroup: PHPUnitTestClassAnalysis::belongsToIntegrationGroup($classReflection),
        );
    }

    private function createTraverser(?NodeVisitor ...$visitors): NodeTraverserInterface
    {
        return new NodeTraverser(
            new ParentConnectingVisitor(),
            ...array_filter($visitors),
        );
    }

    private static function createAnalysisResultCacheKey(
        ClassReflection $classReflection,
        bool $analyseNonConcreteClasses,
    ): string {
        return sprintf(
            '%s-%s',
            $classReflection->getName(),
            $analyseNonConcreteClasses ? '1' : '0',
        );
    }
}
