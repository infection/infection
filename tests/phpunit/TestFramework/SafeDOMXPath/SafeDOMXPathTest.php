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

namespace Infection\Tests\TestFramework\SafeDOMXPath;

use DOMDocument;
use DOMNode;
use Infection\TestFramework\SafeDOMXPath;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;
use function sprintf;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
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

    #[DataProvider('validXmlFileProvider')]
    public function test_it_can_be_created_for_an_xml_file(
        string $pathname,
        string $expectedFirstElementTagName,
        ?string $expectedDocumentNamespace,
    ): void {
        $xPath = SafeDOMXPath::fromFile(
            Path::canonicalize($pathname),
        );

        $firstElement = $xPath->document->firstElementChild;

        // Beware: this is only the _document_ namespace, not a namespace registered to the XPath.
        $this->assertSame(
            $expectedDocumentNamespace,
            // @phpstan-ignore property.nonObject
            $firstElement->namespaceURI,
            'Expected the document namespace to be left alone.',
        );
        // Sanity check: ensuring we correctly parsed the XML.
        // @phpstan-ignore property.nonObject
        $this->assertSame($expectedFirstElementTagName, $firstElement->tagName);

        // Check that no namespace was registered.
        // This is done by doing a query that _should_ return a result but does
        // not because the XML is namespaced but not the XPath.
        $expectedNodeCount = $expectedDocumentNamespace === null ? 1 : 0;
        $phpunitNodes = $xPath->queryList('/phpunit');
        $this->assertCount($expectedNodeCount, $phpunitNodes);
    }

    public static function validXmlFileProvider(): iterable
    {
        yield 'file with namespace' => [
            __DIR__ . '/example-with-namespace.xml',
            'phpunit',
            'https://schema.phpunit.de/coverage/1.0',
        ];

        yield 'file without namespace' => [
            __DIR__ . '/example-without-namespace.xml',
            'phpunit',
            null,
        ];
    }

    public function test_it_can_be_created_for_an_xml_file_with_a_namespace_registered(): void
    {
        $xPath = SafeDOMXPath::fromFile(
            __DIR__ . '/example-with-namespace.xml',
            'p',
        );

        // Check that no namespace was registered.
        $phpunitNodes = $xPath->queryList('/p:phpunit');
        $this->assertCount(1, $phpunitNodes);
    }

    public function test_it_cannot_be_created_for_an_xml_file_with_a_namespace_registered_if_the_document_has_no_namespace(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException(
                'Expected the first document element to have a namespace URI. None found.',
            ),
        );

        SafeDOMXPath::fromFile(
            __DIR__ . '/example-without-namespace.xml',
            'p',
        );
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
        $xPath = SafeDOMXPath::fromString(
            <<<'XML'
                <?xml version="1.0"?>
                <phpunit xmlns="https://schema.phpunit.de/coverage/1.0">
                    <file name="Validator00.php" path="/AutoGenerated/Validator">
                        <totals>
                            <lines total="644" comments="19" code="625" executable="247" executed="225"
                                   percent="91.09"/>
                            <methods count="43" tested="34" percent="79.07"/>
                            <functions count="0" tested="0" percent="0"/>
                            <classes count="1" tested="0" percent="0.00"/>
                            <traits count="0" tested="0" percent="0"/>
                        </totals>
                        <class name="Infection\BenchmarkSource\AutoGenerated\Validator\Validator00" start="22"
                               executable="247" executed="225" crap="117.4">
                            <namespace name="Infection\BenchmarkSource\AutoGenerated\Validator"/>
                            <method name="getErrors" signature="getErrors(): array" start="32" end="35" crap="1"
                                    executable="1" executed="1" coverage="100"/>
                            <method name="hasErrors" signature="hasErrors(): bool" start="37" end="40" crap="1"
                                    executable="1" executed="1" coverage="100"/>
                        </class>
                    </file>
                </phpunit>
                XML,
        );

        $firstElement = $xPath->document->firstElementChild;

        // Beware: this is only the _document_ namespace, not a namespace registered to the XPath.
        $this->assertSame(
            'https://schema.phpunit.de/coverage/1.0',
            // @phpstan-ignore property.nonObject
            $firstElement->namespaceURI,
            'Expected the document namespace to be left alone.',
        );
        // Sanity check: ensuring we correctly parsed the XML.
        // @phpstan-ignore property.nonObject
        $this->assertSame('phpunit', $firstElement->tagName);

        // Check that no namespace was registered.
        // This is done by doing a query that _should_ return a result but does
        // not because the XML is namespaced but not the XPath.
        $phpunitNodes = $xPath->queryList('/phpunit');
        $this->assertCount(0, $phpunitNodes);

        // Check that whitespaces are _not_ preserved
        $this->assertSame(
            <<<'XML'
                <?xml version="1.0"?>
                <phpunit xmlns="https://schema.phpunit.de/coverage/1.0">
                    <file name="Validator00.php" path="/AutoGenerated/Validator">
                        <totals>
                            <lines total="644" comments="19" code="625" executable="247" executed="225" percent="91.09"/>
                            <methods count="43" tested="34" percent="79.07"/>
                            <functions count="0" tested="0" percent="0"/>
                            <classes count="1" tested="0" percent="0.00"/>
                            <traits count="0" tested="0" percent="0"/>
                        </totals>
                        <class name="Infection\BenchmarkSource\AutoGenerated\Validator\Validator00" start="22" executable="247" executed="225" crap="117.4">
                            <namespace name="Infection\BenchmarkSource\AutoGenerated\Validator"/>
                            <method name="getErrors" signature="getErrors(): array" start="32" end="35" crap="1" executable="1" executed="1" coverage="100"/>
                            <method name="hasErrors" signature="hasErrors(): bool" start="37" end="40" crap="1" executable="1" executed="1" coverage="100"/>
                        </class>
                    </file>
                </phpunit>

                XML,
            $xPath->document->saveXML(),
        );
    }

    public function test_it_can_be_created_for_an_xml_string_without_a_namespace(): void
    {
        $xPath = SafeDOMXPath::fromString(
            file_get_contents(__DIR__ . '/example-without-namespace.xml'),
        );

        $firstElement = $xPath->document->firstElementChild;

        // @phpstan-ignore property.nonObject
        $this->assertNull($firstElement->namespaceURI);
        // Sanity check: ensuring we correctly parsed the XML.
        // @phpstan-ignore property.nonObject
        $this->assertSame('phpunit', $firstElement->tagName);

        // Since no namespace is registered and the document has no namespace,
        // the query must return a result.
        $phpunitNodes = $xPath->queryList('/phpunit');
        $this->assertCount(1, $phpunitNodes);
    }

    public function test_it_can_be_created_for_an_xml_string_with_a_namespace_registered(): void
    {
        $xPath = SafeDOMXPath::fromString(
            file_get_contents(__DIR__ . '/example-with-namespace.xml'),
            'p',
        );

        // Check that no namespace was registered.
        $phpunitNodes = $xPath->queryList('/p:phpunit');
        $this->assertCount(1, $phpunitNodes);
    }

    public function test_it_cannot_be_created_for_an_xml_string_with_a_namespace_registered_if_the_document_has_no_namespace(): void
    {
        $xml = file_get_contents(__DIR__ . '/example-without-namespace.xml');

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'Expected the first document element to have a namespace URI. None found.',
            ),
        );

        SafeDOMXPath::fromString(
            $xml,
            'p',
        );
    }

    public function test_it_can_be_created_with_a_formatted_output(): void
    {
        $xPath = SafeDOMXPath::fromString(
            <<<'XML'
                <?xml version="1.0"?><root><node1></node1><node2></node2></root>
                XML,
            formatOutput: true,
        );

        $expected = <<<'XML'
            <?xml version="1.0"?>
            <root>
              <node1/>
              <node2/>
            </root>

            XML;

        $actual = $xPath->document->saveXML();

        $this->assertSame($expected, $actual);
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

        $books = $xPath->queryList('//book');
        $this->assertCount(2, $books);

        $booksCount = $xPath->queryCount('//book');
        $this->assertSame(2, $booksCount);
    }

    public function test_it_can_query_elements_relative_to_another_node(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $firstBook = $xPath->queryList('//book')->item(0);
        // Sanity check
        $this->assertInstanceOf(DOMNode::class, $firstBook);

        $titlesInFirstBook = $xPath->queryList('.//title', $firstBook);
        $titlesCountInFirstBook = $xPath->queryCount('.//title', $firstBook);
        $titles = $xPath->queryList('.//title');
        $titlesCount = $xPath->queryCount('.//title');

        $this->assertCount(1, $titlesInFirstBook);
        $this->assertSame(1, $titlesCountInFirstBook);
        // Sanity check
        $this->assertCount(2, $titles);
        $this->assertSame(2, $titlesCount);
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

    public function test_it_cannot_query_the_count_with_an_invalid_query(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The query "#" is invalid.',
            ),
        );

        $xPath->queryCount('#');
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

    public function test_it_cannot_query_the_count_with_a_query_with_a_dom_node_that_cannot_be_fetched(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The context node passed for the query "//book" is invalid.',
            ),
        );

        $xPath->queryCount('//book', new DOMNode());
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

    public function test_it_cannot_query_count_with_a_query_with_an_invalid_context_node(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The context node passed for the query "//book" is invalid.',
            ),
        );

        $xPath->queryCount('//book', new DOMNode());
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
        $this->assertInstanceOf(DOMNode::class, $firstBook);

        $expected = $xPath->queryList('//book/title[1]')->item(0);
        $actual = $xPath->queryElement('.//title', $firstBook);
        $anotherActual = $xPath->getElement('.//title', $firstBook);

        $this->assertSame($expected, $actual);
        $this->assertSame($expected, $anotherActual);
    }

    public function test_it_returns_null_if_the_element_could_not_be_found(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $element = $xPath->queryElement('//book[10]');

        $this->assertNull($element);
    }

    public function test_it_throws_if_it_cannot_get_a_dom_element(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'Expected the query "//book[10]" to return a "DOMElement" node. None found.',
            ),
        );

        $xPath->getElement('//book[10]');
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

    public function test_it_cannot_query_a_non_dom_element(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'Expected the query "//book[1]/@category" to return a "DOMElement" node. Got "DOMAttr".',
            ),
        );

        $element = $xPath->queryElement('//book[1]/@category');

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

        $xPath->queryElement('//book', new DOMNode());
    }

    public function test_it_can_query_an_attribute(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $expected = $xPath
            ->queryList('//book')
            ->item(0)
            ?->attributes
            ?->getNamedItem('category');
        // Sanity check
        $this->assertNotNull($expected);

        $actual = $xPath->queryAttribute('///book[1]/@category');

        $this->assertSame($expected, $actual);
    }

    public function test_it_can_query_an_attribute_relative_to_another_node(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $firstBook = $xPath->queryList('//book')->item(0);
        // Sanity check
        $this->assertInstanceOf(DOMNode::class, $firstBook);

        $expected = $xPath
            ->queryList('//book/title[1]')
            ->item(0)
            ?->attributes
            ?->getNamedItem('lang');
        // Sanity check
        $this->assertNotNull($expected);

        $actual = $xPath->queryAttribute('.//title/@lang', $firstBook);

        $this->assertSame($expected, $actual);
    }

    public function test_it_returns_null_if_the_attribute_could_not_be_found(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $attribute = $xPath->queryAttribute('/@unknown');

        $this->assertNull($attribute);
    }

    public function test_it_cannot_query_an_attribute_for_which_there_is_more_than_one_item(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'Expected the query "//book/@category" to return a "DOMNodeList" with no or one node. Got "2".',
            ),
        );

        $element = $xPath->queryAttribute('//book/@category');

        $this->assertNull($element);
    }

    public function test_it_cannot_query_an_attribute_with_an_invalid_query(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The query "#" is invalid.',
            ),
        );

        $xPath->queryAttribute('#');
    }

    public function test_it_cannot_query_an_attribute_with_a_dom_node_that_cannot_be_fetched(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The context node passed for the query "//book/@category" is invalid.',
            ),
        );

        $xPath->queryAttribute('//book/@category', new DOMNode());
    }

    public function test_it_cannot_query_an_attribute_with_an_invalid_context_node(): void
    {
        $xPath = SafeDOMXPath::fromString(self::BOOKSTORE_XML);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The context node passed for the query "//book/@category" is invalid.',
            ),
        );

        $xPath->queryAttribute('//book/@category', new DOMNode());
    }

    public function test_it_has_document_property(): void
    {
        $xPath = SafeDOMXPath::fromString('<?xml version="1.0"?><test/>');

        $this->assertInstanceOf(DOMDocument::class, $xPath->document);
    }
}
