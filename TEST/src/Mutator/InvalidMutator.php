<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

use function array_keys;
use RuntimeException;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use Throwable;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class InvalidMutator extends RuntimeException
{
    private const GITHUB_BUG_LINK = 'https://github.com/infection/infection/issues/new?template=Bug_report.md';
    public static function create(string $filePath, string $mutatorName, Throwable $previous) : self
    {
        Assert::oneOf($mutatorName, array_keys(ProfileList::ALL_MUTATORS));
        return new self(sprintf(<<<'TXT'
Encountered an error with the "%s" mutator in the "%s" file. This is most likely a bug in Infection.
Please consider reporting this this in our issue tracker: %s
TXT
, $mutatorName, $filePath, self::GITHUB_BUG_LINK), 0, $previous);
    }
}
