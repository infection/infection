<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection;

use InvalidArgumentException;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Context;
use function explode;
use function implode;
use function strpos;
/**
@psalm-immutable
*/
class FqsenResolver
{
    private const OPERATOR_NAMESPACE = '\\';
    public function resolve(string $fqsen, ?Context $context = null) : Fqsen
    {
        if ($context === null) {
            $context = new Context('');
        }
        if ($this->isFqsen($fqsen)) {
            return new Fqsen($fqsen);
        }
        return $this->resolvePartialStructuralElementName($fqsen, $context);
    }
    private function isFqsen(string $type) : bool
    {
        return strpos($type, self::OPERATOR_NAMESPACE) === 0;
    }
    private function resolvePartialStructuralElementName(string $type, Context $context) : Fqsen
    {
        $typeParts = explode(self::OPERATOR_NAMESPACE, $type, 2);
        $namespaceAliases = $context->getNamespaceAliases();
        if (!isset($namespaceAliases[$typeParts[0]])) {
            $namespace = $context->getNamespace();
            if ($namespace !== '') {
                $namespace .= self::OPERATOR_NAMESPACE;
            }
            return new Fqsen(self::OPERATOR_NAMESPACE . $namespace . $type);
        }
        $typeParts[0] = $namespaceAliases[$typeParts[0]];
        return new Fqsen(self::OPERATOR_NAMESPACE . implode(self::OPERATOR_NAMESPACE, $typeParts));
    }
}
