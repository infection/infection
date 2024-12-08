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
use function array_unique;
use function array_values;
use function class_exists;
use function in_array;
use InvalidArgumentException;
use function is_subclass_of;
use PhpParser\Node;
use function sprintf;
use stdClass;

/**
 * @internal
 */
final class MutatorResolver
{
    private const IGNORE_SETTING = 'ignore';
    private const IGNORE_SOURCE_CODE_BY_REGEX_SETTING = 'ignoreSourceCodeByRegex';

    private const GLOBAL_IGNORE_SETTING = 'global-ignore';
    private const GLOBAL_IGNORE_SOURCE_CODE_BY_REGEX_SETTING = 'global-ignoreSourceCodeByRegex';

    /**
     * Resolves the given hashmap of enabled, disabled or configured mutators
     * and profiles into a hashmap of mutator raw settings by their mutator
     * class name.
     *
     * @param array<string, bool|stdClass> $mutatorSettings
     *
     * @return array<class-string<Mutator<Node>&ConfigurableMutator<Node>>, mixed[]>
     */
    public function resolve(array $mutatorSettings): array
    {
        $mutators = [];

        $globalSettings = [];

        foreach ($mutatorSettings as $mutatorOrProfileOrGlobalSettingKey => $setting) {
            if ($mutatorOrProfileOrGlobalSettingKey === self::GLOBAL_IGNORE_SETTING) {
                /** @var string[] $globalSetting */
                $globalSetting = $setting;

                $globalSettings[self::IGNORE_SETTING] = $globalSetting;
                unset($mutatorSettings[self::GLOBAL_IGNORE_SETTING]);
            }

            if ($mutatorOrProfileOrGlobalSettingKey === self::GLOBAL_IGNORE_SOURCE_CODE_BY_REGEX_SETTING) {
                /** @var string[] $globalSetting */
                $globalSetting = $setting;

                $globalSettings[self::IGNORE_SOURCE_CODE_BY_REGEX_SETTING] = array_values(array_unique($globalSetting));
                unset($mutatorSettings[self::GLOBAL_IGNORE_SOURCE_CODE_BY_REGEX_SETTING]);
            }
        }

        foreach ($mutatorSettings as $mutatorOrProfile => $setting) {
            $resolvedSettings = self::resolveSettings($setting, $globalSettings);

            if (array_key_exists($mutatorOrProfile, ProfileList::ALL_PROFILES)) {
                self::registerFromProfile(
                    $mutatorOrProfile,
                    $resolvedSettings,
                    $mutators,
                );

                continue;
            }

            if (array_key_exists($mutatorOrProfile, ProfileList::ALL_MUTATORS)) {
                self::registerFromName(
                    $mutatorOrProfile,
                    $resolvedSettings,
                    $mutators,
                );

                continue;
            }

            if (self::isValidMutator($mutatorOrProfile)) {
                self::registerFromClass(
                    $mutatorOrProfile,
                    $resolvedSettings,
                    $mutators,
                );

                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'The profile or mutator "%s" was not recognized.',
                $mutatorOrProfile,
            ));
        }

        return $mutators;
    }

    public static function isValidMutator(string $mutatorClass): bool
    {
        return class_exists($mutatorClass, true) && is_subclass_of($mutatorClass, Mutator::class);
    }

    /**
     * @param array<string, string[]> $globalSettings
     *
     * @return array<string, mixed[]>|bool
     */
    private static function resolveSettings(bool|stdClass|array $settings, array $globalSettings): array|bool
    {
        if ($settings === false) {
            return false;
        }

        if ($settings === true) {
            return $globalSettings;
        }

        if ($globalSettings === []) {
            return (array) $settings;
        }

        $resultSettings = array_merge_recursive($globalSettings, (array) $settings);

        foreach ($resultSettings as $key => &$settingValues) {
            if (in_array($key, [self::IGNORE_SETTING, self::IGNORE_SOURCE_CODE_BY_REGEX_SETTING], true)) {
                $settingValues = array_values(array_unique($settingValues));
            }
        }
        unset($settingValues);

        return $resultSettings;
    }

    /**
     * @param array<string, mixed>|bool $settings
     * @param array<string, array<array-key, string>> $mutators
     */
    private static function registerFromProfile(
        string $profile,
        array|bool $settings,
        array &$mutators,
    ): void {
        foreach (ProfileList::ALL_PROFILES[$profile] as $mutatorOrProfile) {
            // A profile may refer to another collection of profiles
            if (array_key_exists($mutatorOrProfile, ProfileList::ALL_PROFILES)) {
                self::registerFromProfile(
                    $mutatorOrProfile,
                    $settings,
                    $mutators,
                );

                continue;
            }

            if (class_exists($mutatorOrProfile, true)) {
                self::registerFromClass(
                    $mutatorOrProfile,
                    $settings,
                    $mutators,
                );

                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'The "%s" profile contains the "%s" mutator which was '
                . 'not recognized.',
                $profile,
                $mutatorOrProfile,
            ));
        }
    }

    /**
     * @param array<string, mixed[]>|bool $settings
     * @param array<string, array<string, string>> $mutators
     */
    private static function registerFromName(
        string $mutator,
        array|bool $settings,
        array &$mutators,
    ): void {
        if (!array_key_exists($mutator, ProfileList::ALL_MUTATORS)) {
            throw new InvalidArgumentException(sprintf(
                'The "%s" mutator/profile was not recognized.',
                $mutator,
            ));
        }

        self::registerFromClass(
            ProfileList::ALL_MUTATORS[$mutator],
            $settings,
            $mutators,
        );
    }

    /**
     * @param array<string, string[]>|bool $settings
     * @param array<string, array<string, string>> $mutators
     */
    private static function registerFromClass(
        string $mutatorClassName,
        array|bool $settings,
        array &$mutators,
    ): void {
        if ($settings === false) {
            unset($mutators[$mutatorClassName]);

            return;
        }

        if ($settings === true || $settings === []) {
            $mutators[$mutatorClassName] ??= [];

            return;
        }

        if (!array_key_exists($mutatorClassName, $mutators)) {
            $mutators[$mutatorClassName] = $settings;

            return;
        }

        $mutators[$mutatorClassName] = array_merge_recursive($settings, $mutators[$mutatorClassName]);
    }
}
