<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Patcher;

use function _HumbugBoxb47773b41c19\Safe\preg_replace;
use function sprintf;
use function str_contains;
final class SymfonyPatcher implements Patcher
{
    private const PATHS = ['src/Symfony/Component/DependencyInjection/Dumper/PhpDumper.php', 'symfony/dependency-injection/Dumper/PhpDumper.php'];
    public function __invoke(string $filePath, string $prefix, string $contents) : string
    {
        if (!self::isSupportedFile($filePath)) {
            return $contents;
        }
        return (string) preg_replace('/use (Symfony(\\\\(?:\\\\)?)Component\\\\.+?;)/', sprintf('use %s$2$1', $prefix), $contents);
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
