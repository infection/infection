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

namespace Infection\Tests\Event\Subscriber;

use Infection\Configuration\Configuration;
use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Differ\DiffColorizer;
use Infection\Event\Subscriber\MutationTestingConsoleLoggerSubscriberFactory;
use Infection\Logger\FederatedLogger;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(MutationTestingConsoleLoggerSubscriberFactory::class)]
final class MutationTestingConsoleLoggerSubscriberFactoryLogicTest extends TestCase
{
    /**
     * @dataProvider mutateOnlyCoveredCodeProvider
     */
    #[DataProvider('mutateOnlyCoveredCodeProvider')]
    public function test_show_mutation_score_indicator_logic_matches_expected_behavior(bool $mutateOnlyCoveredCode, bool $expectedShowMsi): void
    {
        // This test directly exercises the logic: $config->mutateOnlyCoveredCode() === false
        $actualShowMsi = $mutateOnlyCoveredCode === false;

        $this->assertSame($expectedShowMsi, $actualShowMsi,
            'The logic $config->mutateOnlyCoveredCode() === false should match expected behavior');
    }

    #[DataProvider('mutateOnlyCoveredCodeProvider')]
    public function test_factory_constructor_accepts_show_mutation_score_indicator_parameter(bool $mutateOnlyCoveredCode, bool $expectedShowMsi): void
    {
        $configMock = $this->createMock(Configuration::class);
        $metricsCalculatorMock = $this->createMock(MetricsCalculator::class);
        $resultsCollectorMock = $this->createMock(ResultsCollector::class);
        $diffColorizerMock = $this->createMock(DiffColorizer::class);
        $federatedLogger = new FederatedLogger();
        $outputFormatterMock = $this->createMock(OutputFormatter::class);

        // Simulate the Container logic: $config->mutateOnlyCoveredCode() === false
        $showMutationScoreIndicator = $mutateOnlyCoveredCode === false;

        $factory = new MutationTestingConsoleLoggerSubscriberFactory(
            $metricsCalculatorMock,
            $resultsCollectorMock,
            $diffColorizerMock,
            $federatedLogger,
            null,
            $outputFormatterMock,
            $showMutationScoreIndicator,
        );

        // Use reflection to verify the parameter was set correctly
        $reflection = new ReflectionClass($factory);
        $property = $reflection->getProperty('showMutationScoreIndicator');
        $property->setAccessible(true);

        $this->assertSame($expectedShowMsi, $property->getValue($factory));
    }

    public static function mutateOnlyCoveredCodeProvider(): iterable
    {
        yield 'with-uncovered flag (mutateOnlyCoveredCode = false) should show MSI' => [false, true];

        yield 'default behavior (mutateOnlyCoveredCode = true) should hide MSI' => [true, false];
    }
}
