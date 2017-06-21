<?php

declare(strict_types=1);


namespace Infection\Mutant;

use Infection\Differ\Differ;
use Infection\Mutation;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\Visitor\MutatorVisitor;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

class MutantCreator
{
    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var Differ
     */
    private $differ;

    public function __construct(string $tempDir, Differ $differ)
    {
        $this->tempDir = $tempDir;
        $this->differ = $differ;
    }

    public function create(Mutation $mutation, CodeCoverageData $codeCoverageData) : Mutant
    {
        $lexer = new Lexer([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos'
            ]
        ]);
        $prettyPrinter = new Standard();
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);

        $traverser = new NodeTraverser();
        $visitor = new MutatorVisitor($mutation);

        $traverser->addVisitor($visitor);

        $originalStatements = $parser->parse(file_get_contents($mutation->getOriginalFilePath()));

        $originalPrettyPrintedFile = $prettyPrinter->prettyPrintFile($originalStatements);

        $mutatedStatements = $traverser->traverse($originalStatements);

        $mutatedCode = $prettyPrinter->prettyPrintFile($mutatedStatements);
        $mutatedFilePath = sprintf('%s/mutant.%s.infection.php', $this->tempDir, $mutation->getHash());

        $diff = $this->differ->diff($originalPrettyPrintedFile, $mutatedCode);

        file_put_contents($mutatedFilePath, $mutatedCode);

        $isCoveredByTest = $this->isCoveredByTest($mutation, $codeCoverageData);

        return new Mutant(
            $mutatedFilePath,
            $mutation,
            $diff,
            $isCoveredByTest,
            $codeCoverageData->getAllTestsFor($mutation->getOriginalFilePath(), $mutation->getAttributes()['startLine'])
        );
    }

    private function isCoveredByTest(Mutation $mutation, CodeCoverageData $codeCoverageData)
    {
        $line = $mutation->getAttributes()['startLine'];
        $filePath = $mutation->getOriginalFilePath();

        return $codeCoverageData->hasTestsOnLine($filePath, $line);
    }
}