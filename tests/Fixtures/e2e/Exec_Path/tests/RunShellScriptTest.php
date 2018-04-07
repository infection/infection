<?php

namespace Namespace_\Test;

use Namespace_\RunShellScript;
use PHPUnit\Framework\TestCase;

class RunShellScriptTest extends TestCase
{
    public function test_hello()
    {
    	$runner = new RunShellScript();
    	$this->assertSame('Program finished with flying colors!', $runner->hello());
    }
}
