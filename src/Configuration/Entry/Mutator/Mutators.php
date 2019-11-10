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

namespace Infection\Configuration\Entry\Mutator;

use function array_keys;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class Mutators
{
    public const PROFILES = [
        '@arithmetic',
        '@boolean',
        '@cast',
        '@conditional_boundary',
        '@conditional_negotiation',
        '@function_signature',
        '@number',
        '@operator',
        '@regex',
        '@removal',
        '@return_value',
        '@sort',
        '@zero_iteration',
        '@default',
    ];

    private $profiles;
    private $trueValue;
    private $arrayItemRemoval;
    private $bcMath;
    private $mbString;
    private $genericMutators;

    /**
     * @param array<string,bool> $profiles
     */
    public function __construct(
        array $profiles,
        ?MutatorConfiguration $trueValue,
        ?MutatorConfiguration $arrayItemRemoval,
        ?MutatorConfiguration $bcMath,
        ?MutatorConfiguration $mbString,
        GenericMutator ...$genericMutators
    ) {
        Assert::allOneOf(array_keys($profiles), self::PROFILES);
        Assert::allBoolean($profiles);

        $this->profiles = $profiles;
        $this->trueValue = $trueValue;
        $this->arrayItemRemoval = $arrayItemRemoval;
        $this->bcMath = $bcMath;
        $this->mbString = $mbString;
        $this->genericMutators = $genericMutators;
    }

    public function setDefaultProfile(): void
    {
        $this->profiles['default'] = true;
    }

    /**
     * @return array<string,bool>
     */
    public function getProfiles(): array
    {
        return $this->profiles;
    }

    public function getTrueValue(): ?MutatorConfiguration
    {
        return $this->trueValue;
    }

    public function getArrayItemRemoval(): ?MutatorConfiguration
    {
        return $this->arrayItemRemoval;
    }

    public function getBcMath(): ?MutatorConfiguration
    {
        return $this->bcMath;
    }

    public function getMbString(): ?MutatorConfiguration
    {
        return $this->mbString;
    }

    /**
     * @return GenericMutator[]
     */
    public function getGenericMutators(): array
    {
        return $this->genericMutators;
    }
}
