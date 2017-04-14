<?php

declare(strict_types=1);


namespace Infection\Mutant;

use Infection\Mutation;
use Infection\Visitor\MutatorVisitor;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

class MutantFileCreator
{
    private $tempDir;

    public function __construct($tempDir)
    {
        $this->tempDir = $tempDir;
    }

    public function create(Mutation $mutation) : string
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
        $mutatedStatements = $traverser->traverse($originalStatements);

        $mutatedCode = $prettyPrinter->prettyPrintFile($mutatedStatements);
        $mutatedFilePath = sprintf('%s/mutant.%s.infection.php', $this->tempDir, $mutation->getHash());

        echo $mutatedCode;

        file_put_contents($mutatedFilePath, $mutatedCode);

        return $mutatedFilePath;
    }
}