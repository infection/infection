<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\Node;

use _HumbugBoxb47773b41c19\PhpParser\Node\Arg;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\ConstFetch;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\FuncCall;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name\FullyQualified;
use _HumbugBoxb47773b41c19\PhpParser\Node\Scalar\String_;
final class ClassAliasFuncCall extends FuncCall
{
    public function __construct(FullyQualified $prefixedName, FullyQualified $originalName, array $attributes = [])
    {
        parent::__construct(new FullyQualified('class_alias'), [new Arg(new String_((string) $prefixedName)), new Arg(new String_((string) $originalName)), new Arg(new ConstFetch(new FullyQualified('false')))], $attributes);
    }
}
