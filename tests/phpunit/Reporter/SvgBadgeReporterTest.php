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

namespace Infection\Tests\Reporter;

use Infection\Metrics\MetricsCalculator;
use Infection\Reporter\SvgBadgeReporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

#[CoversClass(SvgBadgeReporter::class)]
final class SvgBadgeReporterTest extends TestCase
{
    use CreateMetricsCalculator;

    public function test_it_reports_the_msi_as_an_svg_badge(): void
    {
        $reporter = new SvgBadgeReporter(self::createCompleteMetricsCalculator());

        $this->assertSame(
            [
                <<<'SVG'
                    <svg xmlns="http://www.w3.org/2000/svg" width="140" height="20" role="img" aria-label="mutation score: 69%">
                      <title>mutation score: 69%</title>
                      <clipPath id="r"><rect width="140" height="20" rx="3" fill="#fff"/></clipPath>
                      <g clip-path="url(#r)">
                        <rect width="96" height="20" fill="#555"/>
                        <rect x="96" width="44" height="20" fill="#dfb317"/>
                      </g>
                      <g fill="#fff" text-anchor="middle" font-family="Verdana,Geneva,DejaVu Sans,sans-serif" font-size="11">
                        <text x="48" y="15" fill="#010101" fill-opacity=".3">mutation score</text>
                        <text x="48" y="14">mutation score</text>
                        <text x="118" y="15" fill="#010101" fill-opacity=".3">69%</text>
                        <text x="118" y="14">69%</text>
                      </g>
                    </svg>
                    SVG,
            ],
            $reporter->getLines(),
        );
    }

    #[DataProvider('msiProvider')]
    public function test_it_shows_the_rounded_down_msi_in_a_color_matching_its_quality(
        float $msi,
        string $expectedScore,
        string $expectedColor,
    ): void {
        $metricsCalculator = $this->createStub(MetricsCalculator::class);
        $metricsCalculator
            ->method('getMutationScoreIndicator')
            ->willReturn($msi)
        ;

        $svg = (new SvgBadgeReporter($metricsCalculator))->getLines()[0];

        // Also checks that the badge is well-formed XML.
        $badge = new SimpleXMLElement($svg);

        $this->assertSame('mutation score: ' . $expectedScore, (string) $badge['aria-label']);
        $this->assertStringContainsString('<text x="118" y="14">' . $expectedScore . '</text>', $svg);
        $this->assertStringContainsString('<rect x="96" width="44" height="20" fill="' . $expectedColor . '"/>', $svg);
    }

    public static function msiProvider(): iterable
    {
        yield 'no mutation killed' => [0., '0%', '#e05d44'];

        yield 'just below the low threshold' => [49.99, '49%', '#e05d44'];

        yield 'low threshold' => [50., '50%', '#dfb317'];

        yield 'just below the medium threshold' => [89.99, '89%', '#dfb317'];

        yield 'medium threshold' => [90., '90%', '#4c1'];

        yield 'all mutations killed' => [100., '100%', '#4c1'];
    }
}
