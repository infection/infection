<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Builder;

use _HumbugBox9658796bb9f0\PhpParser;
use _HumbugBox9658796bb9f0\PhpParser\BuilderHelpers;
abstract class Declaration implements PhpParser\Builder
{
    protected $attributes = [];
    public abstract function addStmt($stmt);
    public function addStmts(array $stmts)
    {
        foreach ($stmts as $stmt) {
            $this->addStmt($stmt);
        }
        return $this;
    }
    public function setDocComment($docComment)
    {
        $this->attributes['comments'] = [BuilderHelpers::normalizeDocComment($docComment)];
        return $this;
    }
}
