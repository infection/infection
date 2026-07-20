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

use Infection\FileSystem\FileSystem;
use Infection\Tests\FileSystem\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;

#[Group('integration')]
#[CoversClass(AdrIndexUpdater::class)]
final class AdrIndexUpdaterTest extends FileSystemTestCase
{
    private FileSystem $fileSystem;

    private AdrIndexUpdater $updater;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileSystem = new FileSystem();
        $this->updater = new AdrIndexUpdater(
            $this->fileSystem,
            new AdrIndexGenerator(),
        );

        $this->fileSystem->mkdir($this->tmp . '/adr');
        $this->fileSystem->dumpFile(
            $this->tmp . '/adr/0001-example.md',
            "# Example\n\n## Status\n\nAccepted.",
        );
        $this->fileSystem->dumpFile(
            $this->tmp . '/AGENTS.md',
            "Before\n<!-- adr-index:start -->\nstale\n<!-- adr-index:end -->\nAfter",
        );
    }

    public function test_it_updates_the_index(): void
    {
        $this->updater->update($this->tmp, false);

        $this->assertSame(
            <<<MARKDOWN
                Before
                <!-- adr-index:start -->
                - [ADR 0001: Example](adr/0001-example.md) — Accepted
                <!-- adr-index:end -->
                After
                MARKDOWN,
            $this->fileSystem->readFile($this->tmp . '/AGENTS.md'),
        );
    }

    public function test_it_rejects_an_outdated_index_in_check_mode(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The ADR index in AGENTS.md is outdated. Run `make adr-index-update`.');

        $this->updater->update($this->tmp, true);
    }

    public function test_it_accepts_an_up_to_date_index_in_check_mode(): void
    {
        $this->updater->update($this->tmp, false);
        $expectedContents = $this->fileSystem->readFile($this->tmp . '/AGENTS.md');

        $this->updater->update($this->tmp, true);

        $this->assertSame(
            $expectedContents,
            $this->fileSystem->readFile($this->tmp . '/AGENTS.md'),
        );
    }
}
