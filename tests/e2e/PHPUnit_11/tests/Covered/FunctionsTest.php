<?php

namespace Infection\E2ETests\PHPUnit_11\Tests\Covered;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\TestCase;

use function Infection\E2ETests\PHPUnit_11\Covered\formatName;

#[CoversFunction('Infection\E2ETests\PHPUnit_11\Covered\formatName')]
class FunctionsTest extends TestCase
{
    public function test_format_name_with_both_names(): void
    {
        $this->assertSame('John Doe', formatName('John', 'Doe'));
    }

    public function test_format_name_with_first_name_only(): void
    {
        $this->assertSame('John', formatName('John', ''));
    }

    public function test_format_name_with_last_name_only(): void
    {
        $this->assertSame('Doe', formatName('', 'Doe'));
    }

    public function test_format_name_with_no_names(): void
    {
        $this->assertSame('Anonymous', formatName('', ''));
    }
}
