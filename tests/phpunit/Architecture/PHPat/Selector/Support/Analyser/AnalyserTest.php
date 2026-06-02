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
use Infection\Tests\Architecture\PHPat\Selector\SelectorTestCase;
use PhpParser\ErrorHandler\Throwing;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Parser;
use PHPStan\Reflection\ClassReflection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(Analyser::class)]
#[CoversClass(AnalysisResult::class)]
#[CoversClass(DetectConcreteClassMeaningfulImplementationVisitor::class)]
#[CoversClass(IoCodeDetector::class)]
final class AnalyserTest extends SelectorTestCase
{
    private ClassReflection $classReflection;

    private FileSystem&MockObject $fileSystemMock;

    private Parser&MockObject $parserMock;

    private Analyser $analyser;

    protected function setUp(): void
    {
        $this->classReflection = $this->createClassReflection(self::class);

        $this->fileSystemMock = $this->createMock(FileSystem::class);
        $this->parserMock = $this->createMock(Parser::class);

        $this->analyser = new Analyser(
            $this->parserMock,
            $this->fileSystemMock,
        );
    }

    public function test_it_reads_the_class_file_parses_it_and_returns_the_analysis_result(): void
    {
        $fileContents = 'PHP file contents';

        $nodes = [
            new Class_('RandomTestClass'),
        ];

        $this->fileSystemMock
            ->expects($this->once())
            ->method('readFile')
            ->with(__FILE__)
            ->willReturn($fileContents);

        $this->parserMock
            ->expects($this->once())
            ->method('parse')
            ->with(
                $fileContents,
                $this->isInstanceOf(Throwing::class),
            )
            ->willReturn($nodes);

        $actual = $this->analyser->analyse($this->classReflection);

        $this->assertTrue($actual->hasTrivialImplementation);
        $this->assertFalse($actual->usesIo);
    }
}
