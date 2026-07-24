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

namespace Infection\Tests\AutoReview\AI;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(AdrIndexGenerator::class)]
final class AdrIndexGeneratorTest extends TestCase
{
    #[DataProvider('statusProvider')]
    public function test_it_generates_an_index(string $status, string $expectedStatus): void
    {
        $generator = new AdrIndexGenerator();

        $actual = $generator->generate(
            "Before\n<!-- adr-index:start -->\nstale\n<!-- adr-index:end -->\nAfter",
            [
                '0002-second.md' => "# Second\n\n## Status\n\nAccepted.",
                '0000-template.md' => "# Template\n\n## Status\n\nProposed.",
                '0001-first.md' => "# First\n\n### Status\n\n{$status}",
            ],
        );

        $this->assertSame(
            <<<MARKDOWN
                Before
                <!-- adr-index:start -->
                - [ADR 0001: First](adr/0001-first.md) — {$expectedStatus}
                - [ADR 0002: Second](adr/0002-second.md) — Accepted
                <!-- adr-index:end -->
                After
                MARKDOWN,
            $actual,
        );
    }

    public static function statusProvider(): iterable
    {
        yield 'proposed' => ['Proposed.', 'Proposed'];

        yield 'accepted with metadata' => ['Accepted ([#123](example.com)).', 'Accepted'];

        yield 'deprecated' => ['Deprecated.', 'Deprecated'];

        yield 'rejected' => ['Rejected.', 'Rejected'];

        yield 'superseded' => ['Superseded by [ADR 0042](0042-example.md).', 'Superseded by ADR 0042'];
    }

    #[DataProvider('invalidAdrProvider')]
    public function test_it_rejects_an_invalid_adr(string $filename, string $contents, string $expectedMessage): void
    {
        $generator = new AdrIndexGenerator();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage($expectedMessage);

        $generator->generate(
            '<!-- adr-index:start --><!-- adr-index:end -->',
            [$filename => $contents],
        );
    }

    public static function invalidAdrProvider(): iterable
    {
        yield 'missing title' => [
            '0001-example.md',
            "## Status\n\nAccepted.",
            'Could not find the title in 0001-example.md.',
        ];

        yield 'missing status' => [
            '0001-example.md',
            '# Example',
            'Could not find the status in 0001-example.md.',
        ];

        yield 'invalid number' => [
            'example.md',
            "# Example\n\n## Status\n\nAccepted.",
            'Could not find the ADR number in example.md.',
        ];

        yield 'unknown status' => [
            '0001-example.md',
            "# Example\n\n## Status\n\nUnknown.",
            'Could not determine the status in 0001-example.md.',
        ];

        yield 'superseded without a replacement' => [
            '0001-example.md',
            "# Example\n\n## Status\n\nSuperseded.",
            'Could not determine the status in 0001-example.md.',
        ];
    }

    #[DataProvider('invalidIndexProvider')]
    public function test_it_rejects_an_invalid_index(string $contents): void
    {
        $generator = new AdrIndexGenerator();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Expected exactly one ADR index in AGENTS.md.');

        $generator->generate($contents, []);
    }

    public static function invalidIndexProvider(): iterable
    {
        yield 'missing index' => ['No index'];

        yield 'multiple indexes' => [
            '<!-- adr-index:start --><!-- adr-index:end -->'
            . '<!-- adr-index:start --><!-- adr-index:end -->',
        ];
    }
}
