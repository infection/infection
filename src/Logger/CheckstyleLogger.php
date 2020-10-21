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

namespace Infection\Logger;

use DOMDocument;
use DOMElement;
use Infection\Metrics\MetricsCalculator;

/**
 * @internal
 */
final class CheckstyleLogger implements LineMutationTestingResultsLogger
{
    private MetricsCalculator $metricsCalculator;

    public function __construct(MetricsCalculator $metricsCalculator)
    {
        $this->metricsCalculator = $metricsCalculator;
    }

    public function getLogLines(): array
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $checkstyleNode = $dom->createElement('checkstyle');
        $checkstyleNode->setAttribute('version', '6.5');

        $allErrors = [];

        foreach ($this->metricsCalculator->getEscapedExecutionResults() as $escapedExecutionResult) {
            $filePath = $escapedExecutionResult->getOriginalFilePath();

            $allErrors[$filePath][] = [
                'line' => $escapedExecutionResult->getOriginalStartingLine(),
                'severity' => 'warning',
                'message' => <<<"TEXT"
Escaped Mutant:

{$escapedExecutionResult->getMutantDiff()}
TEXT
            ,
                'source' => $escapedExecutionResult->getMutatorName(),
            ];
        }

        /** @var array<string, array{line: string, severity: string, message: string, source: string}> $errors */
        foreach ($allErrors as $filePath => $errors) {
            $fileNode = $this->buildFileErrors($dom, $filePath, $errors);

            $checkstyleNode->appendChild($fileNode);
        }

        $dom->appendChild($checkstyleNode);

        return [(string) $dom->saveXML()];
    }

    /**
     * @param array<string, array{line: string, severity: string, message: string, source: string}> $errors
     */
    private function buildFileErrors(DOMDocument $dom, string $filePath, array $errors): DOMElement
    {
        $fileNode = $dom->createElement('file');
        $fileNode->setAttribute('name', $filePath);

        foreach ($errors as $error) {
            $errorNode = $dom->createElement('error');
            $errorNode->setAttribute('severity', 'warning');
            $errorNode->setAttribute('line', (string) $error['line']);
            $errorNode->setAttribute('message', $error['message']);
            $errorNode->setAttribute('source', $error['source']);

            $fileNode->appendChild($errorNode);
        }

        return $fileNode;
    }
}
