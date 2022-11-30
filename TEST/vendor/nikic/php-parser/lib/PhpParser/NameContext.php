<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser;

use _HumbugBox9658796bb9f0\PhpParser\Node\Name;
use _HumbugBox9658796bb9f0\PhpParser\Node\Name\FullyQualified;
use _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;
class NameContext
{
    protected $namespace;
    protected $aliases = [];
    protected $origAliases = [];
    protected $errorHandler;
    public function __construct(ErrorHandler $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }
    public function startNamespace(Name $namespace = null)
    {
        $this->namespace = $namespace;
        $this->origAliases = $this->aliases = [Stmt\Use_::TYPE_NORMAL => [], Stmt\Use_::TYPE_FUNCTION => [], Stmt\Use_::TYPE_CONSTANT => []];
    }
    public function addAlias(Name $name, string $aliasName, int $type, array $errorAttrs = [])
    {
        if ($type === Stmt\Use_::TYPE_CONSTANT) {
            $aliasLookupName = $aliasName;
        } else {
            $aliasLookupName = \strtolower($aliasName);
        }
        if (isset($this->aliases[$type][$aliasLookupName])) {
            $typeStringMap = [Stmt\Use_::TYPE_NORMAL => '', Stmt\Use_::TYPE_FUNCTION => 'function ', Stmt\Use_::TYPE_CONSTANT => 'const '];
            $this->errorHandler->handleError(new Error(\sprintf('Cannot use %s%s as %s because the name is already in use', $typeStringMap[$type], $name, $aliasName), $errorAttrs));
            return;
        }
        $this->aliases[$type][$aliasLookupName] = $name;
        $this->origAliases[$type][$aliasName] = $name;
    }
    public function getNamespace()
    {
        return $this->namespace;
    }
    public function getResolvedName(Name $name, int $type)
    {
        if ($type === Stmt\Use_::TYPE_NORMAL && $name->isSpecialClassName()) {
            if (!$name->isUnqualified()) {
                $this->errorHandler->handleError(new Error(\sprintf("'\\%s' is an invalid class name", $name->toString()), $name->getAttributes()));
            }
            return $name;
        }
        if ($name->isFullyQualified()) {
            return $name;
        }
        if (null !== ($resolvedName = $this->resolveAlias($name, $type))) {
            return $resolvedName;
        }
        if ($type !== Stmt\Use_::TYPE_NORMAL && $name->isUnqualified()) {
            if (null === $this->namespace) {
                return new FullyQualified($name, $name->getAttributes());
            }
            return null;
        }
        return FullyQualified::concat($this->namespace, $name, $name->getAttributes());
    }
    public function getResolvedClassName(Name $name) : Name
    {
        return $this->getResolvedName($name, Stmt\Use_::TYPE_NORMAL);
    }
    public function getPossibleNames(string $name, int $type) : array
    {
        $lcName = \strtolower($name);
        if ($type === Stmt\Use_::TYPE_NORMAL) {
            if ($lcName === "self" || $lcName === "parent" || $lcName === "static") {
                return [new Name($name)];
            }
        }
        $possibleNames = [new FullyQualified($name)];
        if (null !== ($nsRelativeName = $this->getNamespaceRelativeName($name, $lcName, $type))) {
            if (null === $this->resolveAlias($nsRelativeName, $type)) {
                $possibleNames[] = $nsRelativeName;
            }
        }
        foreach ($this->origAliases[Stmt\Use_::TYPE_NORMAL] as $alias => $orig) {
            $lcOrig = $orig->toLowerString();
            if (0 === \strpos($lcName, $lcOrig . '\\')) {
                $possibleNames[] = new Name($alias . \substr($name, \strlen($lcOrig)));
            }
        }
        foreach ($this->origAliases[$type] as $alias => $orig) {
            if ($type === Stmt\Use_::TYPE_CONSTANT) {
                $normalizedOrig = $this->normalizeConstName($orig->toString());
                if ($normalizedOrig === $this->normalizeConstName($name)) {
                    $possibleNames[] = new Name($alias);
                }
            } else {
                if ($orig->toLowerString() === $lcName) {
                    $possibleNames[] = new Name($alias);
                }
            }
        }
        return $possibleNames;
    }
    public function getShortName(string $name, int $type) : Name
    {
        $possibleNames = $this->getPossibleNames($name, $type);
        $shortestName = null;
        $shortestLength = \INF;
        foreach ($possibleNames as $possibleName) {
            $length = \strlen($possibleName->toCodeString());
            if ($length < $shortestLength) {
                $shortestName = $possibleName;
                $shortestLength = $length;
            }
        }
        return $shortestName;
    }
    private function resolveAlias(Name $name, $type)
    {
        $firstPart = $name->getFirst();
        if ($name->isQualified()) {
            $checkName = \strtolower($firstPart);
            if (isset($this->aliases[Stmt\Use_::TYPE_NORMAL][$checkName])) {
                $alias = $this->aliases[Stmt\Use_::TYPE_NORMAL][$checkName];
                return FullyQualified::concat($alias, $name->slice(1), $name->getAttributes());
            }
        } elseif ($name->isUnqualified()) {
            $checkName = $type === Stmt\Use_::TYPE_CONSTANT ? $firstPart : \strtolower($firstPart);
            if (isset($this->aliases[$type][$checkName])) {
                return new FullyQualified($this->aliases[$type][$checkName], $name->getAttributes());
            }
        }
        return null;
    }
    private function getNamespaceRelativeName(string $name, string $lcName, int $type)
    {
        if (null === $this->namespace) {
            return new Name($name);
        }
        if ($type === Stmt\Use_::TYPE_CONSTANT) {
            if ($lcName === "true" || $lcName === "false" || $lcName === "null") {
                return new Name($name);
            }
        }
        $namespacePrefix = \strtolower($this->namespace . '\\');
        if (0 === \strpos($lcName, $namespacePrefix)) {
            return new Name(\substr($name, \strlen($namespacePrefix)));
        }
        return null;
    }
    private function normalizeConstName(string $name)
    {
        $nsSep = \strrpos($name, '\\');
        if (\false === $nsSep) {
            return $name;
        }
        $ns = \substr($name, 0, $nsSep);
        $shortName = \substr($name, $nsSep + 1);
        return \strtolower($ns) . '\\' . $shortName;
    }
}
