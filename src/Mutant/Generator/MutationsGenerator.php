<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types = 1);


namespace Infection\Mutant\Generator;


use Infection\Mutator\Arithmetic\BitwiseAnd;
use Infection\Mutator\Arithmetic\BitwiseNot;
use Infection\Mutator\Arithmetic\BitwiseOr;
use Infection\Mutator\Arithmetic\BitwiseXor;
use Infection\Mutator\Arithmetic\Decrement;
use Infection\Mutator\Arithmetic\DivEqual;
use Infection\Mutator\Arithmetic\Division;
use Infection\Mutator\Arithmetic\Exponentiation;
use Infection\Mutator\Arithmetic\Increment;
use Infection\Mutator\Arithmetic\Minus;
use Infection\Mutator\Arithmetic\MinusEqual;
use Infection\Mutator\Arithmetic\ModEqual;
use Infection\Mutator\Arithmetic\Modulus;
use Infection\Mutator\Arithmetic\MulEqual;
use Infection\Mutator\Arithmetic\Multiplication;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Arithmetic\PlusEqual;
use Infection\Mutator\Arithmetic\PowEqual;
use Infection\Mutator\Arithmetic\ShiftLeft;
use Infection\Mutator\Arithmetic\ShiftRight;
use Infection\Mutator\Boolean\FalseValue;
use Infection\Mutator\Boolean\LogicalAnd;
use Infection\Mutator\Boolean\LogicalLowerAnd;
use Infection\Mutator\Boolean\LogicalLowerOr;
use Infection\Mutator\Boolean\LogicalNot;
use Infection\Mutator\Boolean\LogicalOr;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\ConditionalBoundary\GreaterThanOrEqualTo;
use Infection\Mutator\ConditionalBoundary\GreaterThan;
use Infection\Mutator\ConditionalBoundary\LessThan;
use Infection\Mutator\ConditionalBoundary\LessThanOrEqualTo;
use Infection\Mutator\ConditionalNegotiation\Equal;
use Infection\Mutator\ConditionalNegotiation\GreaterThan as GreaterThanNegotiation;
use Infection\Mutator\ConditionalNegotiation\GreaterThanOrEqualTo as GreaterThanOrEqualToNegotiation;
use Infection\Mutator\ConditionalNegotiation\Identical;
use Infection\Mutator\ConditionalNegotiation\NotEqual;
use Infection\Mutator\ConditionalNegotiation\NotIdentical;
use Infection\Mutator\Number\OneZeroFloat;
use Infection\Mutator\Number\OneZeroInteger;
use Infection\Mutator\ReturnValue\FloatNegation;
use Infection\Mutator\ReturnValue\FunctionCall;
use Infection\Mutator\ReturnValue\IntegerNegation;
use Infection\Mutator\ReturnValue\NewObject;
use Infection\Mutator\ReturnValue\This;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\Tests\Mutator\ReturnValue\NewObjectTest;
use Infection\Visitor\InsideFunctionDetectorVisitor;
use Infection\Visitor\MutationsCollectorVisitor;
use Infection\Visitor\ParentConnectorVisitor;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class MutationsGenerator
{
    /**
     * @var array source directories
     */
    private $srcDirs;

    /**
     * @var CodeCoverageData
     */
    private $codeCoverageData;

    /**
     * @var array
     */
    private $excludeDirs;

    public function __construct(array $srcDirs, array $excludeDirs, CodeCoverageData $codeCoverageData)
    {
        $this->srcDirs = $srcDirs;
        $this->codeCoverageData = $codeCoverageData;
        $this->excludeDirs = $excludeDirs;
    }

    /**
     * @param bool $onlyCovered mutate only covered by tests lines of code
     * @param string $filter
     * @return array
     */
    public function generate(bool $onlyCovered, string $filter = ''): array
    {
        $files = $this->getSrcFiles($filter);
        $allFilesMutations = [];

        foreach ($files as $file) {
            if (!$onlyCovered || ($onlyCovered && $this->hasTests($file))) {
                $allFilesMutations = array_merge($allFilesMutations, $this->getMutationsFromFile($file, $onlyCovered));
            }
        }

        return $allFilesMutations;
    }

    /**
     * @param string $filter
     * @return Finder
     * @throws \InvalidArgumentException
     */
    private function getSrcFiles(string $filter = ''): Finder
    {
        $finder = new Finder();
        $finder->files()->in($this->srcDirs);
        $finder->exclude($this->excludeDirs);

        $finder->files()->name($filter ?: '*.php');

        return $finder;
    }

    /**
     * @param SplFileInfo $file
     * @param bool $onlyCovered mutate only covered by tests lines of code
     * @return array
     */
    private function getMutationsFromFile(SplFileInfo $file, bool $onlyCovered): array
    {
        $lexer = new Lexer([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos'
            ]
        ]);
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);
        $traverser = new NodeTraverser();
        $mutators = $this->getMutators();

        $mutationsCollectorVisitor = new MutationsCollectorVisitor(
            $mutators,
            $file->getRealPath(),
            $this->codeCoverageData,
            $onlyCovered
        );

        $traverser->addVisitor(new ParentConnectorVisitor());
        $traverser->addVisitor(new InsideFunctionDetectorVisitor());
        $traverser->addVisitor($mutationsCollectorVisitor);

        $originalCode = $file->getContents();

        $initialStatements = $parser->parse($originalCode);

        $traverser->traverse($initialStatements);

        return $mutationsCollectorVisitor->getMutations();
    }

    private function hasTests(SplFileInfo $file): bool
    {
        return $this->codeCoverageData->hasTests($file->getRealPath());
    }

    private function getMutators(): array
    {
        // TODO lazy loading. it is executed in the loop
        return [
            // Arithmetic
            new BitwiseAnd(),
            new BitwiseNot(),
            new BitwiseOr(),
            new BitwiseXor(),
            new Decrement(),
            new DivEqual(),
            new Division(),
            new Exponentiation(),
            new Increment(),
            new Minus(),
            new MinusEqual(),
            new ModEqual(),
            new Modulus(),
            new MulEqual(),
            new Multiplication(),
            new Plus(),
            new PlusEqual(),
            new PowEqual(),
            new ShiftLeft(),
            new ShiftRight(),

            // Boolean
            new FalseValue(),
            new LogicalAnd(),
            new LogicalLowerAnd(),
            new LogicalLowerOr(),
            new LogicalNot(),
            new LogicalOr(),
            new TrueValue(),

            // Conditional Boundary
            new GreaterThan(),
            new GreaterThanOrEqualTo(),
            new LessThan(),
            new LessThanOrEqualTo(),

            // Conditional Negotiation
            new Equal(),
            new GreaterThanNegotiation(),
            new GreaterThanOrEqualToNegotiation(),
            new Identical(),
            new LessThan(),
            new LessThanOrEqualTo(),
            new NotEqual(),
            new NotIdentical(),

            // Number
            new OneZeroInteger(),
            new OneZeroFloat(),

            // Return Value
            new FloatNegation(),
            new FunctionCall(),
            new IntegerNegation(),
            new NewObject(),
            new This(),
        ];
    }
}
