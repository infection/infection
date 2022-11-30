<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Caster;

class LinkStub extends ConstStub
{
    public $inVendor = \false;
    private static array $vendorRoots;
    private static array $composerRoots = [];
    public function __construct(string $label, int $line = 0, string $href = null)
    {
        $this->value = $label;
        if (null === $href) {
            $href = $label;
        }
        if (!\is_string($href)) {
            return;
        }
        if (\str_starts_with($href, 'file://')) {
            if ($href === $label) {
                $label = \substr($label, 7);
            }
            $href = \substr($href, 7);
        } elseif (\str_contains($href, '://')) {
            $this->attr['href'] = $href;
            return;
        }
        if (!\is_file($href)) {
            return;
        }
        if ($line) {
            $this->attr['line'] = $line;
        }
        if ($label !== ($this->attr['file'] = \realpath($href) ?: $href)) {
            return;
        }
        if ($composerRoot = $this->getComposerRoot($href, $this->inVendor)) {
            $this->attr['ellipsis'] = \strlen($href) - \strlen($composerRoot) + 1;
            $this->attr['ellipsis-type'] = 'path';
            $this->attr['ellipsis-tail'] = 1 + ($this->inVendor ? 2 + \strlen(\implode('', \array_slice(\explode(\DIRECTORY_SEPARATOR, \substr($href, 1 - $this->attr['ellipsis'])), 0, 2))) : 0);
        } elseif (3 < \count($ellipsis = \explode(\DIRECTORY_SEPARATOR, $href))) {
            $this->attr['ellipsis'] = 2 + \strlen(\implode('', \array_slice($ellipsis, -2)));
            $this->attr['ellipsis-type'] = 'path';
            $this->attr['ellipsis-tail'] = 1;
        }
    }
    private function getComposerRoot(string $file, bool &$inVendor)
    {
        if (!isset(self::$vendorRoots)) {
            self::$vendorRoots = [];
            foreach (\get_declared_classes() as $class) {
                if ('C' === $class[0] && \str_starts_with($class, 'ComposerAutoloaderInit')) {
                    $r = new \ReflectionClass($class);
                    $v = \dirname($r->getFileName(), 2);
                    if (\is_file($v . '/composer/installed.json')) {
                        self::$vendorRoots[] = $v . \DIRECTORY_SEPARATOR;
                    }
                }
            }
        }
        $inVendor = \false;
        if (isset(self::$composerRoots[$dir = \dirname($file)])) {
            return self::$composerRoots[$dir];
        }
        foreach (self::$vendorRoots as $root) {
            if ($inVendor = \str_starts_with($file, $root)) {
                return $root;
            }
        }
        $parent = $dir;
        while (!@\is_file($parent . '/composer.json')) {
            if (!@\file_exists($parent)) {
                break;
            }
            if ($parent === \dirname($parent)) {
                return self::$composerRoots[$dir] = \false;
            }
            $parent = \dirname($parent);
        }
        return self::$composerRoots[$dir] = $parent . \DIRECTORY_SEPARATOR;
    }
}
