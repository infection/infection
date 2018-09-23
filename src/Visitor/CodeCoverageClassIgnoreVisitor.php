<?php

declare(strict_types=1);

namespace Infection\Visitor;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class CodeCoverageClassIgnoreVisitor extends NodeVisitorAbstract
{
    private $namespace;

    public function enterNode(Node $node)
    {
        if ($node instanceof Stmt\Namespace_) {
            $this->namespace = $node->name;
        } elseif ($node instanceof Stmt\ClassLike) {
            $fullyQualifiedClassName = $node->name ? Name::concat($this->namespace, $node->name->name) : null;

            $reflectionClass = new \ReflectionClass($fullyQualifiedClassName->toString());

            $docComment = $reflectionClass->getDocComment();

            if ($docComment === false) {
                return null;
            }

            if (strpos($docComment, '@codeCoverageIgnore') !== false) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
        }
    }
}