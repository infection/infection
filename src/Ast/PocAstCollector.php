<?php

declare(strict_types=1);

namespace Infection\Ast;

use Infection\PhpParser\FileParser;
use Infection\PhpParser\NodeTraverserFactory;
use PhpParser\Node;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
final readonly class PocAstCollector
{
    public function __construct(
        private FileParser $parser,
        private NodeTraverserFactory $traverserFactory,
    ) {
    }

    /**
     * @return iterable<Node[]>
     */
    public function collect(SplFileInfo $sourceFile): iterable
    {
        $statements = $this->parser->parse($sourceFile);

        yield $this->traverserFactory
            ->legacyCreate(Path::canonicalize($sourceFile->getPathname()))
            ->traverse($statements);
    }
}
