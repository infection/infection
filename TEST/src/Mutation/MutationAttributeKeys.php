<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutation;

use _HumbugBox9658796bb9f0\Infection\CannotBeInstantiated;
final class MutationAttributeKeys
{
    use CannotBeInstantiated;
    public const START_LINE = 'startLine';
    public const END_LINE = 'endLine';
    public const START_TOKEN_POS = 'startTokenPos';
    public const END_TOKEN_POS = 'endTokenPos';
    public const START_FILE_POS = 'startFilePos';
    public const END_FILE_POS = 'endFilePos';
    public const ALL = [self::START_LINE, self::END_LINE, self::START_TOKEN_POS, self::END_TOKEN_POS, self::START_FILE_POS, self::END_FILE_POS];
}
