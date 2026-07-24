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

namespace Infection\Tests\AutoReview\AI;

use function basename;
use function implode;
use function ksort;
use function preg_quote;
use function Safe\preg_match;
use function Safe\preg_replace;
use function sprintf;
use function str_starts_with;
use function trim;
use UnexpectedValueException;
use Webmozart\Assert\Assert;

/**
 * Generates an index for the given ADRs.
 */
final class AdrIndexGenerator
{
    private const string START_MARKER = '<!-- adr-index:start -->';

    private const string END_MARKER = '<!-- adr-index:end -->';

    private const string TEMPLATE_ADR = '0000-template.md';

    /**
     * @param array<string, string> $adrs Indexed by filename
     */
    public function generate(string $contents, array $adrs): string
    {
        ksort($adrs);

        $entries = [];

        foreach ($adrs as $filename => $adrContents) {
            if (basename($filename) === self::TEMPLATE_ADR) {
                continue;
            }

            $entries[] = self::generateEntry($filename, $adrContents);
        }

        $index = self::START_MARKER . "\n" . implode("\n", $entries) . "\n" . self::END_MARKER;

        return self::replace($contents, $index);
    }

    private static function replace(string $contents, string $index): string
    {
        $replacementCount = 0;
        $updatedContents = preg_replace(
            '/' . preg_quote(self::START_MARKER, '/') . '.*?' . preg_quote(self::END_MARKER, '/') . '/s',
            $index,
            $contents,
            -1,
            $replacementCount,
        );

        if ($replacementCount !== 1) {
            throw new UnexpectedValueException('Expected exactly one ADR index in AGENTS.md.');
        }
        Assert::string($updatedContents);

        return $updatedContents;
    }

    private static function generateEntry(string $filename, string $contents): string
    {
        $titleMatches = [];

        if (preg_match('/^# (?<title>.+)$/m', $contents, $titleMatches) !== 1) {
            throw new UnexpectedValueException(
                sprintf(
                    'Could not find the title in %s.',
                    $filename,
                ),
            );
        }

        Assert::isArray($titleMatches);
        Assert::keyExists($titleMatches, 'title');
        Assert::string($titleMatches['title']);

        $statusMatches = [];

        if (preg_match('/^#{2,3} Status\R+(?<status>[^\r\n]+)/m', $contents, $statusMatches) !== 1) {
            throw new UnexpectedValueException(
                sprintf(
                    'Could not find the status in %s.',
                    $filename,
                ),
            );
        }
        Assert::isArray($statusMatches);
        Assert::keyExists($statusMatches, 'status');
        Assert::string($statusMatches['status']);

        $numberMatches = [];

        if (preg_match('/^(?<number>\d{4})-/', basename($filename), $numberMatches) !== 1) {
            throw new UnexpectedValueException(
                sprintf(
                    'Could not find the ADR number in %s.',
                    $filename,
                ),
            );
        }

        Assert::isArray($numberMatches);
        Assert::keyExists($numberMatches, 'number');
        Assert::string($numberMatches['number']);

        return sprintf(
            '- [ADR %s: %s](adr/%s) — %s',
            $numberMatches['number'],
            $titleMatches['title'],
            basename($filename),
            self::normalizeStatus($filename, $statusMatches['status']),
        );
    }

    private static function normalizeStatus(string $filename, string $status): string
    {
        $status = trim($status, ". \t\n\r\0\x0B");

        foreach (['Accepted', 'Deprecated', 'Proposed', 'Rejected'] as $knownStatus) {
            if (str_starts_with($status, $knownStatus)) {
                return $knownStatus;
            }
        }

        $matches = [];

        if (
            str_starts_with($status, 'Superseded')
            && preg_match('/ADR (?<number>\d{4})/', $status, $matches) === 1
        ) {
            Assert::isArray($matches);
            Assert::keyExists($matches, 'number');
            Assert::string($matches['number']);

            return sprintf(
                'Superseded by ADR %s',
                $matches['number'],
            );
        }

        throw new UnexpectedValueException(
            sprintf(
                'Could not determine the status in %s.',
                $filename,
            ),
        );
    }
}
