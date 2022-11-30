<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser;

use _HumbugBox9658796bb9f0\PhpParser\Node\Arg;
use _HumbugBox9658796bb9f0\PhpParser\Node\Expr;
use _HumbugBox9658796bb9f0\PhpParser\Node\Expr\BinaryOp\Concat;
use _HumbugBox9658796bb9f0\PhpParser\Node\Identifier;
use _HumbugBox9658796bb9f0\PhpParser\Node\Name;
use _HumbugBox9658796bb9f0\PhpParser\Node\Scalar\String_;
use _HumbugBox9658796bb9f0\PhpParser\Node\Stmt\Use_;
class BuilderFactory
{
    public function attribute($name, array $args = []) : Node\Attribute
    {
        return new Node\Attribute(BuilderHelpers::normalizeName($name), $this->args($args));
    }
    public function namespace($name) : Builder\Namespace_
    {
        return new Builder\Namespace_($name);
    }
    public function class(string $name) : Builder\Class_
    {
        return new Builder\Class_($name);
    }
    public function interface(string $name) : Builder\Interface_
    {
        return new Builder\Interface_($name);
    }
    public function trait(string $name) : Builder\Trait_
    {
        return new Builder\Trait_($name);
    }
    public function enum(string $name) : Builder\Enum_
    {
        return new Builder\Enum_($name);
    }
    public function useTrait(...$traits) : Builder\TraitUse
    {
        return new Builder\TraitUse(...$traits);
    }
    public function traitUseAdaptation($trait, $method = null) : Builder\TraitUseAdaptation
    {
        if ($method === null) {
            $method = $trait;
            $trait = null;
        }
        return new Builder\TraitUseAdaptation($trait, $method);
    }
    public function method(string $name) : Builder\Method
    {
        return new Builder\Method($name);
    }
    public function param(string $name) : Builder\Param
    {
        return new Builder\Param($name);
    }
    public function property(string $name) : Builder\Property
    {
        return new Builder\Property($name);
    }
    public function function(string $name) : Builder\Function_
    {
        return new Builder\Function_($name);
    }
    public function use($name) : Builder\Use_
    {
        return new Builder\Use_($name, Use_::TYPE_NORMAL);
    }
    public function useFunction($name) : Builder\Use_
    {
        return new Builder\Use_($name, Use_::TYPE_FUNCTION);
    }
    public function useConst($name) : Builder\Use_
    {
        return new Builder\Use_($name, Use_::TYPE_CONSTANT);
    }
    public function classConst($name, $value) : Builder\ClassConst
    {
        return new Builder\ClassConst($name, $value);
    }
    public function enumCase($name) : Builder\EnumCase
    {
        return new Builder\EnumCase($name);
    }
    public function val($value) : Expr
    {
        return BuilderHelpers::normalizeValue($value);
    }
    public function var($name) : Expr\Variable
    {
        if (!\is_string($name) && !$name instanceof Expr) {
            throw new \LogicException('Variable name must be string or Expr');
        }
        return new Expr\Variable($name);
    }
    public function args(array $args) : array
    {
        $normalizedArgs = [];
        foreach ($args as $key => $arg) {
            if (!$arg instanceof Arg) {
                $arg = new Arg(BuilderHelpers::normalizeValue($arg));
            }
            if (\is_string($key)) {
                $arg->name = BuilderHelpers::normalizeIdentifier($key);
            }
            $normalizedArgs[] = $arg;
        }
        return $normalizedArgs;
    }
    public function funcCall($name, array $args = []) : Expr\FuncCall
    {
        return new Expr\FuncCall(BuilderHelpers::normalizeNameOrExpr($name), $this->args($args));
    }
    public function methodCall(Expr $var, $name, array $args = []) : Expr\MethodCall
    {
        return new Expr\MethodCall($var, BuilderHelpers::normalizeIdentifierOrExpr($name), $this->args($args));
    }
    public function staticCall($class, $name, array $args = []) : Expr\StaticCall
    {
        return new Expr\StaticCall(BuilderHelpers::normalizeNameOrExpr($class), BuilderHelpers::normalizeIdentifierOrExpr($name), $this->args($args));
    }
    public function new($class, array $args = []) : Expr\New_
    {
        return new Expr\New_(BuilderHelpers::normalizeNameOrExpr($class), $this->args($args));
    }
    public function constFetch($name) : Expr\ConstFetch
    {
        return new Expr\ConstFetch(BuilderHelpers::normalizeName($name));
    }
    public function propertyFetch(Expr $var, $name) : Expr\PropertyFetch
    {
        return new Expr\PropertyFetch($var, BuilderHelpers::normalizeIdentifierOrExpr($name));
    }
    public function classConstFetch($class, $name) : Expr\ClassConstFetch
    {
        return new Expr\ClassConstFetch(BuilderHelpers::normalizeNameOrExpr($class), BuilderHelpers::normalizeIdentifier($name));
    }
    public function concat(...$exprs) : Concat
    {
        $numExprs = \count($exprs);
        if ($numExprs < 2) {
            throw new \LogicException('Expected at least two expressions');
        }
        $lastConcat = $this->normalizeStringExpr($exprs[0]);
        for ($i = 1; $i < $numExprs; $i++) {
            $lastConcat = new Concat($lastConcat, $this->normalizeStringExpr($exprs[$i]));
        }
        return $lastConcat;
    }
    private function normalizeStringExpr($expr) : Expr
    {
        if ($expr instanceof Expr) {
            return $expr;
        }
        if (\is_string($expr)) {
            return new String_($expr);
        }
        throw new \LogicException('Expected string or Expr');
    }
}
