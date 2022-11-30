<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Internal;

use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\Node\Expr;
class PrintableNewAnonClassNode extends Expr
{
    public $attrGroups;
    public $args;
    public $extends;
    public $implements;
    public $stmts;
    public function __construct(array $attrGroups, array $args, Node\Name $extends = null, array $implements, array $stmts, array $attributes)
    {
        parent::__construct($attributes);
        $this->attrGroups = $attrGroups;
        $this->args = $args;
        $this->extends = $extends;
        $this->implements = $implements;
        $this->stmts = $stmts;
    }
    public static function fromNewNode(Expr\New_ $newNode)
    {
        $class = $newNode->class;
        \assert($class instanceof Node\Stmt\Class_);
        return new self($class->attrGroups, $newNode->args, $class->extends, $class->implements, $class->stmts, $newNode->getAttributes());
    }
    public function getType() : string
    {
        return 'Expr_PrintableNewAnonClass';
    }
    public function getSubNodeNames() : array
    {
        return ['attrGroups', 'args', 'extends', 'implements', 'stmts'];
    }
}
