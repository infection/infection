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

namespace Infection\Tests\TestingUtility\PhpParser\NodeDumper;

use function get_debug_type;
use function implode;
use Infection\Tests\TestingUtility\PhpParser\Visitor\AddIdToTraversedNodesVisitor\AddIdToTraversedNodesVisitor;
use Infection\Tests\TestingUtility\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use InvalidArgumentException;
use function is_array;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use Later\Interfaces\Deferred;
use PhpParser\Comment;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use RuntimeException;
use function sprintf;
use function str_replace;
use function strlen;
use function strrpos;
use function substr;
use Webmozart\Assert\Assert;

/**
 * This NodeDumper is taken after the PhpParser one. It has been copied and adapted to our needs. But unless
 * we reach the point where we have so much more custom code that it's better to rewrite it our way, it is better
 * to keep it as close to the original as possible.
 *
 * BSD 3-Clause License
 *
 * Copyright (c) 2011, Nikita Popov
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its
 * contributors may be used to endorse or promote products derived from
 * this software without specific prior written permission.
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
final class NodeDumper
{
    private const IGNORE_ATTRIBUTES = [
        'comments' => true,
        'startLine' => true,
        'endLine' => true,
        'startFilePos' => true,
        'endFilePos' => true,
        'startTokenPos' => true,
        'endTokenPos' => true,
        // Infection specific attribute(s)
        MarkTraversedNodesAsVisitedVisitor::VISITED_ATTRIBUTE => true,
    ];

    // Removed instance properties for stateless refactor
    /**
     * @param bool $dumpComments whether comments should be dumped
     * @param bool $dumpPositions Whether line/offset information should be dumped. To dump offset
     *                            information, the code needs to be passed to dump().
     * @param bool $dumpOtherAttributes whether non-comment, non-position attributes should be dumped
     */
    public function __construct(
        private readonly bool $dumpProperties = false,
        private readonly bool $dumpComments = false,
        private bool $dumpPositions = false,
        private readonly bool $dumpOtherAttributes = true,
        // Infection specific parameter(s)
        private bool $onlyVisitedNodes = true,
    ) {
    }

    /**
     * Dumps a node or array.
     *
     * @param Node[]|Node $node Node or array to dump
     * @param string|null $code Code corresponding to dumped AST. This only needs to be passed if
     *                          the dumpPositions option is enabled, and the dumping of node offsets
     *                          is desired.
     *
     * @throws PotentialCircularDependencyDetected
     */
    public function dump(
        array|Node $node,
        ?string $code = null,
        ?bool $dumpPositions = null,
        // Infection specific parameter(s)
        ?bool $onlyVisitedNodes = null,
    ): string {
        $result = '';
        $newLine = "\n";

        if ($onlyVisitedNodes !== null) {
            $originalOnlyVisitedNodes = $this->onlyVisitedNodes;
            $this->onlyVisitedNodes = $onlyVisitedNodes;
        }

        if ($dumpPositions !== null) {
            Assert::notNull($code, 'The original code is necessary for dumping positions.');

            $originalDumpPositions = $this->dumpPositions;
            $this->dumpPositions = $dumpPositions;
        }

        $this->dumpRecursive(
            $node,
            $code,
            $result,
            $newLine,
            indent: false,
        );

        if ($onlyVisitedNodes !== null) {
            $this->onlyVisitedNodes = $originalOnlyVisitedNodes;
        }

        if ($dumpPositions !== null) {
            $this->dumpPositions = $originalDumpPositions;
        }

        return $result;
    }

    protected function dumpFlags(int $flags): string
    {
        $strs = [];

        if ($flags & Modifiers::PUBLIC) {
            $strs[] = 'PUBLIC';
        }

        if ($flags & Modifiers::PROTECTED) {
            $strs[] = 'PROTECTED';
        }

        if ($flags & Modifiers::PRIVATE) {
            $strs[] = 'PRIVATE';
        }

        if ($flags & Modifiers::ABSTRACT) {
            $strs[] = 'ABSTRACT';
        }

        if ($flags & Modifiers::STATIC) {
            $strs[] = 'STATIC';
        }

        if ($flags & Modifiers::FINAL) {
            $strs[] = 'FINAL';
        }

        if ($flags & Modifiers::READONLY) {
            $strs[] = 'READONLY';
        }

        if ($flags & Modifiers::PUBLIC_SET) {
            $strs[] = 'PUBLIC_SET';
        }

        if ($flags & Modifiers::PROTECTED_SET) {
            $strs[] = 'PROTECTED_SET';
        }

        if ($flags & Modifiers::PRIVATE_SET) {
            $strs[] = 'PRIVATE_SET';
        }

        if ($strs) {
            return implode(' | ', $strs) . ' (' . $flags . ')';
        }

        return (string) $flags;
    }

    /**
     * Dump node position, if possible.
     *
     * @param Node $node Node for which to dump position
     *
     * @return string|null Dump of position, or null if position information not available
     */
    protected function dumpPosition(Node $node, ?string $code): ?string
    {
        if (!$node->hasAttribute('startLine') || !$node->hasAttribute('endLine')) {
            return null;
        }

        $start = $node->getStartLine();
        $end = $node->getEndLine();

        if ($node->hasAttribute('startFilePos') && $node->hasAttribute('endFilePos')
            && $code !== null
        ) {
            $start .= ':' . $this->toColumn($code, $node->getStartFilePos());
            $end .= ':' . $this->toColumn($code, $node->getEndFilePos());
        }

        return "[$start - $end]";
    }

    /**
     * @throws PotentialCircularDependencyDetected
     */
    private function dumpRecursive(
        mixed $node,
        ?string $code,
        string &$result,
        string &$newLine,
        bool $indent = true,
    ): void {
        $previousNewLine = $newLine;

        if ($indent) {
            $newLine .= '    ';
        }

        if ($node instanceof Node) {
            if ($this->onlyVisitedNodes && !MarkTraversedNodesAsVisitedVisitor::wasVisited($node)) {
                $result .= '<skipped>';
                $newLine = $previousNewLine;

                return;
            }

            $result .= $node->getType();

            if ($this->dumpPositions && null !== $p = $this->dumpPosition($node, $code)) {
                $result .= $p;
            }

            $hasDetails = false;
            $nodeDetails = '(';

            foreach ($node->getSubNodeNames() as $key) {
                $value = $node->$key;

                // Skip "extra" properties unless configured to dump them
                if (!$this->dumpProperties && !$this->isNodeOrNodeArray($value)) {
                    continue;
                }

                $hasDetails = true;
                $nodeDetails .= "$newLine    " . $key . ': ';

                if (is_int($value)) {
                    if ($key === 'flags' || $key === 'newModifier') {
                        $nodeDetails .= $this->dumpFlags($value);

                        continue;
                    }

                    if ($key === 'type' && $node instanceof Include_) {
                        $nodeDetails .= $this->dumpIncludeType($value);

                        continue;
                    }

                    if ($key === 'type'
                        && ($node instanceof Use_ || $node instanceof UseItem || $node instanceof GroupUse)) {
                        $nodeDetails .= $this->dumpUseType($value);

                        continue;
                    }
                }

                $this->dumpRecursive($value, $code, $nodeDetails, $newLine);
            }

            if ($this->dumpComments && $comments = $node->getComments()) {
                $hasDetails = true;
                $nodeDetails .= "$newLine    comments: ";

                $this->dumpRecursive($comments, $code, $nodeDetails, $newLine);
            }

            if ($this->dumpOtherAttributes) {
                foreach ($node->getAttributes() as $key => $value) {
                    if (isset(self::IGNORE_ATTRIBUTES[$key])) {
                        continue;
                    }

                    $hasDetails = true;
                    $nodeDetails .= "$newLine    $key: ";

                    if (is_int($value)) {
                        if ($key === 'kind') {
                            if ($node instanceof Int_) {
                                $nodeDetails .= $this->dumpIntKind($value);

                                continue;
                            }

                            if ($node instanceof String_ || $node instanceof InterpolatedString) {
                                $nodeDetails .= $this->dumpStringKind($value);

                                continue;
                            }

                            if ($node instanceof Array_) {
                                $nodeDetails .= $this->dumpArrayKind($value);

                                continue;
                            }

                            if ($node instanceof List_) {
                                $nodeDetails .= $this->dumpListKind($value);

                                continue;
                            }
                        }
                    }

                    // This was added: add native support for Node ids. This removes
                    // the need to employ various tricks for circular dependencies.
                    if ($value instanceof Node) {
                        if ($value->hasAttribute(AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE)) {
                            $nodeDetails .= sprintf(
                                'nodeId(%s)',
                                $value->getAttribute(AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE),
                            );
                        } else {
                            throw PotentialCircularDependencyDetected::forAttribute($key, $value);
                        }
                    } else {
                        $this->dumpRecursive($value, $code, $nodeDetails, $newLine);
                    }
                }
            }

            $nodeDetails .= "$newLine)";

            if ($hasDetails) {
                $result .= $nodeDetails;
            }
        } elseif (is_array($node)) {
            $result .= 'array(';

            foreach ($node as $key => $value) {
                $result .= "$newLine    " . $key . ': ';

                $this->dumpRecursive($value, $code, $result, $newLine);
            }

            $result .= "$newLine)";
        } elseif ($node instanceof Comment) {
            $result .= str_replace("\n", $newLine, $node->getReformattedText());
        } elseif (is_string($node)) {
            $result .= str_replace("\n", $newLine, $node);
        } elseif (is_int($node) || is_float($node)) {
            $result .= $node;
        } elseif ($node === null) {
            $result .= 'null';
        } elseif ($node === false) {
            $result .= 'false';
        } elseif ($node === true) {
            $result .= 'true';
        } elseif ($node instanceof Deferred) {
            // TODO: this condition was changed compared to the original PHP-Parser code.
            $result .= 'Deferred(';

            $deferredValue = $node->get();

            if ($deferredValue === null) {
                $result .= 'null)';
            } else {
                Assert::isIterable($deferredValue);

                $result .= 'array(';

                foreach ($deferredValue as $key => $value) {
                    $result .= "$newLine    " . $key . ': ';

                    $this->dumpRecursive($value, $code, $result, $newLine);
                }

                $result .= "$newLine))";
            }
        } elseif (is_object($node)) {
            $result .= $node::class;
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'Can only dump nodes and arrays. Got "%s".',
                    get_debug_type($node),
                ),
            );
        }

        if ($indent) {
            $newLine = substr($newLine, 0, -4);
        }
    }

    /**
     * Check if a value is a Node or an array of Node values
     */
    private function isNodeOrNodeArray(mixed $value): bool
    {
        if ($value instanceof Node) {
            return true;
        }

        if (!is_array($value)) {
            return false;
        }

        // Check if it's an array of nodes
        foreach ($value as $item) {
            if ($item instanceof Node) {
                return true;
            }
        }

        return false;
    }

    /** @param array<int, string> $map */
    private function dumpEnum(int $value, array $map): string
    {
        if (!isset($map[$value])) {
            return (string) $value;
        }

        return $map[$value] . ' (' . $value . ')';
    }

    private function dumpIncludeType(int $type): string
    {
        return $this->dumpEnum($type, [
            Include_::TYPE_INCLUDE => 'TYPE_INCLUDE',
            Include_::TYPE_INCLUDE_ONCE => 'TYPE_INCLUDE_ONCE',
            Include_::TYPE_REQUIRE => 'TYPE_REQUIRE',
            Include_::TYPE_REQUIRE_ONCE => 'TYPE_REQUIRE_ONCE',
        ]);
    }

    private function dumpUseType(int $type): string
    {
        return $this->dumpEnum($type, [
            Use_::TYPE_UNKNOWN => 'TYPE_UNKNOWN',
            Use_::TYPE_NORMAL => 'TYPE_NORMAL',
            Use_::TYPE_FUNCTION => 'TYPE_FUNCTION',
            Use_::TYPE_CONSTANT => 'TYPE_CONSTANT',
        ]);
    }

    private function dumpIntKind(int $kind): string
    {
        return $this->dumpEnum($kind, [
            Int_::KIND_BIN => 'KIND_BIN',
            Int_::KIND_OCT => 'KIND_OCT',
            Int_::KIND_DEC => 'KIND_DEC',
            Int_::KIND_HEX => 'KIND_HEX',
        ]);
    }

    private function dumpStringKind(int $kind): string
    {
        return $this->dumpEnum($kind, [
            String_::KIND_SINGLE_QUOTED => 'KIND_SINGLE_QUOTED',
            String_::KIND_DOUBLE_QUOTED => 'KIND_DOUBLE_QUOTED',
            String_::KIND_HEREDOC => 'KIND_HEREDOC',
            String_::KIND_NOWDOC => 'KIND_NOWDOC',
        ]);
    }

    private function dumpArrayKind(int $kind): string
    {
        return $this->dumpEnum($kind, [
            Array_::KIND_LONG => 'KIND_LONG',
            Array_::KIND_SHORT => 'KIND_SHORT',
        ]);
    }

    private function dumpListKind(int $kind): string
    {
        return $this->dumpEnum($kind, [
            List_::KIND_LIST => 'KIND_LIST',
            List_::KIND_ARRAY => 'KIND_ARRAY',
        ]);
    }

    // Copied from Error class
    private function toColumn(string $code, int $pos): int
    {
        if ($pos > strlen($code)) {
            throw new RuntimeException('Invalid position information');
        }

        $lineStartPos = strrpos($code, "\n", $pos - strlen($code));

        if ($lineStartPos === false) {
            $lineStartPos = -1;
        }

        return $pos - $lineStartPos;
    }
}
