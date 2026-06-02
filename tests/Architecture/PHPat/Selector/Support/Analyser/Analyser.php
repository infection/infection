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

use Infection\FileSystem\FileSystem;
use Infection\Framework\ClassName;
use PhpParser\ErrorHandler\Throwing;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\Parser;
use PHPStan\Reflection\ClassReflection;
use ReflectionClass;
use function sprintf;
use Webmozart\Assert\Assert;

final readonly class Analyser
{
    public function __construct(
        private Parser $parser,
        private FileSystem $fileSystem,
    ) {
    }

    /**
     * @param ReflectionClass<object>|ClassReflection $classReflection
     */
    public function analyse(ReflectionClass|ClassReflection $classReflection, bool $testCaseCode = false): AnalysisResult
    {
        $nodes = $this->parse($classReflection);

        return $this->visit($classReflection, $nodes, $testCaseCode);
    }

    /**
     * @param ReflectionClass<object>|ClassReflection $classReflection
     *
     * @return Node[]
     */
    private function parse(ReflectionClass|ClassReflection $classReflection): array
    {
        $fileName = $classReflection->getFileName();
        Assert::string(
            $fileName,
            sprintf(
                'Expected the class "%s" to have a file name.',
                $classReflection->getName(),
            ),
        );

        return $this->parseCode($this->fileSystem->readFile($fileName));
    }

    /**
     * @return Node[]
     */
    private function parseCode(string $code): array
    {
        $nodes = $this->parser->parse(
            $code,
            new Throwing(),
        );
        Assert::notNull($nodes);

        return $nodes;
    }

    /**
     * @param Node[] $nodes
     * @param ReflectionClass<object>|ClassReflection $classReflection
     */
    private function visit(
        ReflectionClass|ClassReflection $classReflection,
        array $nodes,
        bool $testCaseCode,
    ): AnalysisResult {
        $meaningfulImplementationVisitor = new DetectConcreteClassMeaningfulImplementationVisitor(
            ClassName::getShortClassName($classReflection->getName()),
        );
        $ioCodeDetector = new IoCodeDetector($testCaseCode);

        $this->createTraverser($meaningfulImplementationVisitor)->traverse($nodes);
        $this->createTraverser($ioCodeDetector)->traverse($nodes);

        return new AnalysisResult(
            hasTrivialImplementation: !$meaningfulImplementationVisitor->hasMeaningfulImplementation(),
            usesIo: $ioCodeDetector->hasIoOperations(),
        );
    }

    private function createTraverser(NodeVisitor ...$visitors): NodeTraverserInterface
    {
        return new NodeTraverser(
            new ParentConnectingVisitor(),
            ...$visitors,
        );
    }
}
