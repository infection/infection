<?php

declare(strict_types=1);

namespace Infection\Ast;

use Infection\TestFramework\Tracing\Trace\Trace;
use PhpParser\Node;

final readonly class Ast
{
    /**
     * @param Node[] $originalFileTokens
     * @param Node[] $nodes
     */
    public function __construct(
        public Trace $trace,
        public array $originalFileTokens,
        public array $nodes,
    ) {
    }
}
