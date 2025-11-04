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

namespace Infection\Tests\Configuration\ConfigurationFactory;

use Infection\Configuration\Configuration;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Configuration\Entry\LogsBuilder;
use Infection\Tests\Configuration\Schema\SchemaConfigurationBuilder;

final class ConfigurationFactoryScenario
{
    public function __construct(
        public bool $ciDetected,
        public bool $githubActionsDetected,
        public SchemaConfigurationBuilder $schemaBuilder,
        public ConfigurationFactoryInputBuilder $inputBuilder,
        public Configuration $expected,
    ) {
    }

    public static function create(
        bool $ciDetected,
        bool $githubActionsDetected,
        SchemaConfigurationBuilder $schemaBuilder,
        ConfigurationFactoryInputBuilder $inputBuilder,
        Configuration $expected,
    ): self {
        return new self(
            $ciDetected,
            $githubActionsDetected,
            $schemaBuilder,
            $inputBuilder,
            $expected,
        );
    }

    public function withCiDetected(bool $ciDetected): self
    {
        $clone = clone $this;
        $clone->ciDetected = $ciDetected;

        return $clone;
    }

    public function withGithubActionsDetected(bool $githubActionsDetected): self
    {
        $clone = clone $this;
        $clone->githubActionsDetected = $githubActionsDetected;

        return $clone;
    }

    public function withSchema(SchemaConfigurationBuilder $schemaBuilder): self
    {
        $clone = clone $this;
        $clone->schemaBuilder = $schemaBuilder;

        return $clone;
    }

    public function withInput(ConfigurationFactoryInputBuilder $builder): self
    {
        $clone = clone $this;
        $clone->inputBuilder = $builder;

        return $clone;
    }

    public function withExpected(Configuration $expected): self
    {
        $clone = clone $this;
        $clone->expected = $expected;

        return $clone;
    }

    public function forValueForTextLogFilePath(
        ?string $textFileLogPathInConfig,
        ?string $textFileLogPathFromCliOption,
        ?string $expectedTextFileLogPath,
    ): ConfigurationFactoryScenario {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withLogs(
                        LogsBuilder::withMinimalTestData()
                            ->withTextLogFilePath($textFileLogPathInConfig)
                            ->build(),
                    ),
            )
            ->withInput(
                $this->inputBuilder
                    ->withTextLogFilePath($textFileLogPathFromCliOption)
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withLogs(
                        LogsBuilder::from($this->expected->getLogs())
                            ->withTextLogFilePath($expectedTextFileLogPath)
                            ->build(),
                    )
                    ->build(),
            );
    }
}
