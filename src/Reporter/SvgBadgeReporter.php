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

namespace Infection\Reporter;

use function floor;
use Infection\Metrics\MetricsCalculator;
use function sprintf;

/**
 * Writes a static, shields.io-like badge with the Mutation Score Indicator. Unlike the
 * Stryker dashboard badge, it requires no third-party service, so it can be served from
 * a private repository or used as a GitLab badge, e.g. from a CI job artifact.
 *
 * @internal
 */
final readonly class SvgBadgeReporter implements LineMutationTestingResultsReporter
{
    // Same thresholds and colors as the MSI printed by ShowMetricsReporter.
    private const int LOW_QUALITY_THRESHOLD = 50;

    private const int MEDIUM_QUALITY_THRESHOLD = 90;

    private const string LOW_QUALITY_COLOR = '#e05d44';

    private const string MEDIUM_QUALITY_COLOR = '#dfb317';

    private const string HIGH_QUALITY_COLOR = '#4c1';

    private const string TEMPLATE = <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg" width="140" height="20" role="img" aria-label="mutation score: %1$s">
          <title>mutation score: %1$s</title>
          <clipPath id="r"><rect width="140" height="20" rx="3" fill="#fff"/></clipPath>
          <g clip-path="url(#r)">
            <rect width="96" height="20" fill="#555"/>
            <rect x="96" width="44" height="20" fill="%2$s"/>
          </g>
          <g fill="#fff" text-anchor="middle" font-family="Verdana,Geneva,DejaVu Sans,sans-serif" font-size="11">
            <text x="48" y="15" fill="#010101" fill-opacity=".3">mutation score</text>
            <text x="48" y="14">mutation score</text>
            <text x="118" y="15" fill="#010101" fill-opacity=".3">%1$s</text>
            <text x="118" y="14">%1$s</text>
          </g>
        </svg>
        SVG;

    public function __construct(
        private MetricsCalculator $metricsCalculator,
    ) {
    }

    /**
     * @return array{0: string}
     */
    public function getLines(): array
    {
        // Rounded down like the MSI printed in the console.
        $msi = floor($this->metricsCalculator->getMutationScoreIndicator());

        return [
            sprintf(
                self::TEMPLATE,
                $msi . '%',
                self::getColor($msi),
            ),
        ];
    }

    private static function getColor(float $msi): string
    {
        if ($msi < self::LOW_QUALITY_THRESHOLD) {
            return self::LOW_QUALITY_COLOR;
        }

        if ($msi < self::MEDIUM_QUALITY_THRESHOLD) {
            return self::MEDIUM_QUALITY_COLOR;
        }

        return self::HIGH_QUALITY_COLOR;
    }
}
