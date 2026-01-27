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

namespace Infection\Logger\MutationAnalysis\TeamCity;

use function array_keys;
use function array_map;
use function implode;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutation\Mutation;
use function is_int;
use function preg_replace;
use function round;
use function sprintf;
use function str_contains;
use function str_replace;

/**
 * This service provides the primitives to write a TeamCity log record. It is
 * adapted to Infection in the context of mutation testing, but it does not
 * make any assumption about the order of the calls made or the support the logs
 * are written to.
 *
 * @see https://www.jetbrains.com/help/teamcity/2025.07/service-messages.html
 *
 * @internal
 */
final readonly class TeamCity
{
    private const MILLISECONDS_PER_SECOND = 1000;

    // `|` must be escaped FIRST to avoid double-escaping.
    private const CHARACTERS_TO_ESCAPE = ['|', "'", "\n", "\r", '[', ']'];

    private const ESCAPED_CHARACTERS = ['||', "|'", '|n', '|r', '|[', '|]'];

    private const UNICODE_CHARACTER_REGEX = '/\\\\u(?<hexadecimalDigits>[0-9A-Fa-f]{4})/';

    public function __construct(
        private bool $timeoutsAsEscaped,
    ) {
    }

    /**
     * @param positive-int $count
     */
    public function testCount(int $count): string
    {
        return $this->write(
            MessageName::TEST_COUNT,
            ['count' => (string) $count],
        );
    }

    public function testSuiteStarted(
        string $location,
        string $name,
        string $flowId,
    ): string {
        return $this->write(
            MessageName::TEST_SUITE_STARTED,
            [
                'name' => $name,
                'nodeId' => $flowId,
                'parentNodeId' => '0',
                'locationHint' => sprintf(
                    'file://%s',
                    $location,
                ),
            ],
        );
    }

    public function testSuiteFinished(string $name, string $flowId): string
    {
        return $this->write(
            MessageName::TEST_SUITE_FINISHED,
            [
                'name' => $name,
                'nodeId' => $flowId,
            ],
        );
    }

    /**
     * @param string $flowId Flow ID of the test suite the test belongs to.
     */
    public function testStarted(
        Mutation $mutation,
        string $flowId,
        string $parentFlowId,
    ): string {
        // TODO: feels stupid that we are computing the name multiple times
        return $this->write(
            MessageName::TEST_STARTED,
            [
                'name' => self::createTestName($mutation),
                'nodeId' => $flowId,
                'parentNodeId' => $parentFlowId,
                'mutationId' => $mutation->getHash(),
            ],
        );
    }

    public function testFinished(
        MutantExecutionResult $executionResult,
        string $flowId,
        string $parentFlowId,
    ): string {
        return $this->write(
            $this->mapExecutionResultToTestStatus($executionResult),
            [
                'name' => self::createTestName($executionResult),
                'nodeId' => $flowId,
                'parentNodeId' => $parentFlowId,
                // TODO: looks like this information is not used when the test is marked as successful or ignored :/
                'message' => sprintf(
                    <<<'MESSAGE'
                        Mutator: %s
                        Mutation ID: %s
                        Mutation result: %s
                        MESSAGE,
                    $executionResult->getMutatorName(),
                    $executionResult->getMutantHash(),
                    $executionResult->getDetectionStatus()->value,
                ),
                'details' => $executionResult->getMutantDiff(),
                'duration' => self::getExecutionDurationInMs($executionResult),
            ],
        );
    }

    /**
     * @see https://www.jetbrains.com/help/teamcity/2025.07/service-messages.html#Service+Messages+Formats
     *
     * @param string|array<non-empty-string|int, string|int|float> $valueOrAttributes
     */
    public function write(
        MessageName $messageName,
        string|array $valueOrAttributes,
    ): string {
        return sprintf(
            '##teamcity[%s]' . "\n",
            implode(
                ' ',
                [
                    $messageName->value,
                    ...self::escape((array) $valueOrAttributes),
                ],
            ),
        );
    }

    /**
     * @return array{MessageName, array<non-empty-string|int, string|int|float>}
     */
    private function mapExecutionResultToTestStatus(MutantExecutionResult $executionResult): MessageName
    {
        $detectionStatus = $executionResult->getDetectionStatus();

        return match ($detectionStatus) {
            DetectionStatus::KILLED_BY_TESTS,
            DetectionStatus::KILLED_BY_STATIC_ANALYSIS,
            DetectionStatus::ERROR,
            DetectionStatus::SYNTAX_ERROR => MessageName::TEST_FINISHED,

            DetectionStatus::ESCAPED => MessageName::TEST_FAILED,

            DetectionStatus::SKIPPED,
            DetectionStatus::NOT_COVERED,
            DetectionStatus::IGNORED => MessageName::TEST_IGNORED,

            DetectionStatus::TIMED_OUT => $this->timeoutsAsEscaped
                ? MessageName::TEST_FAILED
                : MessageName::TEST_FINISHED,
        };
    }

    private function createTestName(Mutation|MutantExecutionResult $subject): string
    {
        // TODO: add a test to make it obvious: a test name must be unique
        return sprintf(
            '%s (%s)',
            $subject->getMutatorClass(),
            $subject instanceof Mutation
                ? $subject->getHash()
                : $subject->getMutantHash(),
        );
    }

    /**
     * @param array<non-empty-string|int, string|int|float> $values
     *
     * @return non-empty-string[]
     */
    private static function escape(array $values): array
    {
        return array_map(
            static function (string|int $key) use ($values): string {
                $value = $values[$key];

                return is_int($key)
                    ? self::escapeValue($value)
                    : sprintf(
                        '%s=%s',
                        $key,
                        self::escapeValue($value),
                    );
            },
            array_keys($values),
        );
    }

    /**
     * @return non-empty-string
     */
    private static function escapeValue(string|int|float $value): string
    {
        $escapedValue = sprintf(
            '\'%s\'',
            str_replace(
                self::CHARACTERS_TO_ESCAPE,
                self::ESCAPED_CHARACTERS,
                (string) $value,
            ),
        );

        if (str_contains($value, '\u')) {
            $escapedValue = preg_replace(
                self::UNICODE_CHARACTER_REGEX,
                '|0x$1',
                $escapedValue,
            );
        }

        return $escapedValue;
    }

    private static function getExecutionDurationInMs(MutantExecutionResult $executionResult): string
    {
        // TODO: this is actually incorrect! Or is it?
        //  this could be either the (singular) process, but what about:
        //  - the other processes executed prior?
        //  - the time taken by the heuristics?
        //  - the waiting time in-between being generated and processes?
        return (string) round($executionResult->getProcessRuntime() * self::MILLISECONDS_PER_SECOND);
    }
}
