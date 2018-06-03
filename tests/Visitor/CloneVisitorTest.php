<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Visitor;

use Infection\Visitor\CloneVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CloneVisitorTest extends TestCase
{
    public function test_it_does_not_save_the_old_nodes_without_the_clone_visitor()
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor($this->getChangingVisitor());
        $oldNodes = $this->getNodes();

        $newNodes = $traverser->traverse($oldNodes);
        $this->assertSame($oldNodes, $newNodes);
    }

    public function test_it_saves_the_old_nodes_with_the_clone_visitor()
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CloneVisitor());
        $traverser->addVisitor($this->getChangingVisitor());
        $oldNodes = $this->getNodes();

        $newNodes = $traverser->traverse($oldNodes);
        $this->assertNotSame($oldNodes, $newNodes);
    }

    private function getNodes(): array
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

        return $parser->parse(<<<'PHP'
<?php

function hello() 
{
    return 'hello';
}
PHP
        );
    }

    private function getChangingVisitor()
    {
        return new class() extends NodeVisitorAbstract {
            public function enterNode(Node $node)
            {
                if ($node instanceof Node\Scalar\String_) {
                    return new Node\Scalar\String_('foo');
                }
            }
        };
    }
}
