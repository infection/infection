<?php

declare(strict_types=1);

namespace Infection\Mutator;

use Infection\Mutator\Util\Mutator;
use Infection\Mutator\Util\MutatorConfig;
use InvalidArgumentException;
use stdClass;
use function array_key_exists;
use function class_exists;
use function sprintf;

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
    ): void
    {
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
    ): void
    {
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
    ): void
    {
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

        foreach ($mutatorNames as $mutator => $settings) {
            $mutators[$mutator::getName()] = new $mutator(
                new MutatorConfig($settings)
            );
        }

        return $mutators;
    }
}
