<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Builder;

use _HumbugBox9658796bb9f0\PhpParser\BuilderHelpers;
use _HumbugBox9658796bb9f0\PhpParser\Node;
abstract class FunctionLike extends Declaration
{
    protected $returnByRef = \false;
    protected $params = [];
    protected $returnType = null;
    public function makeReturnByRef()
    {
        $this->returnByRef = \true;
        return $this;
    }
    public function addParam($param)
    {
        $param = BuilderHelpers::normalizeNode($param);
        if (!$param instanceof Node\Param) {
            throw new \LogicException(\sprintf('Expected parameter node, got "%s"', $param->getType()));
        }
        $this->params[] = $param;
        return $this;
    }
    public function addParams(array $params)
    {
        foreach ($params as $param) {
            $this->addParam($param);
        }
        return $this;
    }
    public function setReturnType($type)
    {
        $this->returnType = BuilderHelpers::normalizeType($type);
        return $this;
    }
}
