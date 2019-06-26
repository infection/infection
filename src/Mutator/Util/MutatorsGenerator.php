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

namespace Infection\Mutator\Util;

use Infection\Config\Exception\InvalidConfigException;

/**
 * @internal
 */
final class MutatorsGenerator
{
    /**
     * @var array
     */
    private $mutatorSettings;

    /**
     * @var array
     */
    private $mutatorList = [];

    public function __construct(array $mutatorSettings = [])
    {
        $this->mutatorSettings = $mutatorSettings;
    }

    /**
     * Will create an array of mutators, keyed by their name
     *
     * @throws InvalidConfigException
     *
     * @return Mutator[]
     */
    public function generate(): array
    {
        if (empty($this->mutatorSettings)) {
            $this->mutatorSettings = [
                '@default' => true,
            ];
        }
        $this->mutatorList = [];

        foreach ($this->mutatorSettings as $mutatorOrProfile => $setting) {
            if (\array_key_exists($mutatorOrProfile, MutatorProfile::MUTATOR_PROFILE_LIST)) {
                $this->registerFromProfile($mutatorOrProfile, $setting);

                continue;
            }

            if (class_exists($mutatorOrProfile)) {
                $this->registerFromClass($mutatorOrProfile, $setting);

                continue;
            }

            $this->registerFromName($mutatorOrProfile, $setting);
        }

        return $this->createFromList($this->mutatorList);
    }

    /**
     * @param array|bool $setting
     *
     * @throws InvalidConfigException
     */
    private function registerFromProfile(string $profile, $setting): void
    {
        $mutators = MutatorProfile::MUTATOR_PROFILE_LIST[$profile];

        foreach ($mutators as $mutatorOrProfile) {
            if (\array_key_exists($mutatorOrProfile, MutatorProfile::MUTATOR_PROFILE_LIST)) {
                $this->registerFromProfile($mutatorOrProfile, $setting);

                continue;
            }

            if (class_exists($mutatorOrProfile)) {
                $this->registerFromClass($mutatorOrProfile, $setting);

                continue;
            }

            throw InvalidConfigException::invalidProfile($profile, $mutatorOrProfile);
        }
    }

    /**
     * @param array|bool|\stdClass $setting
     */
    private function registerFromClass(string $mutator, $setting): void
    {
        if ($setting === false) {
            $this->mutatorList[$mutator] = false;
        } elseif ($setting === true) {
            if (!\array_key_exists($mutator, $this->mutatorList)) {
                $this->mutatorList[$mutator] = [];
            }
        } elseif (!\array_key_exists($mutator, $this->mutatorList) || !$this->mutatorList[$mutator]) {
            $this->mutatorList[$mutator] = (array) $setting;
        }
    }

    /**
     * @param array|bool $setting
     *
     * @throws InvalidConfigException
     */
    private function registerFromName(string $mutator, $setting): void
    {
        if (!\array_key_exists($mutator, MutatorProfile::FULL_MUTATOR_LIST)) {
            throw InvalidConfigException::invalidMutator($mutator);
        }

        $this->registerFromClass(MutatorProfile::FULL_MUTATOR_LIST[$mutator], $setting);
    }

    /**
     * @return Mutator[]
     */
    private function createFromList(array $mutators): array
    {
        $mutatorList = [];

        foreach ($mutators as $mutator => $setting) {
            if ($setting !== false) {
                \assert(\is_string($mutator));
                $mutatorList[$mutator::getName()] = new $mutator(
                    new MutatorConfig($setting)
                );
            }
        }

        return $mutatorList;
    }
}
