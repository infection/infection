<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Regex;

use Generator;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use function _HumbugBox9658796bb9f0\Safe\preg_match;
final class PregMatchRemoveCaret extends AbstractPregMatch
{
    public const ANALYSE_REGEX = '/^([^\\w\\s\\\\])([\\^]?)([^\\^]*)\\1([gmixXsuUAJD]*)$/';
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Removes a "^" character from a regular expression in `preg_match()`. For example:

```php
preg_match('/^test/', $string);
```

Will be mutated to:

```php
preg_match('/test/', $string);
```

TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- preg_match('/^test/', $string);
+ preg_match('/test/', $string);
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    protected function mutateRegex(string $regex) : Generator
    {
        preg_match(self::ANALYSE_REGEX, $regex, $matches);
        $delimiter = $matches[1] ?? '';
        $regexBody = $matches[3] ?? '';
        $flags = $matches[4] ?? '';
        (yield $delimiter . $regexBody . $delimiter . $flags);
    }
    protected function isProperRegexToMutate(string $regex) : bool
    {
        preg_match(self::ANALYSE_REGEX, $regex, $matches);
        return ($matches[2] ?? null) === '^';
    }
}
