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

namespace Infection\Tests\Configuration\PositionalPathsClassifier;

use Exception;
use Infection\Configuration\ClassifiedPaths;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Tests\Configuration\Schema\SchemaConfigurationBuilder;
use InvalidArgumentException;
use LogicException;

final class Scenario
{
    public SchemaConfiguration $schema;

    /**
     * @param list<non-empty-string> $paths
     * @param list<non-empty-string> $sourceDirectories
     * @param list<non-empty-string> $existingFiles
     * @param list<non-empty-string> $existingDirectories
     */
    public function __construct(
        public array $paths,
        array $sourceDirectories,
        public array $existingFiles,
        public array $existingDirectories,
        public ClassifiedPaths|Exception $expected,
    ) {
        $this->schema = self::createSchema($sourceDirectories);
    }

    public static function empty(): self
    {
        return new self(
            paths: [],
            sourceDirectories: [],
            existingFiles: [],
            existingDirectories: [],
            expected: new ClassifiedPaths(
                sourcePaths: [],
                testPaths: [],
            ),
        );
    }

    /**
     * @param list<non-empty-string> $paths
     */
    public function withPaths(array $paths): self
    {
        $clone = clone $this;
        $clone->paths = $paths;

        return $clone;
    }

    /**
     * @param list<non-empty-string> $sourceDirectories
     */
    public function withSourceDirectories(array $sourceDirectories): self
    {
        $clone = clone $this;
        $clone->schema = self::createSchema($sourceDirectories);

        return $clone;
    }

    /**
     * @param list<non-empty-string> $existingFiles
     */
    public function withExistingFiles(array $existingFiles): self
    {
        $clone = clone $this;
        $clone->existingFiles = $existingFiles;

        return $clone;
    }

    /**
     * @param list<non-empty-string> $existingDirectories
     */
    public function withExistingDirectories(array $existingDirectories): self
    {
        $clone = clone $this;
        $clone->existingDirectories = $existingDirectories;

        return $clone;
    }

    public function withExpected(ClassifiedPaths|Exception $expected): self
    {
        $clone = clone $this;
        $clone->expected = $expected;

        return $clone;
    }

    public function expectedClassifiedPaths(): ClassifiedPaths
    {
        $expected = $this->expected;

        if (!$expected instanceof ClassifiedPaths) {
            throw new LogicException('Expected classified paths.');
        }

        return $expected;
    }

    public function expectedException(): InvalidArgumentException
    {
        $expected = $this->expected;

        if (!$expected instanceof InvalidArgumentException) {
            throw new LogicException('Expected invalid argument exception.');
        }

        return $expected;
    }

    /**
     * @param list<non-empty-string> $sourceDirectories
     */
    private static function createSchema(array $sourceDirectories): SchemaConfiguration
    {
        return SchemaConfigurationBuilder::withMinimalTestData()
            ->withPathname('/project/infection.json5')
            ->withSource(new Source($sourceDirectories, []))
            ->build();
    }
}
