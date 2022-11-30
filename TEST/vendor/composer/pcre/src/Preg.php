<?php

namespace _HumbugBox9658796bb9f0\Composer\Pcre;

class Preg
{
    const ARRAY_MSG = '$subject as an array is not supported. You can use \'foreach\' instead.';
    public static function match($pattern, $subject, &$matches = null, $flags = 0, $offset = 0)
    {
        if (($flags & \PREG_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_OFFSET_CAPTURE is not supported as it changes the type of $matches, use matchWithOffsets() instead');
        }
        $result = \preg_match($pattern, $subject, $matches, $flags, $offset);
        if ($result === \false) {
            throw PcreException::fromFunction('preg_match', $pattern);
        }
        return $result;
    }
    /**
    @phpstan-param
    */
    public static function matchWithOffsets($pattern, $subject, &$matches, $flags = 0, $offset = 0)
    {
        $result = \preg_match($pattern, $subject, $matches, $flags | \PREG_OFFSET_CAPTURE, $offset);
        if ($result === \false) {
            throw PcreException::fromFunction('preg_match', $pattern);
        }
        return $result;
    }
    public static function matchAll($pattern, $subject, &$matches = null, $flags = 0, $offset = 0)
    {
        if (($flags & \PREG_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_OFFSET_CAPTURE is not supported as it changes the type of $matches, use matchAllWithOffsets() instead');
        }
        if (($flags & \PREG_SET_ORDER) !== 0) {
            throw new \InvalidArgumentException('PREG_SET_ORDER is not supported as it changes the type of $matches');
        }
        $result = \preg_match_all($pattern, $subject, $matches, $flags, $offset);
        if ($result === \false || $result === null) {
            throw PcreException::fromFunction('preg_match_all', $pattern);
        }
        return $result;
    }
    /**
    @phpstan-param
    */
    public static function matchAllWithOffsets($pattern, $subject, &$matches, $flags = 0, $offset = 0)
    {
        $result = \preg_match_all($pattern, $subject, $matches, $flags | \PREG_OFFSET_CAPTURE, $offset);
        if ($result === \false || $result === null) {
            throw PcreException::fromFunction('preg_match_all', $pattern);
        }
        return $result;
    }
    public static function replace($pattern, $replacement, $subject, $limit = -1, &$count = null)
    {
        if (\is_array($subject)) {
            throw new \InvalidArgumentException(static::ARRAY_MSG);
        }
        $result = \preg_replace($pattern, $replacement, $subject, $limit, $count);
        if ($result === null) {
            throw PcreException::fromFunction('preg_replace', $pattern);
        }
        return $result;
    }
    public static function replaceCallback($pattern, $replacement, $subject, $limit = -1, &$count = null, $flags = 0)
    {
        if (\is_array($subject)) {
            throw new \InvalidArgumentException(static::ARRAY_MSG);
        }
        if (\PHP_VERSION_ID >= 70400) {
            $result = \preg_replace_callback($pattern, $replacement, $subject, $limit, $count, $flags);
        } else {
            $result = \preg_replace_callback($pattern, $replacement, $subject, $limit, $count);
        }
        if ($result === null) {
            throw PcreException::fromFunction('preg_replace_callback', $pattern);
        }
        return $result;
    }
    public static function replaceCallbackArray(array $pattern, $subject, $limit = -1, &$count = null, $flags = 0)
    {
        if (\is_array($subject)) {
            throw new \InvalidArgumentException(static::ARRAY_MSG);
        }
        if (\PHP_VERSION_ID >= 70400) {
            $result = \preg_replace_callback_array($pattern, $subject, $limit, $count, $flags);
        } else {
            $result = \preg_replace_callback_array($pattern, $subject, $limit, $count);
        }
        if ($result === null) {
            $pattern = \array_keys($pattern);
            throw PcreException::fromFunction('preg_replace_callback_array', $pattern);
        }
        return $result;
    }
    public static function split($pattern, $subject, $limit = -1, $flags = 0)
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
    public static function splitWithOffsets($pattern, $subject, $limit = -1, $flags = 0)
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
    public static function grep($pattern, array $array, $flags = 0)
    {
        $result = \preg_grep($pattern, $array, $flags);
        if ($result === \false) {
            throw PcreException::fromFunction('preg_grep', $pattern);
        }
        return $result;
    }
    public static function isMatch($pattern, $subject, &$matches = null, $flags = 0, $offset = 0)
    {
        return (bool) static::match($pattern, $subject, $matches, $flags, $offset);
    }
    public static function isMatchAll($pattern, $subject, &$matches = null, $flags = 0, $offset = 0)
    {
        return (bool) static::matchAll($pattern, $subject, $matches, $flags, $offset);
    }
    /**
    @phpstan-param
    */
    public static function isMatchWithOffsets($pattern, $subject, &$matches, $flags = 0, $offset = 0)
    {
        return (bool) static::matchWithOffsets($pattern, $subject, $matches, $flags, $offset);
    }
    /**
    @phpstan-param
    */
    public static function isMatchAllWithOffsets($pattern, $subject, &$matches, $flags = 0, $offset = 0)
    {
        return (bool) static::matchAllWithOffsets($pattern, $subject, $matches, $flags, $offset);
    }
}
