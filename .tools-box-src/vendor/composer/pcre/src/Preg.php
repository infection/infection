<?php

namespace _HumbugBoxb47773b41c19\Composer\Pcre;

class Preg
{
    public const ARRAY_MSG = '$subject as an array is not supported. You can use \'foreach\' instead.';
    public const INVALID_TYPE_MSG = '$subject must be a string, %s given.';
    /**
    @param-out
    */
    public static function match(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0) : int
    {
        self::checkOffsetCapture($flags, 'matchWithOffsets');
        $result = \preg_match($pattern, $subject, $matches, $flags | \PREG_UNMATCHED_AS_NULL, $offset);
        if ($result === \false) {
            throw PcreException::fromFunction('preg_match', $pattern);
        }
        return $result;
    }
    /**
    @param-out
    */
    public static function matchStrictGroups(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0) : int
    {
        $result = self::match($pattern, $subject, $matchesInternal, $flags, $offset);
        $matches = self::enforceNonNullMatches($pattern, $matchesInternal, 'match');
        return $result;
    }
    /**
    @param-out
    */
    public static function matchWithOffsets(string $pattern, string $subject, ?array &$matches, int $flags = 0, int $offset = 0) : int
    {
        $result = \preg_match($pattern, $subject, $matches, $flags | \PREG_UNMATCHED_AS_NULL | \PREG_OFFSET_CAPTURE, $offset);
        if ($result === \false) {
            throw PcreException::fromFunction('preg_match', $pattern);
        }
        return $result;
    }
    /**
    @param-out
    */
    public static function matchAll(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0) : int
    {
        self::checkOffsetCapture($flags, 'matchAllWithOffsets');
        self::checkSetOrder($flags);
        $result = \preg_match_all($pattern, $subject, $matches, $flags | \PREG_UNMATCHED_AS_NULL, $offset);
        if (!\is_int($result)) {
            throw PcreException::fromFunction('preg_match_all', $pattern);
        }
        return $result;
    }
    /**
    @param-out
    */
    public static function matchAllStrictGroups(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0) : int
    {
        $result = self::matchAll($pattern, $subject, $matchesInternal, $flags, $offset);
        $matches = self::enforceNonNullMatchAll($pattern, $matchesInternal, 'matchAll');
        return $result;
    }
    /**
    @phpstan-param
    */
    public static function matchAllWithOffsets(string $pattern, string $subject, ?array &$matches, int $flags = 0, int $offset = 0) : int
    {
        self::checkSetOrder($flags);
        $result = \preg_match_all($pattern, $subject, $matches, $flags | \PREG_UNMATCHED_AS_NULL | \PREG_OFFSET_CAPTURE, $offset);
        if (!\is_int($result)) {
            throw PcreException::fromFunction('preg_match_all', $pattern);
        }
        return $result;
    }
    /**
    @param-out
    */
    public static function replace($pattern, $replacement, $subject, int $limit = -1, int &$count = null) : string
    {
        if (!\is_scalar($subject)) {
            if (\is_array($subject)) {
                throw new \InvalidArgumentException(static::ARRAY_MSG);
            }
            throw new \TypeError(\sprintf(static::INVALID_TYPE_MSG, \gettype($subject)));
        }
        $result = \preg_replace($pattern, $replacement, $subject, $limit, $count);
        if ($result === null) {
            throw PcreException::fromFunction('preg_replace', $pattern);
        }
        return $result;
    }
    /**
    @param-out
    */
    public static function replaceCallback($pattern, callable $replacement, $subject, int $limit = -1, int &$count = null, int $flags = 0) : string
    {
        if (!\is_scalar($subject)) {
            if (\is_array($subject)) {
                throw new \InvalidArgumentException(static::ARRAY_MSG);
            }
            throw new \TypeError(\sprintf(static::INVALID_TYPE_MSG, \gettype($subject)));
        }
        $result = \preg_replace_callback($pattern, $replacement, $subject, $limit, $count, $flags | \PREG_UNMATCHED_AS_NULL);
        if ($result === null) {
            throw PcreException::fromFunction('preg_replace_callback', $pattern);
        }
        return $result;
    }
    /**
    @param-out
    */
    public static function replaceCallbackStrictGroups(string $pattern, callable $replacement, $subject, int $limit = -1, int &$count = null, int $flags = 0) : string
    {
        return self::replaceCallback($pattern, function (array $matches) use($pattern, $replacement) {
            return $replacement(self::enforceNonNullMatches($pattern, $matches, 'replaceCallback'));
        }, $subject, $limit, $count, $flags);
    }
    /**
    @param-out
    */
    public static function replaceCallbackArray(array $pattern, $subject, int $limit = -1, int &$count = null, int $flags = 0) : string
    {
        if (!\is_scalar($subject)) {
            if (\is_array($subject)) {
                throw new \InvalidArgumentException(static::ARRAY_MSG);
            }
            throw new \TypeError(\sprintf(static::INVALID_TYPE_MSG, \gettype($subject)));
        }
        $result = \preg_replace_callback_array($pattern, $subject, $limit, $count, $flags | \PREG_UNMATCHED_AS_NULL);
        if ($result === null) {
            $pattern = \array_keys($pattern);
            throw PcreException::fromFunction('preg_replace_callback_array', $pattern);
        }
        return $result;
    }
    public static function split(string $pattern, string $subject, int $limit = -1, int $flags = 0) : array
    {
        if (($flags & \PREG_SPLIT_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_SPLIT_OFFSET_CAPTURE is not supported as it changes the type of $matches, use splitWithOffsets() instead');
        }
        $result = \preg_split($pattern, $subject, $limit, $flags);
        if ($result === \false) {
            throw PcreException::fromFunction('preg_split', $pattern);
        }
        return $result;
    }
    /**
    @phpstan-return
    */
    public static function splitWithOffsets(string $pattern, string $subject, int $limit = -1, int $flags = 0) : array
    {
        $result = \preg_split($pattern, $subject, $limit, $flags | \PREG_SPLIT_OFFSET_CAPTURE);
        if ($result === \false) {
            throw PcreException::fromFunction('preg_split', $pattern);
        }
        return $result;
    }
    /**
    @template
    */
    public static function grep(string $pattern, array $array, int $flags = 0) : array
    {
        $result = \preg_grep($pattern, $array, $flags);
        if ($result === \false) {
            throw PcreException::fromFunction('preg_grep', $pattern);
        }
        return $result;
    }
    /**
    @param-out
    */
    public static function isMatch(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0) : bool
    {
        return (bool) static::match($pattern, $subject, $matches, $flags, $offset);
    }
    /**
    @param-out
    */
    public static function isMatchStrictGroups(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0) : bool
    {
        return (bool) self::matchStrictGroups($pattern, $subject, $matches, $flags, $offset);
    }
    /**
    @param-out
    */
    public static function isMatchAll(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0) : bool
    {
        return (bool) static::matchAll($pattern, $subject, $matches, $flags, $offset);
    }
    /**
    @param-out
    */
    public static function isMatchAllStrictGroups(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0) : bool
    {
        return (bool) self::matchAllStrictGroups($pattern, $subject, $matches, $flags, $offset);
    }
    /**
    @param-out
    */
    public static function isMatchWithOffsets(string $pattern, string $subject, ?array &$matches, int $flags = 0, int $offset = 0) : bool
    {
        return (bool) static::matchWithOffsets($pattern, $subject, $matches, $flags, $offset);
    }
    /**
    @param-out
    */
    public static function isMatchAllWithOffsets(string $pattern, string $subject, ?array &$matches, int $flags = 0, int $offset = 0) : bool
    {
        return (bool) static::matchAllWithOffsets($pattern, $subject, $matches, $flags, $offset);
    }
    private static function checkOffsetCapture(int $flags, string $useFunctionName) : void
    {
        if (($flags & \PREG_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_OFFSET_CAPTURE is not supported as it changes the type of $matches, use ' . $useFunctionName . '() instead');
        }
    }
    private static function checkSetOrder(int $flags) : void
    {
        if (($flags & \PREG_SET_ORDER) !== 0) {
            throw new \InvalidArgumentException('PREG_SET_ORDER is not supported as it changes the type of $matches');
        }
    }
    private static function enforceNonNullMatches(string $pattern, array $matches, string $variantMethod)
    {
        foreach ($matches as $group => $match) {
            if (null === $match) {
                throw new UnexpectedNullMatchException('Pattern "' . $pattern . '" had an unexpected unmatched group "' . $group . '", make sure the pattern always matches or use ' . $variantMethod . '() instead.');
            }
        }
        return $matches;
    }
    private static function enforceNonNullMatchAll(string $pattern, array $matches, string $variantMethod)
    {
        foreach ($matches as $group => $groupMatches) {
            foreach ($groupMatches as $match) {
                if (null === $match) {
                    throw new UnexpectedNullMatchException('Pattern "' . $pattern . '" had an unexpected unmatched group "' . $group . '", make sure the pattern always matches or use ' . $variantMethod . '() instead.');
                }
            }
        }
        return $matches;
    }
}
