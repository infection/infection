<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutant\Exception;

use Infection\Mutant\Exception\ParserException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ParserExceptionTest extends TestCase
{
    public function test_it_has_correct_error_message(): void
    {
        $file = $this->createMock(\SplFileInfo::class);
        $file->expects($this->once())
            ->method('getRealPath')
            ->willReturn('foo/bar/baz');
        $previous = new \Exception('Unintentional thing');

        $exception = ParserException::fromInvalidFile($file, $previous);

        $this->assertSame(
            'Unable to parse file "foo/bar/baz", most likely due to syntax errors.',
            $exception->getMessage()
        );
        $this->assertSame($previous,
            $exception->getPrevious()
        );
    }
}
