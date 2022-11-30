<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Expr;

use _HumbugBoxb47773b41c19\PhpParser\Node\Arg;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr;
use _HumbugBoxb47773b41c19\PhpParser\Node\VariadicPlaceholder;
abstract class CallLike extends Expr
{
    public abstract function getRawArgs() : array;
    public function isFirstClassCallable() : bool
    {
        foreach ($this->getRawArgs() as $arg) {
            if ($arg instanceof VariadicPlaceholder) {
                return \true;
            }
        }
        return \false;
    }
    public function getArgs() : array
    {
        \assert(!$this->isFirstClassCallable());
        return $this->getRawArgs();
    }
}
