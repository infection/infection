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

namespace Infection\Tests\Configuration\Options\Handler;

use Infection\Configuration\Options\Handler\ScalarOrObjectHandler;
use Infection\Configuration\Options\InfectionOptions;
use Infection\Configuration\Options\MutatorConfigOptions;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScalarOrObjectHandler::class)]
final class ScalarOrObjectHandlerTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = SerializerBuilder::create()
            ->setPropertyNamingStrategy(new IdenticalPropertyNamingStrategy())
            ->configureHandlers(static function (HandlerRegistryInterface $registry): void {
                $registry->registerSubscribingHandler(new ScalarOrObjectHandler());
            })
            ->build();
    }

    public function test_deserialize_threads_as_integer(): void
    {
        $json = <<<'JSON'
            {
                "source": {
                    "directories": ["src"]
                },
                "threads": 4
            }
            JSON;

        $result = $this->serializer->deserialize(
            $json,
            InfectionOptions::class,
            'json',
        );

        $this->assertSame(4, $result->threads);
    }

    public function test_deserialize_threads_as_max(): void
    {
        $json = <<<'JSON'
            {
                "source": {
                    "directories": ["src"]
                },
                "threads": "max"
            }
            JSON;

        $result = $this->serializer->deserialize(
            $json,
            InfectionOptions::class,
            'json',
        );

        $this->assertSame('max', $result->threads);
    }

    public function test_deserialize_mutator_config_options(): void
    {
        $json = <<<'JSON'
            {
                "ignore": ["path/to/ignore"],
                "ignoreSourceCodeByRegex": [".*test.*"],
                "settings": {"in_array": true}
            }
            JSON;

        $result = $this->serializer->deserialize(
            $json,
            MutatorConfigOptions::class,
            'json',
        );

        $this->assertInstanceOf(MutatorConfigOptions::class, $result);
        $this->assertSame(['path/to/ignore'], $result->ignore);
        $this->assertSame(['.*test.*'], $result->ignoreSourceCodeByRegex);
        $this->assertSame(['in_array' => true], $result->settings);
    }
}
