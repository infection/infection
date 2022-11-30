<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Boolean;

use function array_key_exists;
use _HumbugBox9658796bb9f0\Infection\Mutator\ConfigurableMutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetConfigClassName;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ParentConnector;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use function _HumbugBox9658796bb9f0\Safe\array_flip;
/**
@implements
*/
final class TrueValue implements ConfigurableMutator
{
    use GetConfigClassName;
    use GetMutatorName;
    private array $allowedFunctions;
    public function __construct(TrueValueConfig $config)
    {
        $this->allowedFunctions = array_flip($config->getAllowedFunctions());
    }
    public static function getDefinition() : ?Definition
    {
        return new Definition('Replaces a boolean literal (`true`) with its opposite value (`false`). ', MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $a = true;
+ $a = false;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Expr\ConstFetch(new Node\Name('false')));
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Expr\ConstFetch) {
            return \false;
        }
        if ($node->name->toLowerString() !== 'true') {
            return \false;
        }
        $parentNode = ParentConnector::findParent($node);
        $grandParentNode = $parentNode !== null ? ParentConnector::findParent($parentNode) : null;
        if (!$grandParentNode instanceof Node\Expr\FuncCall || !$grandParentNode->name instanceof Node\Name) {
            return \true;
        }
        $functionName = $grandParentNode->name->toLowerString();
        return array_key_exists($functionName, $this->allowedFunctions);
    }
}
