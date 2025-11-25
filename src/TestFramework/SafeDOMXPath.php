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

namespace Infection\TestFramework;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNameSpaceNode;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Error;
use InvalidArgumentException;
use function sprintf;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final readonly class SafeDOMXPath
{
    private DOMXPath $xPath;

    public function __construct(
        public DOMDocument $document,
    ) {
        $this->xPath = new DOMXPath($document);
    }

    public static function fromFile(
        string $pathname,
        ?string $namespace = null,
    ): self {
        Assert::file($pathname);
        Assert::readable($pathname);

        $document = new DOMDocument();
        $loaded = @$document->load($pathname);

        Assert::true(
            $loaded,
            sprintf(
                'The file "%s" does not contain valid XML.',
                $pathname,
            ),
        );

        $xPath = new self($document);

        if ($namespace !== null) {
            // Beware that this is not a universal solution: it only works because
            // of the type of document we handle.
            // A generic XML can have the namespace at the root or any element...
            // @phpstan-ignore property.nonObject
            $namespaceUri = $document->documentElement->namespaceURI;
            Assert::notNull($namespaceUri, 'Expected the first document element to have a namespace URI. None found.');

            $xPath->registerNamespace($namespace, $namespaceUri);
        }

        return $xPath;
    }

    /**
     * Warning: doing a `file_get_contents()` + `::fromString()` is quite slower
     * than `::fromFile()`.
     *
     * @param bool|null $formatOutput Nicely formats output with indentation and extra space. Has no effect if the document was loaded with preserveWhitespace enabled.
     *
     * @see https://php.net/manual/en/class.domdocument.php#domdocument.props.formatoutput
     */
    public static function fromString(
        string $xml,
        ?string $namespace = null,
        bool $preserveWhiteSpace = true,
        ?bool $formatOutput = null,
    ): self {
        $document = new DOMDocument();
        $document->preserveWhiteSpace = $preserveWhiteSpace;

        if ($formatOutput !== null) {
            $document->formatOutput = $formatOutput;
        }

        $success = @$document->loadXML($xml);

        Assert::true(
            $success,
            sprintf(
                'The string "%s" is not valid XML.',
                $xml,
            ),
        );

        $xPath = new self($document);

        if ($namespace !== null) {
            // Beware that this is not a universal solution: it only works because
            // of the type of document we handle.
            // A generic XML can have the namespace at the root or any element...
            // @phpstan-ignore property.nonObject
            $namespaceUri = $document->documentElement->namespaceURI;
            Assert::notNull($namespaceUri, 'Expected the first document element to have a namespace URI. None found.');

            $xPath->registerNamespace($namespace, $namespaceUri);
        }

        return $xPath;
    }

    /**
     * @return int<0,max>
     */
    public function queryCount(string $query, ?DOMNode $contextNode = null): int
    {
        return $this->queryList($query, $contextNode)->length;
    }

    /**
     * @return DOMNodeList<DOMNameSpaceNode|DOMNode>
     */
    public function queryList(string $query, ?DOMNode $contextNode = null): DOMNodeList
    {
        try {
            $nodes = @$this->xPath->query($query, $contextNode);
        } catch (Error) {
            throw new InvalidArgumentException(
                sprintf(
                    'The context node passed for the query "%s" is invalid.',
                    $query,
                ),
            );
        }

        Assert::isInstanceOf(
            $nodes,
            DOMNodeList::class,
            sprintf(
                'The query "%s" is invalid.',
                $query,
            ),
        );

        return $nodes;
    }

    public function queryAttribute(string $query, ?DOMNode $contextNode = null): ?DOMAttr
    {
        $nodes = $this->queryList($query, $contextNode);

        Assert::true(
            $nodes->length <= 1,
            sprintf(
                'Expected the query "%s" to return a "%s" with no or one node. Got "%s".',
                $query,
                DOMNodeList::class,
                $nodes->length,
            ),
        );

        return $nodes[0] ?? null;
    }

    public function queryElement(string $query, ?DOMNode $contextNode = null): ?DOMElement
    {
        $nodes = $this->queryList($query, $contextNode);

        Assert::true(
            $nodes->length <= 1,
            sprintf(
                'Expected the query "%s" to return a "%s" with no or one node. Got "%s".',
                $query,
                DOMNodeList::class,
                $nodes->length,
            ),
        );

        $node = $nodes->item(0);

        if ($node !== null) {
            Assert::isInstanceOf(
                $node,
                DOMElement::class,
                sprintf(
                    'Expected the query "%s" to return a "%s" node. Got "%s".',
                    $query,
                    DOMElement::class,
                    $node::class,
                ),
            );
        }

        return $node;
    }

    public function getElement(string $query, ?DOMNode $contextNode = null): DOMElement
    {
        $node = $this->queryElement($query, $contextNode);

        Assert::notNull(
            $node,
            sprintf(
                'Expected the query "%s" to return a "%s" node. None found.',
                $query,
                DOMElement::class,
            ),
        );

        return $node;
    }

    private function registerNamespace(string $prefix, string $namespace): void
    {
        $result = $this->xPath->registerNamespace($prefix, $namespace);
        Assert::true($result);
    }
}
