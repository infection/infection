<?php

declare(strict_types=1);


namespace Infection\Mutant\Generator;


use Infection\Mutator\Arithmetic\Minus;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\ReturnValue\FunctionCall;
use Infection\Mutator\ReturnValue\IntegerNegotiation;
use Infection\Visitor\InsideFunctionDetectorVisitor;
use Infection\Visitor\MutationsCollectorVisitor;
use Infection\Visitor\MutatorVisitor;
use Infection\Visitor\ParentConnectorVisitor;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class MutationsGenerator
{
    private $srcDir;

    public function __construct(string $srcDir)
    {
        $this->srcDir = $srcDir;
    }

    public function generate() : array
    {
        $files = $this->getSrcFiles();
        $allFilesMutations = [];

        foreach ($files as $file) {
            $allFilesMutations = array_merge($allFilesMutations, $this->getMutationsFromFile($file));
        }

        return $allFilesMutations;
    }

    /**
     * @return Finder
     * @throws \InvalidArgumentException
     */
    private function getSrcFiles(): Finder
    {
        $finder = new Finder();
        $finder->files()->in($this->srcDir);

//        $finder->files()->name('*.php');
        $finder->files()->name('Example*.php');
//        $finder->files()->name('Plus.php');

        return $finder;
    }

    private function getMutationsFromFile(SplFileInfo $file) : array
    {
        $lexer = new Lexer([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos'
            ]
        ]);
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);
        $traverser = new NodeTraverser();
        $mutators = $this->getMutators();

        $mutationsCollectorVisitor = new MutationsCollectorVisitor($mutators, $file->getRealPath());

        $traverser->addVisitor(new ParentConnectorVisitor());
        $traverser->addVisitor(new InsideFunctionDetectorVisitor());
        $traverser->addVisitor($mutationsCollectorVisitor);

        $originalCode = $file->getContents();

        $initialStatements = $parser->parse($originalCode);

        $traverser->traverse($initialStatements);

        return $mutationsCollectorVisitor->getMutations();
    }

    private function getMutators() : array
    {
        return [
            new Plus(),
            new Minus(),
            new FunctionCall(),
            new IntegerNegotiation(),
        ];
    }
}