<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Regex;

use Generator;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use function _HumbugBox9658796bb9f0\Safe\preg_match;
use function str_replace;
use function str_split;
final class PregMatchRemoveFlags extends AbstractPregMatch
{
    public const ANALYSE_REGEX = '/^([^\\w\\s\\\\])(.*)([^\\w\\s\\\\])([gmixXsuUAJD]*)$/';
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Removes one by one flags ("gmixXsuUAJD") in a Regular Expression in `preg_match()` function. For example:

```php
preg_match('/^test$/ig', $string);
```

Will be mutated to:

```php
preg_match('/^test/i', $string);
```

and

```php
preg_match('/^test/g', $string);
```

TXT
, MutatorCategory::SEMANTIC_REDUCTION, 'In order to kill this Mutant, write tests that cover every single flag used in a Regular Expression', <<<'DIFF'
- preg_match('/^test$/ig', $string);
# Mutation 1
+ preg_match('/^test$/i', $string);
# Mutation 2
+ preg_match('/^test$/g', $string);

DIFF
);
    }
    protected function isProperRegexToMutate(string $regex) : bool
    {
        preg_match(self::ANALYSE_REGEX, $regex, $matches);
        return ($matches[4] ?? null) !== '';
    }
    /**
    @psalm-mutation-free
    */
    protected function mutateRegex(string $regex) : Generator
    {
        preg_match(self::ANALYSE_REGEX, $regex, $matches);
        $delimiter = $matches[1] ?? '';
        $regexBody = $matches[2] ?? '';
        $flags = $matches[4] ?? '';
        foreach (str_split($flags) as $flag) {
            (yield $delimiter . $regexBody . $delimiter . str_replace($flag, '', $flags));
        }
    }
}
