<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Removal;

use function in_array;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class FunctionCallRemoval implements Mutator
{
    use GetMutatorName;
    private array $doNotRemoveFunctions = ['assert', 'closedir', 'curl_close', 'curl_multi_close', 'fclose', 'mysqli_close', 'mysqli_free_result', 'openssl_free_key', 'socket_close'];
    public static function getDefinition() : ?Definition
    {
        return new Definition('Removes the function call.', MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- fooBar();
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Stmt\Nop());
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Stmt\Expression) {
            return \false;
        }
        if (!$node->expr instanceof Node\Expr\FuncCall) {
            return \false;
        }
        $name = $node->expr->name;
        if (!$name instanceof Node\Name) {
            return \true;
        }
        return !in_array($name->toLowerString(), $this->doNotRemoveFunctions, \true);
    }
}
