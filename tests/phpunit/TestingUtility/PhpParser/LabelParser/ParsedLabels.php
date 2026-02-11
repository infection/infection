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

use RuntimeException;
use function array_key_exists;
use function count;
use function sprintf;

/**
 * Stores parsed label data mapping lines to node types and labels.
 *
 * Structure:
 * - lineToLabels: [line => [FQN => label]]
 * - labelToLine: [label => line] for duplicate detection
 */
final class ParsedLabels
{
    /**
     * @var array<int, array<class-string, string>>
     */
    private array $lineToLabels = [];

    /**
     * @var array<string, int>
     */
    private array $labelToLine = [];

    /**
     * @param class-string $fqn
     */
    public function addLabel(string $label, string $fqn, int $line): void
    {
        // Check for duplicate labels
        if (array_key_exists($label, $this->labelToLine)) {
            $firstLine = $this->labelToLine[$label];

            throw new RuntimeException(
                sprintf(
                    'Duplicate label "%s" found at lines %d and %d. Each label must be unique within a traversal.',
                    $label,
                    $firstLine,
                    $line,
                ),
            );
        }

        // Store the label
        if (!array_key_exists($line, $this->lineToLabels)) {
            $this->lineToLabels[$line] = [];
        }

        $this->lineToLabels[$line][$fqn] = $label;
        $this->labelToLine[$label] = $line;
    }

    public function isEmpty(): bool
    {
        return count($this->lineToLabels) === 0;
    }

    /**
     * @return array<class-string, string>|null
     */
    public function getLabelsForLine(int $line): ?array
    {
        return $this->lineToLabels[$line] ?? null;
    }

    /**
     * @return array<int, array<class-string, string>>
     */
    public function getAllLineToLabels(): array
    {
        return $this->lineToLabels;
    }
}
