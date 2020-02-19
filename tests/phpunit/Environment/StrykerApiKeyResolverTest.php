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

namespace Infection\Tests\Environment;

use Infection\Environment\CouldNotResolveStrykerApiKey;
use Infection\Environment\StrykerApiKeyResolver;
use PHPUnit\Framework\TestCase;
use stdClass;

final class StrykerApiKeyResolverTest extends TestCase
{
    public function test_resolve_throws_when_environment_is_empty_array(): void
    {
        $environment = [];

        $resolver = new StrykerApiKeyResolver();

        $this->expectException(CouldNotResolveStrykerApiKey::class);

        $resolver->resolve($environment);
    }

    public function test_resolve_throws_when_environment_does_not_contain_any_known_environment_variable_names(): void
    {
        $environment = [
            'API_KEY' => 'foo',
        ];

        $resolver = new StrykerApiKeyResolver();

        $this->expectException(CouldNotResolveStrykerApiKey::class);

        $resolver->resolve($environment);
    }

    public function test_resolve_throws_when_value_of_known_environment_variables_is_not_a_string(): void
    {
        $environment = [
            'API_KEY' => 'foo',
            'INFECTION_BADGE_API_KEY' => 9000,
            'STRYKER_DASHBOARD_API_KEY' => new stdClass(),
        ];

        $resolver = new StrykerApiKeyResolver();

        $this->expectException(CouldNotResolveStrykerApiKey::class);

        $resolver->resolve($environment);
    }

    public function test_resolve_returns_value_of_infection_badge_api_key_when_available(): void
    {
        $environment = [
            'API_KEY' => 'foo',
            'INFECTION_BADGE_API_KEY' => 'bar',
            'STRYKER_DASHBOARD_API_KEY' => 'baz',
        ];

        $resolver = new StrykerApiKeyResolver();

        $this->assertSame('bar', $resolver->resolve($environment));
    }

    public function test_resolve_returns_value_of_stryker_dashboard_api_key_when_available(): void
    {
        $environment = [
            'API_KEY' => 'foo',
            'STRYKER_DASHBOARD_API_KEY' => 'baz',
        ];

        $resolver = new StrykerApiKeyResolver();

        $this->assertSame('baz', $resolver->resolve($environment));
    }
}
