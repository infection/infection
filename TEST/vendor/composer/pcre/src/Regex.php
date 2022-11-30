<?php

namespace _HumbugBox9658796bb9f0\Composer\Pcre;

class Regex
{
    public static function isMatch($pattern, $subject, $offset = 0)
    {
        return (bool) Preg::match($pattern, $subject, $matches, 0, $offset);
    }
    public static function match($pattern, $subject, $flags = 0, $offset = 0)
    {
        if (($flags & \PREG_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_OFFSET_CAPTURE is not supported as it changes the return type, use matchWithOffsets() instead');
        }
        $count = Preg::match($pattern, $subject, $matches, $flags, $offset);
        return new MatchResult($count, $matches);
    }
    public static function matchWithOffsets($pattern, $subject, $flags = 0, $offset = 0)
    {
        $count = Preg::matchWithOffsets($pattern, $subject, $matches, $flags, $offset);
        return new MatchWithOffsetsResult($count, $matches);
    }
    public static function matchAll($pattern, $subject, $flags = 0, $offset = 0)
    {
        if (($flags & \PREG_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_OFFSET_CAPTURE is not supported as it changes the return type, use matchAllWithOffsets() instead');
        }
        if (($flags & \PREG_SET_ORDER) !== 0) {
            throw new \InvalidArgumentException('PREG_SET_ORDER is not supported as it changes the return type');
        }
        $count = Preg::matchAll($pattern, $subject, $matches, $flags, $offset);
        return new MatchAllResult($count, $matches);
    }
    public static function matchAllWithOffsets($pattern, $subject, $flags = 0, $offset = 0)
    {
        $count = Preg::matchAllWithOffsets($pattern, $subject, $matches, $flags, $offset);
        return new MatchAllWithOffsetsResult($count, $matches);
    }
    public static function replace($pattern, $replacement, $subject, $limit = -1)
    {
        $result = Preg::replace($pattern, $replacement, $subject, $limit, $count);
        return new ReplaceResult($count, $result);
    }
    public static function replaceCallback($pattern, $replacement, $subject, $limit = -1, $flags = 0)
    {
        $result = Preg::replaceCallback($pattern, $replacement, $subject, $limit, $count, $flags);
        return new ReplaceResult($count, $result);
    }
    public static function replaceCallbackArray($pattern, $subject, $limit = -1, $flags = 0)
    {
        $result = Preg::replaceCallbackArray($pattern, $subject, $limit, $count, $flags);
        return new ReplaceResult($count, $result);
    }
}
