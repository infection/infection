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

namespace Infection\Tests\Source\Collector;

use Exception;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\SourceFilter\FakeSourceFilter;
use Infection\Configuration\SourceFilter\GitDiffFilter;
use Infection\Configuration\SourceFilter\PlainFilter;
use Infection\Configuration\SourceFilter\SourceFilter;
use Infection\Git\Git;
use Infection\Source\Collector\BasicSourceCollector;
use Infection\Source\Collector\GitDiffSourceCollector;
use Infection\Source\Collector\SourceCollector;
use Infection\Source\Collector\SourceCollectorFactory;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function sprintf;

#[CoversClass(SourceCollectorFactory::class)]
final class SourceCollectorFactoryTest extends TestCase
{
    /**
     * @param Exception|class-string<SourceCollector> $exceptionOrExpectedCollectorClassName
     */
    #[DataProvider('sourceFilterProvider')]
    public function test_it_can_create_a_collector(
        ?SourceFilter $sourceFilter,
        Exception|string $exceptionOrExpectedCollectorClassName,
    ): void {
        $factory = new SourceCollectorFactory(
            $this->createMock(Git::class),
        );

        if ($exceptionOrExpectedCollectorClassName instanceof Exception) {
            $this->expectExceptionObject($exceptionOrExpectedCollectorClassName);
        }

        $actual = $factory->create(
            '/path/to/project',
            new Source(['src', 'lib'], ['vendor', 'tests']),
            $sourceFilter,
        );

        if (!($exceptionOrExpectedCollectorClassName instanceof Exception)) {
            $this->assertSame($actual::class, $exceptionOrExpectedCollectorClassName);
        }
    }

    public static function sourceFilterProvider(): iterable
    {
        yield 'no filter' => [
            null,
            BasicSourceCollector::class,
        ];

        yield 'plain filter' => [
            new PlainFilter(['src/Service']),
            BasicSourceCollector::class,
        ];

        yield 'git diff filter' => [
            new GitDiffFilter('AM', '<merge-base-hash>'),
            GitDiffSourceCollector::class,
        ];

        yield 'unknown filter' => [
            new FakeSourceFilter(),
            new InvalidArgumentException(
                sprintf(
                    'Unknown source filter "%s".',
                    FakeSourceFilter::class,
                ),
            ),
        ];
    }
}
