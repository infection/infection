<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\Symfony;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\Scoper;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\EnrichedReflector;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\SymbolsRegistry;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name\FullyQualified;
use function array_filter;
use function func_get_args;
use function preg_match as native_preg_match;
use function preg_match_all as native_preg_match_all;
use function str_contains;
use function str_replace;
use function strlen;
use function strpos;
use function substr;
final class YamlScoper implements Scoper
{
    private const YAML_EXTENSION_REGEX = '/\\.ya?ml$/i';
    private const CLASS_PATTERN = '/(?:(?<singleClass>(?:[\\p{L}_\\d]+(?<singleSeparator>\\\\(?:\\\\)?))):)|(?<class>(?:[\\p{L}_\\d]+(?<separator>\\\\(?:\\\\)?)+)+[\\p{L}_\\d]+)/u';
    public function __construct(private readonly Scoper $decoratedScoper, private readonly string $prefix, private readonly EnrichedReflector $enrichedReflector, private readonly SymbolsRegistry $symbolsRegistry)
    {
    }
    public function scope(string $filePath, string $contents) : string
    {
        if (1 !== native_preg_match(self::YAML_EXTENSION_REGEX, $filePath)) {
            return $this->decoratedScoper->scope(...func_get_args());
        }
        if (1 > native_preg_match_all(self::CLASS_PATTERN, $contents, $matches)) {
            return $contents;
        }
        $contents = self::replaceClasses(array_filter($matches['singleClass']), array_filter($matches['singleSeparator']), $this->prefix, $contents, $this->enrichedReflector, $this->symbolsRegistry);
        return self::replaceClasses(array_filter($matches['class']), array_filter($matches['separator']), $this->prefix, $contents, $this->enrichedReflector, $this->symbolsRegistry);
    }
    private static function replaceClasses(array $classes, array $separators, string $prefix, string $contents, EnrichedReflector $enrichedReflector, SymbolsRegistry $symbolsRegistry) : string
    {
        if ([] === $classes) {
            return $contents;
        }
        $scopedContents = '';
        foreach ($classes as $index => $class) {
            $separator = $separators[$index];
            $psr4Service = $class . $separator . ':';
            if (str_contains($contents, $psr4Service)) {
                $offset = strpos($contents, $psr4Service) + strlen($psr4Service);
                $stringToScope = substr($contents, 0, $offset);
                $contents = substr($contents, $offset);
                $prefixedClass = $prefix . $separator . $class;
                $scopedContents .= $enrichedReflector->belongsToExcludedNamespace($class . $separator . '__UnknownService__') ? $stringToScope : str_replace($class, $prefixedClass, $stringToScope);
                continue;
            }
            $offset = strpos($contents, $class) + strlen($class);
            $stringToScope = substr($contents, 0, $offset);
            $contents = substr($contents, $offset);
            $prefixedClass = $prefix . $separator . $class;
            $scopedContents .= $enrichedReflector->belongsToExcludedNamespace($class) ? $stringToScope : str_replace($class, $prefixedClass, $stringToScope);
            if ($enrichedReflector->isExposedClass($class)) {
                $symbolsRegistry->recordClass(new FullyQualified($class), new FullyQualified($prefixedClass));
            }
        }
        $scopedContents .= $contents;
        return $scopedContents;
    }
}
