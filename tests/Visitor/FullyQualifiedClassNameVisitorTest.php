<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Visitor;

use Infection\Visitor\FullyQualifiedClassNameVisitor;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FullyQualifiedClassNameVisitorTest extends TestCase
{
    private $spyVisitor;

    protected function setUp(): void
    {
        $this->spyVisitor = $this->getSpyVisitor();
    }

    public function test_it_adds_fqcl_to_class_node(): void
    {
        $code = $this->getFileContent('fqcn-empty-class.php');

        $this->parseAndTraverse($code);

        $this->assertCount(1, $this->spyVisitor->processedNodes);
        $this->assertSame(
            'FqcnEmptyClass\EmptyClass',
            $this->spyVisitor->processedNodes[0]->fullyQualifiedClassName->toString()
        );
    }

    public function test_it_adds_fqcl_to_class_with_interface(): void
    {
        $code = $this->getFileContent('fqcn-class-interface.php');

        $this->parseAndTraverse($code);

        $this->assertCount(1, $this->spyVisitor->processedNodes);
        $this->assertSame(
            'FqcnClassInterface\Ci',
            $this->spyVisitor->processedNodes[0]->fullyQualifiedClassName->toString()
        );
    }

    public function test_it_adds_fqcl_to_class_with_anonymous_class(): void
    {
        $code = $this->getFileContent('fqcn-anonymous-class.php');

        $this->parseAndTraverse($code);

        $this->assertCount(1, $this->spyVisitor->processedNodes);
        $this->assertSame(
            'FqcnClassAnonymous\Ci',
            $this->spyVisitor->processedNodes[0]->fullyQualifiedClassName->toString()
        );
    }

    private function getNodes(string $code): array
    {
        $lexer = new Lexer\Emulative();
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);

        return $parser->parse($code);
    }

    private function getSpyVisitor()
    {
        return new class() extends NodeVisitorAbstract {
            public $processedNodes = [];

            public function enterNode(Node $node): void
            {
                if (isset($node->fullyQualifiedClassName)) {
                    $this->processedNodes[] = $node;
                }
            }
        };
    }

    private function parseAndTraverse($code): void
    {
        $nodes = $this->getNodes($code);

        $traverser = new NodeTraverser();

        $traverser->addVisitor(new FullyQualifiedClassNameVisitor());
        $traverser->addVisitor($this->spyVisitor);

        $traverser->traverse($nodes);
    }

    private function getFileContent(string $file): string
    {
        return file_get_contents(sprintf(__DIR__ . '/../Fixtures/Autoloaded/Fqcn/%s', $file));
    }
}
