<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator;

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

    abstract public function mutate(Node $node);

    abstract public function shouldMutate(Node $node): bool;

    public static function getName(): string
    {
        $parts = explode('\\', static::class);

        return implode('-', array_slice($parts, -2));
    }

    public function isIgnored(string $class, string $method): bool
    {
        return $this->config->isIgnored($class, $method);
    }
}
