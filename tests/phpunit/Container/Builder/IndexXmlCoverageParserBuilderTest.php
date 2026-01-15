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

namespace Infection\Tests\Container\Builder;

use Infection\Configuration\SourceFilter\PlainFilter;
use Infection\Container\Builder\IndexXmlCoverageParserBuilder;
use Infection\FileSystem\FakeFileSystem;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageParser;
use Infection\Tests\Configuration\ConfigurationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[CoversClass(IndexXmlCoverageParserBuilder::class)]
final class IndexXmlCoverageParserBuilderTest extends TestCase
{
    public function test_it_builds_with_source_filtered_false_when_no_filter(): void
    {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withSourceFilter(null)
            ->build();

        $parser = (new IndexXmlCoverageParserBuilder(
            $configuration,
            new FakeFileSystem(),
        ))->build();

        $this->assertFalse($this->getIsSourceFiltered($parser));
    }

    public function test_it_builds_with_source_filtered_true_when_filter_present(): void
    {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withSourceFilter(new PlainFilter(['src/']))
            ->build();

        $parser = (new IndexXmlCoverageParserBuilder(
            $configuration,
            new FakeFileSystem(),
        ))->build();

        $this->assertTrue($this->getIsSourceFiltered($parser));
    }

    private function getIsSourceFiltered(IndexXmlCoverageParser $parser): bool
    {
        return (new ReflectionProperty($parser, 'isSourceFiltered'))->getValue($parser);
    }
}
