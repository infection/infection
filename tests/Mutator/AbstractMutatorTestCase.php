<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator;

use Infection\Mutator\Util\Mutator;
use Infection\Mutator\Util\MutatorConfig;
use Infection\Tests\Fixtures\SimpleMutatorVisitor;
use Infection\Visitor\CloneVisitor;
use Infection\Visitor\FullyQualifiedClassNameVisitor;
use Infection\Visitor\ParentConnectorVisitor;
use Infection\Visitor\ReflectionVisitor;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

abstract class AbstractMutatorTestCase extends TestCase
{
    /**
     * @var Mutator
     */
    protected $mutator;

    public function doTest(string $inputCode, string $expectedCode = null)
    {
        if ($inputCode === $expectedCode) {
            throw new \LogicException('Input code cant be the same as mutated code');
        }

        $realMutatedCode = $this->mutate($inputCode);
        if ($expectedCode !== null) {
            $this->assertSame($expectedCode, $realMutatedCode);
            $this->assertSyntaxIsValid($realMutatedCode);
        } else {
            $this->assertSame($inputCode, $realMutatedCode);
        }
    }

    protected function getMutator(): Mutator
    {
        $class = get_class($this);
        $mutator = substr(str_replace('\Tests', '', $class), 0, -4);

        return new $mutator(new MutatorConfig([]));
    }

    protected function setUp()
    {
        $this->mutator = $this->getMutator();
    }

    protected function getNodes(string $code): array
    {
        $lexer = new Lexer\Emulative();
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);

        return $parser->parse($code);
    }

    protected function mutate(string $code)
    {
        $traverser = new NodeTraverser();
        $prettyPrinter = new Standard();

        $nodes = $this->getNodes($code);

        $traverser->addVisitor(new ParentConnectorVisitor());
        $traverser->addVisitor(new FullyQualifiedClassNameVisitor());
        $traverser->addVisitor(new ReflectionVisitor());
        $traverser->addVisitor(new CloneVisitor());
        $traverser->addVisitor(new SimpleMutatorVisitor($this->mutator));

        $mutatedNodes = $traverser->traverse($nodes);

        return $prettyPrinter->prettyPrintFile($mutatedNodes);
    }

    private function assertSyntaxIsValid(string $realMutatedCode)
    {
        exec(sprintf('echo %s | php -l', escapeshellarg($realMutatedCode)), $output, $returnCode);

        $this->assertSame(
            0,
            $returnCode,
            sprintf(
                'Mutator %s produces invalid code',
                $this->getMutator()::getName()
            )
        );
    }
}
