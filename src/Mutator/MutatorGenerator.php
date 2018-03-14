<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator;

use Infection\Config\Exception\InvalidConfigException;

class MutatorGenerator
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
    public function create(): array
    {
        if (empty($this->mutatorSettings)) {
            $this->mutatorSettings = [
                '@default' => true,
            ];
        }
        $this->mutatorList = [];

        foreach ($this->mutatorSettings as $mutator => $setting) {
            if (array_key_exists($mutator, MutatorProfile::MUTATOR_PROFILE_LIST)) {
                $this->registerFromProfile($mutator, $setting);
                continue;
            }

            if (class_exists($mutator)) {
                $this->registerFromClass($mutator, $setting);
                continue;
            }
            $this->registerFromName($mutator, $setting);
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
        foreach ($mutators as $mutator) {
            if (array_key_exists($mutator, MutatorProfile::MUTATOR_PROFILE_LIST)) {
                $this->registerFromProfile($mutator, $setting);
                continue;
            }

            if (class_exists($mutator)) {
                $this->registerFromClass($mutator, $setting);
                continue;
            }

            throw InvalidConfigException::invalidProfile($profile, $mutator);
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
        if (array_key_exists($mutator, MutatorProfile::FULL_MUTATOR_LIST)) {
            $this->registerFromClass(MutatorProfile::FULL_MUTATOR_LIST[$mutator], $setting);
        } else {
            throw InvalidConfigException::invalidMutator($mutator);
        }
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
