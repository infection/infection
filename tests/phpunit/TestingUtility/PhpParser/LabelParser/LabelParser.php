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

namespace Infection\Tests\TestingUtility\PhpParser\LabelParser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use RuntimeException;
use function preg_match;
use function preg_match_all;
use function sprintf;

/**
 * Extracts and validates @label annotations from comments in PhpParser nodes.
 *
 * Label format: @label:NodeType:Label
 * Example: @label:Expr_Variable:my-var
 */
final class LabelParser
{
    private const LABEL_PATTERN = '/@label:([^:]+):([^\s]+)/';
    private const LABEL_NAME_PATTERN = '/^[a-zA-Z][a-zA-Z0-9_-]*$/';

    /**
     * @param Node[] $nodes
     */
    public function parseLabelsFromNodes(array $nodes): ParsedLabels
    {
        $parsedLabels = new ParsedLabels();

        // Create a visitor to collect labels from all nodes
        $visitor = new class($parsedLabels, $this) extends NodeVisitorAbstract {
            public function __construct(
                private readonly ParsedLabels $parsedLabels,
                private readonly LabelParser $labelParser,
            ) {
            }

            public function enterNode(Node $node): void
            {
                $this->labelParser->extractLabelsFromNode($node, $this->parsedLabels);
            }
        };

        $traverser = new NodeTraverser($visitor);
        $traverser->traverse($nodes);

        return $parsedLabels;
    }

    public function extractLabelsFromNode(Node $node, ParsedLabels $parsedLabels): void
    {
        $nodeStartLine = $node->getStartLine();

        if ($nodeStartLine === -1) {
            return; // Node has no line information
        }

        foreach ($node->getComments() as $comment) {
            $commentStartLine = $comment->getStartLine();
            $commentEndLine = $comment->getEndLine();

            // PhpParser attaches comments to nodes in complex ways.
            // We simply store labels based on the comment's line number.
            // The visitor will handle matching labels to nodes by checking
            // both the node's line and the line before it.
            $targetLine = $commentEndLine;

            // Extract all @label patterns from the comment
            $text = $comment->getText();
            $matches = [];
            preg_match_all(self::LABEL_PATTERN, $text, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $fullMatch = $match[0];
                $nodeType = $match[1];
                $label = $match[2];

                // Validate label name format
                if (preg_match(self::LABEL_NAME_PATTERN, $label) !== 1) {
                    throw new RuntimeException(
                        sprintf(
                            'Invalid label name "%s" at line %d. Labels must match /^[a-zA-Z][a-zA-Z0-9_-]*$/',
                            $label,
                            $targetLine,
                        ),
                    );
                }

                // Convert node type to FQN and validate it exists
                $fqn = NodeTypeConverter::convertToFqn($nodeType, $label, $targetLine);

                // Add to parsed labels (this also checks for duplicates)
                $parsedLabels->addLabel($label, $fqn, $targetLine);
            }
        }
    }
}
