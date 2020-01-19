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
use function class_exists;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\Boolean\TrueValueConfig;
use Infection\Mutator\Extensions\BCMath;
use Infection\Mutator\Extensions\BCMathConfig;
use Infection\Mutator\Extensions\MBString;
use Infection\Mutator\Extensions\MBStringConfig;
use Infection\Mutator\Removal\ArrayItemRemoval;
use Infection\Mutator\Removal\ArrayItemRemovalConfig;
use Infection\Mutator\Util\MutatorConfig;
use InvalidArgumentException;
use function sprintf;
use stdClass;

/**
 * @internal
 */
final class MutatorFactory
{
    /**
     * @param array<string, bool|array<string, string>> $mutatorSettings
     *
     * @return array<string, Mutator>
     */
    public function create(array $mutatorSettings): array
    {
        return self::createFromNames(
            self::retrieveMutatorNames($mutatorSettings)
        );
    }

    /**
     * @param array<string, bool|array<string, string>> $mutatorSettings
     *
     * @return array<string, array<string, string>>
     */
    private static function retrieveMutatorNames(array $mutatorSettings): array
    {
        $mutators = [];

        foreach ($mutatorSettings as $mutatorOrProfile => $setting) {
            if (array_key_exists($mutatorOrProfile, ProfileList::ALL_PROFILES)) {
                self::registerFromProfile($mutatorOrProfile, $setting, $mutators);

                continue;
            }

            if (array_key_exists($mutatorOrProfile, ProfileList::ALL_MUTATORS)) {
                self::registerFromName($mutatorOrProfile, $setting, $mutators);

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
     * @param array<string, string>|bool           $settings
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
                self::registerFromProfile($mutatorOrProfile, $settings, $mutators);

                continue;
            }

            if (class_exists($mutatorOrProfile, true)) {
                self::registerFromClass($mutatorOrProfile, $settings, $mutators);

                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'The "%s" profile contains the "%s" mutator which was not recognized.',
                $profile,
                $mutatorOrProfile
            ));
        }
    }

    /**
     * @param array<string, string>|bool           $settings
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
     * @param array<string, string>|bool|stdClass  $settings
     * @param array<string, array<string, string>> $mutators
     */
    private static function registerFromClass(
        string $mutator,
        $settings,
        array &$mutators
    ): void {
        if ($settings === false) {
            unset($mutators[$mutator]);
        } else {
            $mutators[$mutator] = $settings === true ? [] : (array) $settings;
        }
    }

    /**
     * @param array<string, array<string, string>> $mutatorNames
     *
     * @return array<string, Mutator>
     */
    private static function createFromNames(array $mutatorNames): array
    {
        $mutators = [];

        foreach ($mutatorNames as $mutatorClass => $config) {
            /** @var string[] $settings */
            $settings = $config['settings'] ?? [];
            /** @var string[] $ignored */
            $ignored = $config['ignore'] ?? [];

            switch ($mutatorClass) {
                case BCMath::class:
                    $mutator = new BCMath(new BCMathConfig($settings));
                    break;

                case MBString::class:
                    $mutator = new MBString(new MBStringConfig($settings));

                    break;

                case TrueValue::class:
                    $mutator = new TrueValue(new TrueValueConfig($settings));

                    break;

                case ArrayItemRemoval::class:
                    $mutator = new ArrayItemRemoval(new ArrayItemRemovalConfig($settings));

                    break;

                default:
                    /** @var Mutator $mutator */
                    $mutator = new $mutatorClass();
            }

            $mutators[$mutator->getName()] = new IgnoreMutator(
                new IgnoreConfig($ignored),
                $mutator
            );
        }

        return $mutators;
    }
}
