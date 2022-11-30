<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Input;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
class StringInput extends ArgvInput
{
    public const REGEX_STRING = '([^\\s]+?)(?:\\s|(?<!\\\\)"|(?<!\\\\)\'|$)';
    public const REGEX_UNQUOTED_STRING = '([^\\s\\\\]+?)';
    public const REGEX_QUOTED_STRING = '(?:"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\')';
    public function __construct(string $input)
    {
        parent::__construct([]);
        $this->setTokens($this->tokenize($input));
    }
    private function tokenize(string $input) : array
    {
        $tokens = [];
        $length = \strlen($input);
        $cursor = 0;
        $token = null;
        while ($cursor < $length) {
            if ('\\' === $input[$cursor]) {
                $token .= $input[++$cursor] ?? '';
                ++$cursor;
                continue;
            }
            if (\preg_match('/\\s+/A', $input, $match, 0, $cursor)) {
                if (null !== $token) {
                    $tokens[] = $token;
                    $token = null;
                }
            } elseif (\preg_match('/([^="\'\\s]+?)(=?)(' . self::REGEX_QUOTED_STRING . '+)/A', $input, $match, 0, $cursor)) {
                $token .= $match[1] . $match[2] . \stripcslashes(\str_replace(['"\'', '\'"', '\'\'', '""'], '', \substr($match[3], 1, -1)));
            } elseif (\preg_match('/' . self::REGEX_QUOTED_STRING . '/A', $input, $match, 0, $cursor)) {
                $token .= \stripcslashes(\substr($match[0], 1, -1));
            } elseif (\preg_match('/' . self::REGEX_UNQUOTED_STRING . '/A', $input, $match, 0, $cursor)) {
                $token .= $match[1];
            } else {
                throw new InvalidArgumentException(\sprintf('Unable to parse input near "... %s ...".', \substr($input, $cursor, 10)));
            }
            $cursor += \strlen($match[0]);
        }
        if (null !== $token) {
            $tokens[] = $token;
        }
        return $tokens;
    }
}
