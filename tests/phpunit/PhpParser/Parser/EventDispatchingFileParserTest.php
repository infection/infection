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

namespace Infection\Tests\PhpParser\Parser;

use Infection\Event\Events\Ast\AstParsing\AstParsingWasFinished;
use Infection\Event\Events\Ast\AstParsing\AstParsingWasStarted;
use Infection\PhpParser\Parser\EventDispatchingFileParser;
use Infection\PhpParser\Parser\FileParser;
use Infection\Testing\FileSystem\MockSplFileInfo;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use Infection\Tests\Fixtures\PhpParser\FakeNode;
use Infection\Tests\PhpParser\FakeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

#[CoversClass(EventDispatchingFileParser::class)]
final class EventDispatchingFileParserTest extends TestCase
{
    #[DataProvider('sourceFileProvider')]
    public function test_it_dispatches_parsing_events_around_the_decorated_parser(
        SplFileInfo $sourceFile,
        string $expectedSourceFilePath,
    ): void {
        $eventDispatcher = new EventDispatcherCollector();
        $expectedStatementsAndTokens = [
            [new FakeNode()],
            [FakeToken::create()],
        ];
        $expectedEvents = [
            new AstParsingWasStarted($expectedSourceFilePath),
            new AstParsingWasFinished($expectedSourceFilePath),
        ];

        $decoratedParserMock = $this->createMock(FileParser::class);
        $decoratedParserMock
            ->expects($this->once())
            ->method('parse')
            ->with($sourceFile)
            ->willReturn($expectedStatementsAndTokens);

        $parser = new EventDispatchingFileParser(
            $decoratedParserMock,
            $eventDispatcher,
        );

        $this->assertSame($expectedStatementsAndTokens, $parser->parse($sourceFile));
        $this->assertEquals(
            $expectedEvents,
            $eventDispatcher->getEvents(),
        );
    }

    public static function sourceFileProvider(): iterable
    {
        yield 'real path available' => [
            new MockSplFileInfo(
                '/path/to/source.php',
                '/real/path/to/source.php',
            ),
            '/real/path/to/source.php',
        ];

        yield 'real path unavailable' => [
            new MockSplFileInfo('/path/to/source.php'),
            '/path/to/source.php',
        ];
    }
}
