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

namespace Infection\Benchmark\DOMDocument;

use Closure;
use DOMDocument;
use DOMXPath;
use Infection\TestFramework\SafeDOMXPath;
use PhpBench\Attributes\Revs;
use Symfony\Component\Filesystem\Path;
use function file_get_contents;
use function Safe\preg_replace;
use const PHP_INT_MAX;
use PhpBench\Attributes\AfterMethods;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use Webmozart\Assert\Assert;

final class DOMDocumentBench
{
    private const FAT_XML = __DIR__.'/../Tracing/coverage/xml/index.xml';

    private int $nodeCount;

    /**
     * Mimics `SafeDomXPath::fromFile()` drafted in #2558. In this scenario,
     * the XML may have a namespace, but we have no practical way to remove it.
     * Hence, we need to register the namespace and adapt the queries.
     *
     * @see SafeDOMXPath
     * @link https://github.com/infection/infection/pull/2558
     */
    #[Iterations(10)]
    #[Revs(100)]
    #[AfterMethods('tearDown')]
    public function benchLoadFromFile(): void
    {
        $document = new DOMDocument();
        @$document->load(self::FAT_XML);

        $xPath = new DOMXPath($document);
        $xPath->registerNamespace(
            'p',
            $document->documentElement->namespaceURI,
        );

        $this->nodeCount = $xPath->query('//p:test')->count();
    }

    /**
     * Mimics the existing `SafeDomXPath::fromString()`. In this scenario we
     * first have to fetch the XML content and then remove the namespace to
     * keep the queries lean.
     *
     * @see SafeDOMXPath
     * @see XPathFactory
     */
    #[Iterations(10)]
    #[Revs(100)]
    #[AfterMethods('tearDown')]
    public function benchLoadFromString(): void
    {
        $xml = file_get_contents(self::FAT_XML);
        $cleanedXml = preg_replace('/xmlns=\".*?\"/', '', $xml);

        $document = new DOMDocument();
        @$document->loadXML($cleanedXml);

        $xPath = new DOMXPath($document);

        $this->nodeCount = $xPath->query('//test')->count();
    }

    public function tearDown(): void
    {
        Assert::greaterThan(
            $this->nodeCount,
            0,
            'No node queried.',
        );
    }
}
