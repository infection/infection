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

namespace Infection\Tests\TestFramework;

use DOMDocument;
use Infection\TestFramework\SafeDOMXPath;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SafeDOMXPathTest extends TestCase
{
    public function test_it_reads_xml(): void
    {
        $xPath = SafeDOMXPath::fromString('<?xml version="1.0"?><foo><bar>Baz</bar></foo>');
        $this->assertSame('Baz', $xPath->query('/foo/bar')[0]->nodeValue);
    }

    public function test_it_fails_on_invalid_query(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $xPath = SafeDOMXPath::fromString('<?xml version="1.0"?><foo><bar>Baz</bar></foo>');
        $xPath->query('#');
    }

    public function test_it_fails_on_invalid_xml(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SafeDOMXPath::fromString('<?xml version="1.0"?><foo>');
    }

    public function test_it_has_document_property(): void
    {
        $xPath = SafeDOMXPath::fromString('<?xml version="1.0"?><test/>');
        $this->assertInstanceOf(DOMDocument::class, $xPath->document);
    }
}
