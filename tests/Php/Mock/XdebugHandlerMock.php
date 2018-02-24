<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Php\Mock;

use Infection\Php\ConfigBuilder;
use Infection\Php\XdebugHandler;

class XdebugHandlerMock extends XdebugHandler
{
    public $restarted;

    public function __construct($isLoaded = null)
    {
        parent::__construct(new ConfigBuilder(sys_get_temp_dir()));

        $isLoaded = null === $isLoaded ? true : $isLoaded;
        $class = new \ReflectionClass(get_parent_class($this));

        $prop = $class->getProperty('isLoaded');
        $prop->setAccessible(true);
        $prop->setValue($this, $isLoaded);

        $this->restarted = false;
    }

    protected function restart(string $command)
    {
        $this->restarted = true;
    }
}
