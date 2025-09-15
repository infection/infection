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

namespace Infection\Tests\TestFramework\XML\SafeDOMXPath;

use DOMDocument;
use DOMNode;
use Infection\TestFramework\XML\SafeDOMXPath;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function sprintf;

#[CoversClass(SafeDOMXPath::class)]
final class SafeDOMXPathTest extends TestCase
{
    private const BOOKSTORE_XML = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <bookstore>
          <book category="cooking">
            <title lang="en">Everyday Italian</title>
            <author>Giada De Laurentiis</author>
            <year>2005</year>
          </book>
            
          <book category="fantasy">
            <title lang="en">The Hobbit</title>
            <author>J.R.R. Tolkien</author>
            <year>1937</year>
          </book>
        </bookstore>
        XML;

    public function test_it_can_be_instantiated(): void
    {
        $domDocument = new DOMDocument();

        $xPath = new SafeDOMXPath($domDocument);

        $this->assertNull($xPath->document->namespaceURI);
    }

    public function test_it_can_be_created_for_an_xml_file(): void
    {
        $xPath = SafeDOMXPath::fromFile(
            __DIR__ . '/example.xml',
        );

        $firstElement = $xPath->document->firstElementChild;

        $this->assertSame('http://www.w3.org/TR/html5/', $firstElement->namespaceURI);
        $this->assertSame('note', $firstElement->tagName);
    }

    public function test_it_throws_an_exception_when_creating_it_from_an_invalid_xml_file(): void
    {
        $pathname = __DIR__ . '/invalid.xml';

        $this->expectExceptionObject(
            new InvalidArgumentException(
                sprintf(
                    'The file "%s" does not contain valid XML.',
                    $pathname,
                ),
            ),
        );

        SafeDOMXPath::fromFile($pathname);
    }

    public function test_it_can_be_created_for_an_xml_string(): void
    {
        $xml = <<<'XML'
            <?xml version="1.0"?>
            <note xmlns="http://www.w3.org/TR/html5/">
                <to>Tove</to>
                <from>Jani</from>
                <heading>Reminder</heading>
                <body>Don't forget me this weekend!</body>
            </note>
            XML;

        $xPath = SafeDOMXPath::fromString($xml);

        $firstElement = $xPath->document->firstElementChild;

        $this->assertSame('http://www.w3.org/TR/html5/', $firstElement->namespaceURI);
        $this->assertSame('note', $firstElement->tagName);
    }

    public function test_it_throws_an_exception_when_creating_it_from_an_invalid_xml_string(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The string "Hello world!" is not valid XML.',
            ),
        );

        SafeDOMXPath::fromString('Hello world!');
    }

    public function test_it_can_query_elements(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $bookList1 = $xPath->queryList('//book');
        $this->assertCount(2, $bookList1);
    }

    public function test_it_can_query_elements_relative_to_another_node(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $firstBook = $xPath->queryList('//book')->item(0);
        // Sanity check
        $this->assertNotNull($firstBook);

        $titlesInFirstBook = $xPath->queryList('.//title', $firstBook);
        $titles = $xPath->queryList('.//title');

        $this->assertCount(1, $titlesInFirstBook);
        // Sanity check
        $this->assertCount(2, $titles);
    }

    public function test_it_cannot_query_with_an_invalid_query(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The query "#" is invalid.',
            ),
        );

        $xPath->queryList('#');
    }

    public function test_it_cannot_query_with_a_query_with_a_dom_node_that_cannot_be_fetched(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The context node passed for the query "//book" is invalid.',
            ),
        );

        $xPath->queryList('//book', new DOMNode());
    }

    public function test_it_cannot_query_with_a_query_with_an_invalid_context_node(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The context node passed for the query "//book" is invalid.',
            ),
        );

        $xPath->queryList('//book', new DOMNode());
    }

    public function test_it_can_query_an_element(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $expected = $xPath->queryList('//book')->item(0);
        $actual = $xPath->queryElement('///book[1]');

        $this->assertSame($expected, $actual);
    }

    public function test_it_can_query_an_element_relative_to_another_node(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $firstBook = $xPath->queryList('//book')->item(0);
        // Sanity check
        $this->assertNotNull($firstBook);

        $expected = $xPath->queryList('//book/title[1]')->item(0);
        $actual = $xPath->queryElement('.//title', $firstBook);

        $this->assertSame($expected, $actual);
    }

    public function test_it_returns_null_if_the_element_could_not_be_found(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $element = $xPath->queryElement('//book[10]');

        $this->assertNull($element);
    }

    public function test_it_cannot_query_an_element_for_which_there_is_more_than_one_item(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'Expected the query "//book" to return a "DOMNodeList" with no or one node. Got "2".',
            ),
        );

        $element = $xPath->queryElement('//book');

        $this->assertNull($element);
    }

    public function test_it_cannot_query_an_element_with_an_invalid_query(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The query "#" is invalid.',
            ),
        );

        $xPath->queryElement('#');
    }

    public function test_it_cannot_query_an_element_with_a_dom_node_that_cannot_be_fetched(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The context node passed for the query "//book" is invalid.',
            ),
        );

        $xPath->queryElement('//book', new DOMNode());
    }

    public function test_it_cannot_query_an_element_with_an_invalid_context_node(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The context node passed for the query "//book" is invalid.',
            ),
        );

        $xPath->queryList('//book', new DOMNode());
    }
}
