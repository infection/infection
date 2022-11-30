<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\RequirementChecker;

use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\file_contents;
use function str_replace;
use _HumbugBoxb47773b41c19\Symfony\Component\Finder\Finder;
use _HumbugBoxb47773b41c19\Symfony\Component\Finder\SplFileInfo;
use function var_export;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class RequirementsDumper
{
    private const REQUIREMENTS_CONFIG_TEMPLATE = <<<'PHP'
<?php

namespace _HumbugBoxb47773b41c19;

return '__CONFIG__';
PHP;
    private const REQUIREMENT_CHECKER_PATH = __DIR__ . '/../../.requirement-checker';
    public static function dump(array $decodedComposerJsonContents, array $decodedComposerLockContents, ?int $compressionAlgorithm) : array
    {
        Assert::directory(self::REQUIREMENT_CHECKER_PATH, 'Expected the requirement checker to have been dumped');
        $filesWithContents = [self::dumpRequirementsConfig($decodedComposerJsonContents, $decodedComposerLockContents, $compressionAlgorithm)];
        $requirementCheckerFiles = Finder::create()->files()->in(self::REQUIREMENT_CHECKER_PATH);
        foreach ($requirementCheckerFiles as $file) {
            $filesWithContents[] = [$file->getRelativePathname(), file_contents($file->getPathname())];
        }
        return $filesWithContents;
    }
    private static function dumpRequirementsConfig(array $composerJsonDecodedContents, array $composerLockDecodedContents, ?int $compressionAlgorithm) : array
    {
        $config = AppRequirementsFactory::create($composerJsonDecodedContents, $composerLockDecodedContents, $compressionAlgorithm);
        return ['.requirements.php', str_replace('\'__CONFIG__\'', var_export($config, \true), self::REQUIREMENTS_CONFIG_TEMPLATE)];
    }
}
