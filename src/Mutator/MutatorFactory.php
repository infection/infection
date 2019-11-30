<?php

declare(strict_types=1);

namespace Infection\Mutator;

use Infection\Mutator\Util\Mutator;
use Infection\Mutator\Util\MutatorConfig;
use Infection\Mutator\Util\MutatorProfile;
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
        $mutators = [];

        // First parse the profiles
        foreach ($mutatorSettings as $mutatorOrProfile => $setting) {
            if (!array_key_exists($mutatorOrProfile, MutatorProfile::MUTATOR_PROFILE_LIST)) {
                continue;
            }

            $this->registerFromProfile($mutatorOrProfile, $setting, $mutators);
        }

        // First parse the profiles
        foreach ($mutatorSettings as $mutatorOrProfile => $setting) {
            if (!array_key_exists($mutatorOrProfile, MutatorProfile::FULL_MUTATOR_LIST)) {
                continue;
            }

            $this->registerFromName($mutatorOrProfile, $setting, $mutators);
        }

        return $this->createFromNames($mutators);
    }

    /**
     * @param array<string, string>|bool           $settings
     * @param array<string, array<string, string>> $mutators
     */
    private function registerFromProfile(
        string $profile,
        $settings,
        array &$mutators
    ): void
    {
        foreach (MutatorProfile::MUTATOR_PROFILE_LIST[$profile] as $mutatorOrProfile) {
            /** @var string $mutatorOrProfile */

            // A profile may refer to another collection of profiles
            if (array_key_exists($mutatorOrProfile, MutatorProfile::MUTATOR_PROFILE_LIST)) {
                $this->registerFromProfile($mutatorOrProfile, $settings, $mutators);

                continue;
            }

            if (class_exists($mutatorOrProfile)) {
                $this->registerFromClass($mutatorOrProfile, $settings, $mutators);

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
    private function registerFromName(
        string $mutator,
        $settings,
        array &$mutators
    ): void
    {
        if (!array_key_exists($mutator, MutatorProfile::FULL_MUTATOR_LIST)) {
            throw new InvalidArgumentException(sprintf(
                'The "%s" mutator/profile was not recognized.',
                $mutator
            ));
        }

        $this->registerFromClass(
            MutatorProfile::FULL_MUTATOR_LIST[$mutator],
            $settings,
            $mutators
        );
    }

    /**
     * @param array<string, string>|bool|stdClass  $settings
     * @param array<string, array<string, string>> $mutators
     */
    private function registerFromClass(
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
    private function createFromNames(array $mutatorNames): array
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
