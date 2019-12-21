<?php

declare(strict_types=1);

namespace Infection\Tests\Visitor;

use Infection\Visitor\ParentConnectorVisitor;
use PhpParser\Node;
use function in_array;
use function is_array;

final class ParentConnectorVisitorTest extends BaseVisitorTest
{
    private const CODE = <<<'PHP'
<?php declare(strict_types=1);

namespace Acme;

function hello() 
{
    return 'hello';
}
PHP;

    public function test_mutating_nodes_during_traverse_mutates_the_original_nodes(): void
    {
        $nodes = $this->traverse(
            $this->parseCode(self::CODE),
            [new ParentConnectorVisitor()]
        );

        foreach ($nodes as $node) {
            $this->assertHasParentNode($node, $nodes);
        }
    }

    /**
     * @param Node[] $roots
     */
    private function assertHasParentNode(Node $node, array $roots): void
    {
        if (!in_array($node, $roots, true)) {
            $this->assertTrue($node->hasAttribute(ParentConnectorVisitor::PARENT_KEY));
            $this->assertInstanceOf(Node::class, $node->getAttribute(ParentConnectorVisitor::PARENT_KEY));
        }

        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNodes = $node->$subNodeName;

            if (!is_array($subNodes)) {
                $subNodes = [$subNodes];
            }

            foreach ($subNodes as $subNode) {
                if ($subNode instanceof Node) {
                    $this->assertHasParentNode($subNode, $roots);
                }
            }
        }
    }
}
