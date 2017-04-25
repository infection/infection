<?php

declare(strict_types=1);


namespace Infection\Mutant\Generator;


use Infection\Mutator\Arithmetic\Minus;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Boolean\FalseValue;
use Infection\Mutator\Boolean\LogicalAnd;
use Infection\Mutator\Boolean\LogicalNot;
use Infection\Mutator\Boolean\LogicalOr;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\ConditionalBoundary\GreaterThan;
use Infection\Mutator\ConditionalBoundary\LessThan;
use Infection\Mutator\ConditionalNegotiation\Identical;
use Infection\Mutator\ConditionalNegotiation\NotIdentical;
use Infection\Mutator\ReturnValue\FunctionCall;
use Infection\Mutator\ReturnValue\IntegerNegotiation;
use Infection\TestFramework\Coverage\CodeCoverageData;
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
    private $srcDir;

    /**
     * @var CodeCoverageData
     */
    private $codeCoverageData;

    public function __construct(string $srcDir, CodeCoverageData $codeCoverageData)
    {
        $this->srcDir = $srcDir;
        $this->codeCoverageData = $codeCoverageData;
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
     */
    private function getSrcFiles(string $filter = ''): Finder
    {
        $finder = new Finder();
        $finder->files()->in($this->srcDir);

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
        return [
            // Boolean
            new LogicalAnd(),
            new LogicalOr(),
            new LogicalNot(),
            new FalseValue(),
            new TrueValue(),

            // Arithmetic
            new Plus(),
            new Minus(),

            // Return Value
            new FunctionCall(),
            new IntegerNegotiation(),

            // Conditional Boundary
            new LessThan(),
            new GreaterThan(),

            // Conditional Negotiation
            new Identical(),
            new NotIdentical(),
        ];
    }
}