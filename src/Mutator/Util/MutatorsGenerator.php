<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Util;

use Infection\Config\Exception\InvalidConfigException;

class MutatorsGenerator
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
            if (array_key_exists($mutatorOrProfile, MutatorProfile::MUTATOR_PROFILE_LIST)) {
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
     * @param string $profile
     * @param array|bool $setting
     *
     * @throws InvalidConfigException
     */
    private function registerFromProfile(string $profile, $setting)
    {
        $mutators = MutatorProfile::MUTATOR_PROFILE_LIST[$profile];

        foreach ($mutators as $mutatorOrProfile) {
            if (array_key_exists($mutatorOrProfile, MutatorProfile::MUTATOR_PROFILE_LIST)) {
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
     * @param string $mutator
     * @param array|bool $setting
     */
    private function registerFromClass(string $mutator, $setting)
    {
        if ($setting === false) {
            $this->mutatorList[$mutator] = false;
        } elseif ($setting === true) {
            if (!array_key_exists($mutator, $this->mutatorList)) {
                $this->mutatorList[$mutator] = [];
            }
        } elseif (!array_key_exists($mutator, $this->mutatorList) || $this->mutatorList[$mutator] === []) {
            $this->mutatorList[$mutator] = (array) $setting;
        }
    }

    /**
     * @param string $mutator
     * @param array|bool $setting
     *
     * @throws InvalidConfigException
     */
    private function registerFromName(string $mutator, $setting)
    {
        if (!array_key_exists($mutator, MutatorProfile::FULL_MUTATOR_LIST)) {
            throw InvalidConfigException::invalidMutator($mutator);
        }

        $this->registerFromClass(MutatorProfile::FULL_MUTATOR_LIST[$mutator], $setting);
    }

    /**
     * @param array $mutators
     *
     * @return Mutator[]
     */
    private function createFromList(array $mutators): array
    {
        $mutatorList = [];

        foreach ($mutators as $mutator => $setting) {
            if ($setting !== false) {
                $mutatorList[$mutator::getName()] = new $mutator(
                    new MutatorConfig($setting)
                );
            }
        }

        return $mutatorList;
    }
}
