<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\Composer;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\EnrichedReflector;
use stdClass;
use function array_map;
use function array_merge;
use function is_array;
use function is_string;
use function rtrim;
use function sprintf;
use function str_contains;
use function str_ends_with;
use function str_replace;
final class AutoloadPrefixer
{
    public function __construct(private readonly string $prefix, private readonly EnrichedReflector $enrichedReflector)
    {
    }
    public function prefixPackageAutoloadStatements(stdClass $contents) : stdClass
    {
        if (isset($contents->autoload)) {
            $contents->autoload = self::prefixAutoloadStatements($contents->autoload, $this->prefix, $this->enrichedReflector);
        }
        if (isset($contents->{'autoload-dev'})) {
            $contents->{'autoload-dev'} = self::prefixAutoloadStatements($contents->{'autoload-dev'}, $this->prefix, $this->enrichedReflector);
        }
        if (isset($contents->extra->laravel->providers)) {
            $contents->extra->laravel->providers = self::prefixLaravelProviders($contents->extra->laravel->providers, $this->prefix, $this->enrichedReflector);
        }
        return $contents;
    }
    private static function prefixAutoloadStatements(stdClass $autoload, string $prefix, EnrichedReflector $enrichedReflector) : stdClass
    {
        if (!isset($autoload->{'psr-4'}) && !isset($autoload->{'psr-0'})) {
            return $autoload;
        }
        if (isset($autoload->{'psr-0'})) {
            [$psr4, $classMap] = self::transformPsr0ToPsr4AndClassmap((array) $autoload->{'psr-0'}, (array) ($autoload->{'psr-4'} ?? new stdClass()), (array) ($autoload->{'classmap'} ?? new stdClass()));
            if ([] === $psr4) {
                unset($autoload->{'psr-4'});
            } else {
                $autoload->{'psr-4'} = $psr4;
            }
            if ([] === $classMap) {
                unset($autoload->{'classmap'});
            } else {
                $autoload->{'classmap'} = $classMap;
            }
        }
        unset($autoload->{'psr-0'});
        if (isset($autoload->{'psr-4'})) {
            $autoload->{'psr-4'} = self::prefixAutoload((array) $autoload->{'psr-4'}, $prefix, $enrichedReflector);
        }
        return $autoload;
    }
    private static function prefixAutoload(array $autoload, string $prefix, EnrichedReflector $enrichedReflector) : array
    {
        $loader = [];
        foreach ($autoload as $namespace => $paths) {
            $newNamespace = $enrichedReflector->isExcludedNamespace($namespace) ? $namespace : sprintf('%s\\%s', $prefix, $namespace);
            $loader[$newNamespace] = $paths;
        }
        return $loader;
    }
    private static function transformPsr0ToPsr4AndClassmap(array $psr0, array $psr4, array $classMap) : array
    {
        foreach ($psr0 as $namespace => $path) {
            if (!str_ends_with($namespace, '\\')) {
                $namespace .= '\\';
            }
            if (str_contains($namespace, '_')) {
                $classMap[] = $path;
                continue;
            }
            $path = self::updatePSR0Path($path, $namespace);
            if (!isset($psr4[$namespace])) {
                $psr4[$namespace] = $path;
                continue;
            }
            $psr4[$namespace] = self::mergeNamespaces($namespace, $path, $psr4);
        }
        return [$psr4, $classMap];
    }
    private static function updatePSR0Path(string|array $path, string $namespace) : string|array
    {
        $namespaceForPsr = rtrim(str_replace('\\', '/', $namespace), '/');
        if (!is_array($path)) {
            if (!str_ends_with($path, '/')) {
                $path .= '/';
            }
            $path .= $namespaceForPsr . '/';
            return $path;
        }
        foreach ($path as $key => $item) {
            if (!str_ends_with($item, '/')) {
                $item .= '/';
            }
            $item .= $namespaceForPsr . '/';
            $path[$key] = $item;
        }
        return $path;
    }
    private static function mergeNamespaces(string $psr0Namespace, string|array $psr0Path, array $psr4) : string|array
    {
        if (is_string($psr0Path) && is_string($psr4[$psr0Namespace])) {
            return [$psr4[$psr0Namespace], $psr0Path];
        }
        if (is_array($psr0Path) && is_string($psr4[$psr0Namespace])) {
            $psr0Path[] = $psr4[$psr0Namespace];
            return $psr0Path;
        }
        if (is_string($psr0Path) && is_array($psr4[$psr0Namespace])) {
            $psr4[$psr0Namespace][] = $psr0Path;
            return $psr4[$psr0Namespace];
        }
        if (is_array($psr0Path) && is_array($psr4[$psr0Namespace])) {
            return array_merge($psr4[$psr0Namespace], $psr0Path);
        }
        return $psr0Path;
    }
    private static function prefixLaravelProviders(array $providers, string $prefix, EnrichedReflector $enrichedReflector) : array
    {
        return array_map(static fn(string $provider) => $enrichedReflector->isExcludedNamespace($provider) ? $provider : sprintf('%s\\%s', $prefix, $provider), $providers);
    }
}
