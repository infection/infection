<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Util;

use Infection\Visitor\ReflectionVisitor;
use PhpParser\Node;

abstract class Mutator
{
    /**
     * @var MutatorConfig
     */
    private $config;

    public function __construct(MutatorConfig $config)
    {
        $this->config = $config;
    }

    abstract public function mutate(Node $node): iterable;

    abstract protected function mutatesNode(Node $node): bool;

    final public function shouldMutate(Node $node): bool
    {
        if (!$this->mutatesNode($node)) {
            return false;
        }

        $reflectionClass = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY, false);

        if (!$reflectionClass) {
            return true;
        }

        return !$this->config->isIgnored($reflectionClass->getName(), $node->getAttribute(ReflectionVisitor::FUNCTION_NAME, ''));
    }

    final public static function getName(): string
    {
        $parts = explode('\\', static::class);

        return end($parts);
    }
}
