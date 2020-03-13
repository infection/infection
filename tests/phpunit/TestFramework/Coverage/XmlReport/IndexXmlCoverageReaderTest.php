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

namespace Infection\Tests\TestFramework\Coverage\XmlReport;

use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageReader;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;
use function Safe\realpath;

/**
 * @group integration
 */
final class IndexXmlCoverageReaderTest extends TestCase
{
    private const COVERAGE_DIR = __DIR__ . '/../../../Fixtures/Files/phpunit/coverage/coverage-xml';

    public function test_it_can_provide_the_PHPUnit_XML_report_index_file_path(): void
    {
        $reader = new IndexXmlCoverageReader(self::COVERAGE_DIR);

        $expectedIndexPath = realpath(self::COVERAGE_DIR . '/index.xml');

        $this->assertSame(
            $expectedIndexPath,
            $reader->getIndexXmlPath()
        );
    }

    public function test_it_does_not_check_the_file_existence_when_retrieving_the_index_file_path(): void
    {
        $reader = new IndexXmlCoverageReader('/nowhere');

        $this->assertSame('/nowhere/index.xml', $reader->getIndexXmlPath());
    }

    public function test_it_can_provide_the_PHPUnit_XML_report_index_file_content(): void
    {
        $reader = new IndexXmlCoverageReader(self::COVERAGE_DIR);

        $expectedContents = file_get_contents(self::COVERAGE_DIR . '/index.xml');

        $this->assertSame($expectedContents, $reader->getIndexXmlContent());
    }
}
