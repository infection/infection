<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Set011;

use ReflectionClass;
final class DirectionaryLocator
{
    public static function locateDictionaries(string $dir) : array
    {
        $dictionaryFiles = \array_values(\array_filter(\array_map(function (string $filePath) use($dir) {
            return \realpath($dir . \DIRECTORY_SEPARATOR . $filePath);
        }, \array_filter(\scandir($dir), function (string $file) : bool {
            return 1 === \preg_match('/.*Dictionary\\.php$/', $file);
        })), function ($filePath) : bool {
            return \false !== $filePath;
        }));
        $classes = \get_declared_classes();
        foreach ($dictionaryFiles as $dictionaryFile) {
            include $dictionaryFile;
        }
        $newClasses = \array_diff(\get_declared_classes(), $classes);
        return \array_reduce($newClasses, function (array $dictionaries, string $className) : array {
            $class = new ReflectionClass($className);
            if (\false === $class->isAbstract() && $class->implementsInterface(Dictionary::class)) {
                $dictionaries[] = $class->newInstanceWithoutConstructor();
            }
            return $dictionaries;
        }, []);
    }
    private function __construct()
    {
    }
}
