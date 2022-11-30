<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types;

use function strlen;
use function substr;
use function trim;
/**
@psalm-immutable
*/
final class Context
{
    private string $namespace;
    /**
    @psalm-var
    */
    private array $namespaceAliases;
    /**
    @psalm-param
    */
    public function __construct(string $namespace, array $namespaceAliases = [])
    {
        $this->namespace = $namespace !== 'global' && $namespace !== 'default' ? trim($namespace, '\\') : '';
        foreach ($namespaceAliases as $alias => $fqnn) {
            if ($fqnn[0] === '\\') {
                $fqnn = substr($fqnn, 1);
            }
            if ($fqnn[strlen($fqnn) - 1] === '\\') {
                $fqnn = substr($fqnn, 0, -1);
            }
            $namespaceAliases[$alias] = $fqnn;
        }
        $this->namespaceAliases = $namespaceAliases;
    }
    public function getNamespace() : string
    {
        return $this->namespace;
    }
    /**
    @psalm-return
    */
    public function getNamespaceAliases() : array
    {
        return $this->namespaceAliases;
    }
}
