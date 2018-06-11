<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Util\Exception;

use Infection\Mutator\Util\Mutator;

/**
 * @internal
 */
final class MutatorException extends \Exception
{
    private $mutator;

    public static function couldNotMutate(Mutator $mutator, \Throwable $previous): self
    {
        $exception = new self($previous->getMessage(), $previous->getCode(), $previous);
        $exception->mutator = $mutator;

        return $exception;
    }

    public static function traverseErrorWithBetterMessage(\SplFileInfo $file, self $previous): self
    {
        return new self(
            sprintf(
                'Encountered an error with the "%s" mutator in the "%s" file. ' .
                'This is most likely a bug in infection, so please report this in our issue tracker.',
                $previous->getMutator()::getName(),
                $file->getRealPath()
            ),
            0,
            $previous->getPrevious()
        );
    }

    public function getMutator(): Mutator
    {
        return $this->mutator;
    }
}
