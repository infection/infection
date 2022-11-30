<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Patcher;

use function str_contains;
use function str_replace;
final class ComposerPatcher implements Patcher
{
    private const PATHS = ['src/Composer/Package/Loader/ArrayLoader.php', 'src/Composer/Package/Loader/RootPackageLoader.php'];
    public function __invoke(string $filePath, string $prefix, string $contents) : string
    {
        if (!self::isSupportedFile($filePath)) {
            return $contents;
        }
        return str_replace(['\'Composer\\Package\\RootPackage\'', '\'Composer\\\\Package\\\\RootPackage\'', ' Composer\\Package\\RootPackage ', '\'Composer\\Package\\CompletePackage\'', '\'Composer\\\\Package\\\\CompletePackage\'', ' Composer\\Package\\CompletePackage '], ['\'' . $prefix . '\\Composer\\Package\\RootPackage\'', '\'' . $prefix . '\\\\Composer\\\\Package\\\\RootPackage\'', ' ' . $prefix . '\\Composer\\Package\\RootPackage ', '\'' . $prefix . '\\Composer\\Package\\CompletePackage\'', '\'' . $prefix . '\\\\Composer\\\\Package\\\\CompletePackage\'', ' ' . $prefix . '\\Composer\\Package\\CompletePackage '], $contents);
    }
    private static function isSupportedFile(string $filePath) : bool
    {
        foreach (self::PATHS as $path) {
            if (str_contains($filePath, $path)) {
                return \true;
            }
        }
        return \false;
    }
}
