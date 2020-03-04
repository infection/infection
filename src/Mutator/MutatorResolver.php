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

namespace Infection\Mutator;

use function array_key_exists;
use function array_merge_recursive;
use function class_exists;
use function count;
use InvalidArgumentException;
use function Safe\sprintf;

/**
 * @internal
 */
final class MutatorResolver
{
    private const GLOBAL_IGNORE_SETTING = 'global-ignore';

    /**
     * Resolves the given hashmap of enabled, disabled or configured mutators
     * and profiles into a hashmap of mutator raw settings by their mutator
     * class name.
     *
     * @param array<string, bool|mixed[]> $mutatorSettings
     *
     * @return array<string, mixed[]>
     */
    public function resolve(array $mutatorSettings): array
    {
        $mutators = [];

        $globalSettings = [];

        foreach ($mutatorSettings as $mutatorOrProfileOrGlobalSettingKey => $setting) {
            if ($mutatorOrProfileOrGlobalSettingKey === self::GLOBAL_IGNORE_SETTING) {
                /** @var string[] $globalSetting */
                $globalSetting = $setting;

                $globalSettings = ['ignore' => $globalSetting];
                unset($mutatorSettings[self::GLOBAL_IGNORE_SETTING]);

                break;
            }
        }

        foreach ($mutatorSettings as $mutatorOrProfile => $setting) {
            $setting = self::resolveSettings($setting, $globalSettings);

            if (array_key_exists($mutatorOrProfile, ProfileList::ALL_PROFILES)) {
                self::registerFromProfile(
                    $mutatorOrProfile,
                    $setting,
                    $mutators
                );

                continue;
            }

            if (array_key_exists($mutatorOrProfile, ProfileList::ALL_MUTATORS)) {
                self::registerFromName(
                    $mutatorOrProfile,
                    $setting,
                    $mutators
                );

                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'The profile or mutator "%s" was not recognized.',
                $mutatorOrProfile
            ));
        }

        return $mutators;
    }

    /**
     * @param mixed[]|bool $settings
     * @param array<string, string[]> $globalSettings
     *
     * @return array<string, mixed[]>|bool
     */
    private static function resolveSettings($settings, array $globalSettings)
    {
        if ($settings === false || count($globalSettings) === 0) {
            return $settings;
        }

        if ($settings === true) {
            return $globalSettings;
        }

        return array_merge_recursive($globalSettings, (array) $settings);
    }

    /**
     * @param array<string, mixed[]>|bool $settings
     * @param array<string, array<string, string>> $mutators
     */
    private static function registerFromProfile(
        string $profile,
        $settings,
        array &$mutators
    ): void {
        foreach (ProfileList::ALL_PROFILES[$profile] as $mutatorOrProfile) {
            /** @var string $mutatorOrProfile */

            // A profile may refer to another collection of profiles
            if (array_key_exists($mutatorOrProfile, ProfileList::ALL_PROFILES)) {
                self::registerFromProfile(
                    $mutatorOrProfile,
                    $settings,
                    $mutators
                );

                continue;
            }

            if (class_exists($mutatorOrProfile, true)) {
                self::registerFromClass(
                    $mutatorOrProfile,
                    $settings,
                    $mutators
                );

                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'The "%s" profile contains the "%s" mutator which was '
                . 'not recognized.',
                $profile,
                $mutatorOrProfile
            ));
        }
    }

    /**
     * @param array<string, mixed[]>|bool $settings
     * @param array<string, array<string, string>> $mutators
     */
    private static function registerFromName(
        string $mutator,
        $settings,
        array &$mutators
    ): void {
        if (!array_key_exists($mutator, ProfileList::ALL_MUTATORS)) {
            throw new InvalidArgumentException(sprintf(
                'The "%s" mutator/profile was not recognized.',
                $mutator
            ));
        }

        self::registerFromClass(
            ProfileList::ALL_MUTATORS[$mutator],
            $settings,
            $mutators
        );
    }

    /**
     * @param array<string, string[]>|bool $settings
     * @param array<string, string[]> $mutators
     */
    private static function registerFromClass(
        string $mutatorClassName,
        $settings,
        array &$mutators
    ): void {
        if ($settings === false) {
            unset($mutators[$mutatorClassName]);
        } else {
            $mutators[$mutatorClassName] = (array) $settings;
        }
    }
}
