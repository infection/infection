<?php
/**
 * Copyright © 2018 Tobias Stadler
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Codeception\Config\Builder;

use Infection\TestFramework\Config\InitialConfigBuilder as ConfigBuilder;

class InitialConfigBuilder implements ConfigBuilder
{
    /**
     * @var string
     */
    private $originalConfigPath;

    public function __construct(string $originalConfigPath)
    {
        $this->originalConfigPath = $originalConfigPath;
    }

    public function build(): string
    {
        return $this->originalConfigPath;
    }
}
