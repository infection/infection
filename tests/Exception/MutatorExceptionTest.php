<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Exception;

use Infection\Exception\MutatorException;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Util\MutatorConfig;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MutatorExceptionTest extends TestCase
{
    public function test_it_has_correct_user_facing_message()
    {
        $mutator = new Plus(new MutatorConfig([]));
        $original = new \Exception();

        $exception = MutatorException::internalErrorWhileTraversing('foo/bar/baz', $mutator, $original);

        $this->assertSame(
            'Encountered an error with the "Plus" mutator in the "foo/bar/baz" file. ' .
            'This is most likely a bug in infection, so please report this in our issue tracker.',
            $exception->getMessage()
        );
        $this->assertSame($original, $exception->getPrevious());
    }
}
