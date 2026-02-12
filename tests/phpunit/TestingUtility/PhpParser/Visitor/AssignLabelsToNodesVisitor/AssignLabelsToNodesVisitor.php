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

namespace Infection\Tests\TestingUtility\PhpParser\Visitor\AssignLabelsToNodesVisitor;

use Infection\Tests\TestingUtility\PhpParser\LabelParser\ParsedLabels;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use RuntimeException;
use function array_key_exists;
use function sprintf;

/**
 * Visitor that assigns labels to nodes based on parsed label data.
 *
 * Labels are stored as node attributes and can be retrieved via getNodeLabel().
 */
final class AssignLabelsToNodesVisitor extends NodeVisitorAbstract
{
    public const NODE_LABEL_ATTRIBUTE = 'nodeLabel';

    /**
     * @var array<string, Node>
     */
    private array $labeledNodes = [];

    /**
     * @var array<int, array<class-string, string>> Remaining unmatched labels
     */
    private array $unmatchedLabels;

    /**
     * @var array<int, array<class-string, true>> Track which types have been matched per line
     */
    private array $matchedTypesPerLine = [];

    public function __construct(
        private readonly ParsedLabels $parsedLabels,
    ) {
        $this->unmatchedLabels = $parsedLabels->getAllLineToLabels();
    }

    public static function getNodeLabel(Node $node): ?string
    {
        return $node->getAttribute(self::NODE_LABEL_ATTRIBUTE);
    }

    public function enterNode(Node $node): void
    {
        $line = $node->getStartLine();

        if ($line === -1) {
            return; // Node has no line information
        }

        // Check for labels on this line AND the line before (for prefix comments)
        $linesToCheck = [$line];

        if ($line > 1) {
            $linesToCheck[] = $line - 1;
        }

        foreach ($linesToCheck as $lineToCheck) {
            $labelsForLine = $this->parsedLabels->getLabelsForLine($lineToCheck);

            if ($labelsForLine === null) {
                continue; // No labels for this line
            }

            // Check each label on this line
            foreach ($labelsForLine as $fqn => $label) {
                $this->processLabel($node, $line, $lineToCheck, $fqn, $label);
            }
        }
    }

    private function processLabel(Node $node, int $nodeLine, int $labelLine, string $fqn, string $label): void
    {
        // Check if this node matches the FQN
        if (!$node instanceof $fqn) {
            return;
        }

        // Check if we already matched a node of this type on this label's line
        if (array_key_exists($labelLine, $this->matchedTypesPerLine)
            && array_key_exists($fqn, $this->matchedTypesPerLine[$labelLine])
        ) {
            throw new RuntimeException(
                sprintf(
                    'Multiple nodes of type "%s" found on line %d for label "%s". Please restructure the code to have one node per line, or use a prefix comment on a separate line.',
                    $this->getShortNodeType($fqn),
                    $labelLine,
                    $label,
                ),
            );
        }

        // Mark this type as matched on the label's line
        if (!array_key_exists($labelLine, $this->matchedTypesPerLine)) {
            $this->matchedTypesPerLine[$labelLine] = [];
        }

        $this->matchedTypesPerLine[$labelLine][$fqn] = true;

        // Store label in node attribute
        $node->setAttribute(self::NODE_LABEL_ATTRIBUTE, $label);

        // Add to labeled nodes map
        $this->labeledNodes[$label] = $node;

        // Remove from unmatched labels
        unset($this->unmatchedLabels[$labelLine][$fqn]);

        if (count($this->unmatchedLabels[$labelLine]) === 0) {
            unset($this->unmatchedLabels[$labelLine]);
        }
    }

    /**
     * @return array<string, Node>
     */
    public function getLabeledNodes(): array
    {
        // Validate all labels were matched
        foreach ($this->unmatchedLabels as $line => $nodeTypeToLabel) {
            foreach ($nodeTypeToLabel as $fqn => $label) {
                throw new RuntimeException(
                    sprintf(
                        'No node of type "%s" found for label "%s" at line %d',
                        $this->getShortNodeType($fqn),
                        $label,
                        $line,
                    ),
                );
            }
        }

        return $this->labeledNodes;
    }

    /**
     * Convert FQN back to short node type for error messages.
     *
     * PhpParser\Node\Expr\Variable -> Expr_Variable
     * PhpParser\Node\Stmt\Function_ -> Stmt_Function (removes trailing underscore)
     *
     * @param class-string $fqn
     */
    private function getShortNodeType(string $fqn): string
    {
        $shortType = str_replace('PhpParser\\Node\\', '', $fqn);
        $shortType = str_replace('\\', '_', $shortType);

        // Remove trailing underscore from reserved keyword class names
        // (e.g., Function_, String_, Int_)
        return rtrim($shortType, '_');
    }
}
