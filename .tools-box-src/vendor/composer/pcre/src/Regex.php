<?php

namespace _HumbugBoxb47773b41c19\Composer\Pcre;

class Regex
{
    public static function isMatch(string $pattern, string $subject, int $offset = 0) : bool
    {
        return (bool) Preg::match($pattern, $subject, $matches, 0, $offset);
    }
    public static function match(string $pattern, string $subject, int $flags = 0, int $offset = 0) : MatchResult
    {
        self::checkOffsetCapture($flags, 'matchWithOffsets');
        $count = Preg::match($pattern, $subject, $matches, $flags, $offset);
        return new MatchResult($count, $matches);
    }
    public static function matchStrictGroups(string $pattern, string $subject, int $flags = 0, int $offset = 0) : MatchStrictGroupsResult
    {
        $count = Preg::matchStrictGroups($pattern, $subject, $matches, $flags, $offset);
        return new MatchStrictGroupsResult($count, $matches);
    }
    public static function matchWithOffsets(string $pattern, string $subject, int $flags = 0, int $offset = 0) : MatchWithOffsetsResult
    {
        $count = Preg::matchWithOffsets($pattern, $subject, $matches, $flags, $offset);
        return new MatchWithOffsetsResult($count, $matches);
    }
    public static function matchAll(string $pattern, string $subject, int $flags = 0, int $offset = 0) : MatchAllResult
    {
        self::checkOffsetCapture($flags, 'matchAllWithOffsets');
        self::checkSetOrder($flags);
        $count = Preg::matchAll($pattern, $subject, $matches, $flags, $offset);
        return new MatchAllResult($count, $matches);
    }
    public static function matchAllStrictGroups(string $pattern, string $subject, int $flags = 0, int $offset = 0) : MatchAllStrictGroupsResult
    {
        self::checkOffsetCapture($flags, 'matchAllWithOffsets');
        self::checkSetOrder($flags);
        $count = Preg::matchAllStrictGroups($pattern, $subject, $matches, $flags, $offset);
        return new MatchAllStrictGroupsResult($count, $matches);
    }
    public static function matchAllWithOffsets(string $pattern, string $subject, int $flags = 0, int $offset = 0) : MatchAllWithOffsetsResult
    {
        self::checkSetOrder($flags);
        $count = Preg::matchAllWithOffsets($pattern, $subject, $matches, $flags, $offset);
        return new MatchAllWithOffsetsResult($count, $matches);
    }
    public static function replace($pattern, $replacement, $subject, int $limit = -1) : ReplaceResult
    {
        $result = Preg::replace($pattern, $replacement, $subject, $limit, $count);
        return new ReplaceResult($count, $result);
    }
    public static function replaceCallback($pattern, callable $replacement, $subject, int $limit = -1, int $flags = 0) : ReplaceResult
    {
        $result = Preg::replaceCallback($pattern, $replacement, $subject, $limit, $count, $flags);
        return new ReplaceResult($count, $result);
    }
    public static function replaceCallbackStrictGroups($pattern, callable $replacement, $subject, int $limit = -1, int $flags = 0) : ReplaceResult
    {
        $result = Preg::replaceCallbackStrictGroups($pattern, $replacement, $subject, $limit, $count, $flags);
        return new ReplaceResult($count, $result);
    }
    public static function replaceCallbackArray(array $pattern, $subject, int $limit = -1, int $flags = 0) : ReplaceResult
    {
        $result = Preg::replaceCallbackArray($pattern, $subject, $limit, $count, $flags);
        return new ReplaceResult($count, $result);
    }
    private static function checkOffsetCapture(int $flags, string $useFunctionName) : void
    {
        if (($flags & \PREG_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_OFFSET_CAPTURE is not supported as it changes the return type, use ' . $useFunctionName . '() instead');
        }
    }
    private static function checkSetOrder(int $flags) : void
    {
        if (($flags & \PREG_SET_ORDER) !== 0) {
            throw new \InvalidArgumentException('PREG_SET_ORDER is not supported as it changes the return type');
        }
    }
}
