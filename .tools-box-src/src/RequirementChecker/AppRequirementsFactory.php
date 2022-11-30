<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\RequirementChecker;

use function array_diff_key;
use function array_key_exists;
use Phar;
use function preg_match;
use function sprintf;
final class AppRequirementsFactory
{
    private const SELF_PACKAGE = '__APPLICATION__';
    public static function create(array $composerJsonDecodedContents, array $composerLockDecodedContents, ?int $compressionAlgorithm) : array
    {
        return self::configureExtensionRequirements(self::retrievePhpVersionRequirements([], $composerJsonDecodedContents, $composerLockDecodedContents), $composerJsonDecodedContents, $composerLockDecodedContents, $compressionAlgorithm);
    }
    private static function retrievePhpVersionRequirements(array $requirements, array $composerJsonContents, array $composerLockContents) : array
    {
        if ([] === $composerLockContents && isset($composerJsonContents['require']['php']) || isset($composerLockContents['platform']['php'])) {
            return self::retrievePlatformPhpRequirement($requirements, $composerJsonContents, $composerLockContents);
        }
        return self::retrievePackagesPhpRequirement($requirements, $composerLockContents);
    }
    private static function retrievePlatformPhpRequirement(array $requirements, array $composerJsonContents, array $composerLockContents) : array
    {
        $requiredPhpVersion = [] === $composerLockContents ? $composerJsonContents['require']['php'] : $composerLockContents['platform']['php'];
        $requirements[] = self::generatePhpCheckRequirement((string) $requiredPhpVersion, null);
        return $requirements;
    }
    private static function retrievePackagesPhpRequirement(array $requirements, array $composerLockContents) : array
    {
        $packages = $composerLockContents['packages'] ?? [];
        foreach ($packages as $packageInfo) {
            $requiredPhpVersion = $packageInfo['require']['php'] ?? null;
            if (null === $requiredPhpVersion) {
                continue;
            }
            $requirements[] = self::generatePhpCheckRequirement((string) $requiredPhpVersion, $packageInfo['name']);
        }
        return $requirements;
    }
    private static function configureExtensionRequirements(array $requirements, array $composerJsonContents, array $composerLockContents, ?int $compressionAlgorithm) : array
    {
        $extensionRequirements = self::collectExtensionRequirements($composerJsonContents, $composerLockContents, $compressionAlgorithm);
        foreach ($extensionRequirements as $extension => $packages) {
            foreach ($packages as $package) {
                if (self::SELF_PACKAGE === $package) {
                    $message = sprintf('The application requires the extension "%s". Enable it or install a polyfill.', $extension);
                    $helpMessage = sprintf('The application requires the extension "%s".', $extension);
                } else {
                    $message = sprintf('The package "%s" requires the extension "%s". Enable it or install a polyfill.', $package, $extension);
                    $helpMessage = sprintf('The package "%s" requires the extension "%s".', $package, $extension);
                }
                $requirements[] = ['type' => 'extension', 'condition' => $extension, 'message' => $message, 'helpMessage' => $helpMessage];
            }
        }
        return $requirements;
    }
    private static function collectExtensionRequirements(array $composerJsonContents, array $composerLockContents, ?int $compressionAlgorithm) : array
    {
        $requirements = [];
        $polyfills = [];
        if (Phar::BZ2 === $compressionAlgorithm) {
            $requirements['bz2'] = [self::SELF_PACKAGE];
        }
        if (Phar::GZ === $compressionAlgorithm) {
            $requirements['zlib'] = [self::SELF_PACKAGE];
        }
        $platform = $composerLockContents['platform'] ?? [];
        foreach ($platform as $package => $constraint) {
            if (preg_match('/^ext-(?<extension>.+)$/', (string) $package, $matches)) {
                $extension = $matches['extension'];
                $requirements[$extension] = [self::SELF_PACKAGE];
            }
        }
        [$polyfills, $requirements] = [] === $composerLockContents ? self::collectComposerJsonExtensionRequirements($composerJsonContents, $polyfills, $requirements) : self::collectComposerLockExtensionRequirements($composerLockContents, $polyfills, $requirements);
        return array_diff_key($requirements, $polyfills);
    }
    private static function collectComposerJsonExtensionRequirements(array $composerJsonContents, $polyfills, $requirements) : array
    {
        $packages = $composerJsonContents['require'] ?? [];
        foreach ($packages as $packageName => $constraint) {
            if (1 === preg_match('/symfony\\/polyfill-(?<extension>.+)/', (string) $packageName, $matches)) {
                $extension = $matches['extension'];
                if (!\str_starts_with($extension, 'php')) {
                    $polyfills[$extension] = \true;
                    continue;
                }
            }
            if ('paragonie/sodium_compat' === $packageName) {
                $polyfills['libsodium'] = \true;
                continue;
            }
            if ('phpseclib/mcrypt_compat' === $packageName) {
                $polyfills['mcrypt'] = \true;
                continue;
            }
            if ('php' !== $packageName && preg_match('/^ext-(?<extension>.+)$/', (string) $packageName, $matches)) {
                $requirements[$matches['extension']] = [self::SELF_PACKAGE];
            }
        }
        return [$polyfills, $requirements];
    }
    private static function collectComposerLockExtensionRequirements(array $composerLockContents, $polyfills, $requirements) : array
    {
        $packages = $composerLockContents['packages'] ?? [];
        foreach ($packages as $packageInfo) {
            $packageRequire = $packageInfo['require'] ?? [];
            if (1 === preg_match('/symfony\\/polyfill-(?<extension>.+)/', (string) $packageInfo['name'], $matches)) {
                $extension = $matches['extension'];
                if (!\str_starts_with((string) $extension, 'php')) {
                    $polyfills[$extension] = \true;
                }
            }
            if ('paragonie/sodium_compat' === $packageInfo['name']) {
                $polyfills['libsodium'] = \true;
            }
            if ('phpseclib/mcrypt_compat' === $packageInfo['name']) {
                $polyfills['mcrypt'] = \true;
            }
            foreach ($packageRequire as $package => $constraint) {
                if (1 === preg_match('/^ext-(?<extension>.+)$/', (string) $package, $matches)) {
                    $extension = $matches['extension'];
                    if (\false === array_key_exists($extension, $requirements)) {
                        $requirements[$extension] = [];
                    }
                    $requirements[$extension][] = $packageInfo['name'];
                }
            }
        }
        return [$polyfills, $requirements];
    }
    private static function generatePhpCheckRequirement(string $requiredPhpVersion, ?string $packageName) : array
    {
        return ['type' => 'php', 'condition' => $requiredPhpVersion, 'message' => null === $packageName ? sprintf('The application requires the version "%s" or greater.', $requiredPhpVersion) : sprintf('The package "%s" requires the version "%s" or greater.', $packageName, $requiredPhpVersion), 'helpMessage' => null === $packageName ? sprintf('The application requires the version "%s" or greater.', $requiredPhpVersion) : sprintf('The package "%s" requires the version "%s" or greater.', $packageName, $requiredPhpVersion)];
    }
}
