<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser;

use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\FullyQualifiedClassNameVisitor;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\IgnoreAllMutationsAnnotationReaderVisitor;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\IgnoreNode\AbstractMethodIgnorer;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\IgnoreNode\ChangingIgnorer;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\IgnoreNode\InterfaceIgnorer;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\IgnoreNode\NodeIgnorer;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\NonMutableNodesIgnorerVisitor;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ParentConnectorVisitor;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ReflectionVisitor;
use _HumbugBox9658796bb9f0\PhpParser\NodeTraverser;
use _HumbugBox9658796bb9f0\PhpParser\NodeTraverserInterface;
use _HumbugBox9658796bb9f0\PhpParser\NodeVisitor;
use _HumbugBox9658796bb9f0\PhpParser\NodeVisitor\NameResolver;
use SplObjectStorage;
class NodeTraverserFactory
{
    public function create(NodeVisitor $mutationVisitor, array $nodeIgnorers) : NodeTraverserInterface
    {
        $changingIgnorer = new ChangingIgnorer();
        $nodeIgnorers[] = $changingIgnorer;
        $nodeIgnorers[] = new InterfaceIgnorer();
        $nodeIgnorers[] = new AbstractMethodIgnorer();
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new IgnoreAllMutationsAnnotationReaderVisitor($changingIgnorer, new SplObjectStorage()));
        $traverser->addVisitor(new NonMutableNodesIgnorerVisitor($nodeIgnorers));
        $traverser->addVisitor(new NameResolver(null, ['preserveOriginalNames' => \true, 'replaceNodes' => \false]));
        $traverser->addVisitor(new ParentConnectorVisitor());
        $traverser->addVisitor(new FullyQualifiedClassNameVisitor());
        $traverser->addVisitor(new ReflectionVisitor());
        $traverser->addVisitor($mutationVisitor);
        return $traverser;
    }
}
