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

namespace Infection\Telemetry\Attribute;

use Infection\Configuration\Configuration;
use Infection\Framework\InfectionVersion;
use OutOfBoundsException;
use Phar;
use Symfony\Component\Filesystem\Path;

/**
 * @phpstan-type Attribute = bool|int|float|string
 * @phpstan-type Attributes = array<non-empty-string, Attribute>
 *
 * @see https://opentelemetry.io/docs/specs/semconv/general/naming/
 *
 * @internal
 */
final readonly class RunSpanAttributesProvider
{
    public function __construct(
        private Configuration $configuration,
        private InfectionVersion $infectionVersion,
    ) {
    }

    /**
     * @throws OutOfBoundsException
     *
     * @return Attributes
     */
    public function provide(): array
    {
        return [
            'infection.project.dir' => $this->configuration->projectDirectory,
            'infection.config.path' => $this->getConfigurationPath(),
            'infection.version' => $this->infectionVersion->prettyVersion(),
            'infection.distribution' => self::getDistribution(),
            'infection.thread.count' => $this->configuration->threadCount,
            'infection.initial_tests.skipped' => $this->configuration->skipInitialTests,
            'infection.initial_static_analysis.skipped' => !$this->configuration->isStaticAnalysisEnabled(),
        ];
    }

    private function getConfigurationPath(): string
    {
        $projectDirectory = Path::canonicalize($this->configuration->projectDirectory);
        $configurationPathname = Path::canonicalize($this->configuration->configurationPathname);

        if (Path::isBasePath($projectDirectory, $configurationPathname)) {
            return Path::makeRelative($configurationPathname, $projectDirectory);
        }

        return $configurationPathname;
    }

    private static function getDistribution(): string
    {
        return Phar::running(false) === ''
            ? 'source'
            : 'phar';
    }
}
