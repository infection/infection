<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

use function array_key_exists;
use function array_merge_recursive;
use function array_unique;
use function array_values;
use function class_exists;
use function in_array;
use InvalidArgumentException;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use stdClass;
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
     * @return array<class-string<Mutator<\PhpParser\Node>&ConfigurableMutator<\PhpParser\Node>>, mixed[]>
     */
    public function resolve(array $mutatorSettings) : array
    {
        $mutators = [];
        $globalSettings = [];
        foreach ($mutatorSettings as $mutatorOrProfileOrGlobalSettingKey => $setting) {
            if ($mutatorOrProfileOrGlobalSettingKey === self::GLOBAL_IGNORE_SETTING) {
                $globalSetting = $setting;
                $globalSettings[self::IGNORE_SETTING] = $globalSetting;
                unset($mutatorSettings[self::GLOBAL_IGNORE_SETTING]);
            }
            if ($mutatorOrProfileOrGlobalSettingKey === self::GLOBAL_IGNORE_SOURCE_CODE_BY_REGEX_SETTING) {
                $globalSetting = $setting;
                $globalSettings[self::IGNORE_SOURCE_CODE_BY_REGEX_SETTING] = array_values(array_unique($globalSetting));
                unset($mutatorSettings[self::GLOBAL_IGNORE_SOURCE_CODE_BY_REGEX_SETTING]);
            }
        }
        foreach ($mutatorSettings as $mutatorOrProfile => $setting) {
            $resolvedSettings = self::resolveSettings($setting, $globalSettings);
            if (array_key_exists($mutatorOrProfile, ProfileList::ALL_PROFILES)) {
                self::registerFromProfile($mutatorOrProfile, $resolvedSettings, $mutators);
                continue;
            }
            if (array_key_exists($mutatorOrProfile, ProfileList::ALL_MUTATORS)) {
                self::registerFromName($mutatorOrProfile, $resolvedSettings, $mutators);
                continue;
            }
            throw new InvalidArgumentException(sprintf('The profile or mutator "%s" was not recognized.', $mutatorOrProfile));
        }
        return $mutators;
    }
    private static function resolveSettings(bool|stdClass|array $settings, array $globalSettings) : array|bool
    {
        if ($settings === \false) {
            return \false;
        }
        if ($settings === \true) {
            return $globalSettings;
        }
        if ($globalSettings === []) {
            return (array) $settings;
        }
        $resultSettings = array_merge_recursive($globalSettings, (array) $settings);
        foreach ($resultSettings as $key => &$settingValues) {
            if (in_array($key, [self::IGNORE_SETTING, self::IGNORE_SOURCE_CODE_BY_REGEX_SETTING], \true)) {
                $settingValues = array_values(array_unique($settingValues));
            }
        }
        unset($settingValues);
        return $resultSettings;
    }
    private static function registerFromProfile(string $profile, array|bool $settings, array &$mutators) : void
    {
        foreach (ProfileList::ALL_PROFILES[$profile] as $mutatorOrProfile) {
            if (array_key_exists($mutatorOrProfile, ProfileList::ALL_PROFILES)) {
                self::registerFromProfile($mutatorOrProfile, $settings, $mutators);
                continue;
            }
            if (class_exists($mutatorOrProfile, \true)) {
                self::registerFromClass($mutatorOrProfile, $settings, $mutators);
                continue;
            }
            throw new InvalidArgumentException(sprintf('The "%s" profile contains the "%s" mutator which was ' . 'not recognized.', $profile, $mutatorOrProfile));
        }
    }
    private static function registerFromName(string $mutator, array|bool $settings, array &$mutators) : void
    {
        if (!array_key_exists($mutator, ProfileList::ALL_MUTATORS)) {
            throw new InvalidArgumentException(sprintf('The "%s" mutator/profile was not recognized.', $mutator));
        }
        self::registerFromClass(ProfileList::ALL_MUTATORS[$mutator], $settings, $mutators);
    }
    private static function registerFromClass(string $mutatorClassName, array|bool $settings, array &$mutators) : void
    {
        if ($settings === \false) {
            unset($mutators[$mutatorClassName]);
            return;
        }
        if ($settings === \true || $settings === []) {
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
