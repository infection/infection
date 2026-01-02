<?php

declare(strict_types=1);

namespace Infection\Ast;

use Infection\TestFramework\Tracing\Trace\Trace;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Token;

final readonly class Ast
{
    /**
     * @param Stmt[] $initialStatements
     * @param Token[] $originalFileTokens
     * @param Node[] $nodes
     */
    public function __construct(
        public Trace $trace,
        public array $initialStatements,
        public array $originalFileTokens,
        public array $nodes,
    ) {
    }
}
