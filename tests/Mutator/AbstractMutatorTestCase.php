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
use Infection\Tests\Fixtures\SimpleMutation;
use Infection\Tests\Fixtures\SimpleMutationsCollectorVisitor;
use Infection\Tests\Fixtures\SimpleMutatorVisitor;
use Infection\Visitor\CloneVisitor;
use Infection\Visitor\FullyQualifiedClassNameVisitor;
use Infection\Visitor\ParentConnectorVisitor;
use Infection\Visitor\ReflectionVisitor;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
abstract class AbstractMutatorTestCase extends TestCase
{
    /**
     * @var Mutator
     */
    protected $mutator;

    public function doTest(string $inputCode, $expectedCode = null): void
    {
        $expectedCodeSamples = (array) $expectedCode;

        $inputCode = rtrim($inputCode, "\n");

        if ($inputCode === $expectedCode) {
            throw new \LogicException('Input code cant be the same as mutated code');
        }

        $mutants = $this->mutate($inputCode);

        if ($expectedCode === null) {
            $this->assertCount(0, $mutants);
        } else {
            foreach ($mutants as $realMutatedCode) {
                $expectedCodeSample = array_shift($expectedCodeSamples);

                if ($expectedCodeSample === null) {
                    throw new \Exception('The number of expected mutated code samples must equal the number of generated Mutants by mutator.');
                }
                $expectedCodeSample = rtrim($expectedCodeSample, "\n");
                $this->assertSame($expectedCodeSample, $realMutatedCode);
                $this->assertSyntaxIsValid($realMutatedCode);
            }
        }
    }

    protected function getMutator(): Mutator
    {
        $class = \get_class($this);
        $mutator = substr(str_replace('\Tests', '', $class), 0, -4);

        return new $mutator(new MutatorConfig([]));
    }

    protected function setUp(): void
    {
        $this->mutator = $this->getMutator();
    }

    protected function getNodes(string $code): array
    {
        $lexer = new Lexer\Emulative();
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);

        return $parser->parse($code);
    }

    protected function mutate(string $code): array
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, new Lexer\Emulative());
        $prettyPrinter = new Standard();

        $mutations = $this->getMutationsFromCode($code, $parser);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CloneVisitor());

        $mutants = [];

        foreach ($mutations as $mutation) {
            $mutatorVisitor = new SimpleMutatorVisitor($mutation);

            $traverser->addVisitor($mutatorVisitor);

            $mutatedStatements = $traverser->traverse($mutation->getOriginalFileAst());

            $mutants[] = $prettyPrinter->prettyPrintFile($mutatedStatements);

            $traverser->removeVisitor($mutatorVisitor);
        }

        return $mutants;
    }

    /**
     * @param string $code
     *
     * @return SimpleMutation[]
     */
    private function getMutationsFromCode(string $code, Parser $parser): array
    {
        $initialStatements = $parser->parse($code);

        $traverser = new NodeTraverser();

        $mutationsCollectorVisitor = new SimpleMutationsCollectorVisitor($this->getMutator(), $initialStatements);

        $traverser->addVisitor($mutationsCollectorVisitor);
        $traverser->addVisitor(new ParentConnectorVisitor());
        $traverser->addVisitor(new FullyQualifiedClassNameVisitor());
        $traverser->addVisitor(new ReflectionVisitor());

        $traverser->traverse($initialStatements);

        return $mutationsCollectorVisitor->getMutations();
    }

    private function assertSyntaxIsValid(string $realMutatedCode): void
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
