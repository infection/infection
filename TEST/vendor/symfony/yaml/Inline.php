<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Yaml;

use _HumbugBox9658796bb9f0\Symfony\Component\Yaml\Exception\DumpException;
use _HumbugBox9658796bb9f0\Symfony\Component\Yaml\Exception\ParseException;
use _HumbugBox9658796bb9f0\Symfony\Component\Yaml\Tag\TaggedValue;
class Inline
{
    public const REGEX_QUOTED_STRING = '(?:"([^"\\\\]*+(?:\\\\.[^"\\\\]*+)*+)"|\'([^\']*+(?:\'\'[^\']*+)*+)\')';
    public static $parsedLineNumber = -1;
    public static $parsedFilename;
    private static $exceptionOnInvalidType = \false;
    private static $objectSupport = \false;
    private static $objectForMap = \false;
    private static $constantSupport = \false;
    public static function initialize(int $flags, int $parsedLineNumber = null, string $parsedFilename = null)
    {
        self::$exceptionOnInvalidType = (bool) (Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE & $flags);
        self::$objectSupport = (bool) (Yaml::PARSE_OBJECT & $flags);
        self::$objectForMap = (bool) (Yaml::PARSE_OBJECT_FOR_MAP & $flags);
        self::$constantSupport = (bool) (Yaml::PARSE_CONSTANT & $flags);
        self::$parsedFilename = $parsedFilename;
        if (null !== $parsedLineNumber) {
            self::$parsedLineNumber = $parsedLineNumber;
        }
    }
    public static function parse(string $value = null, int $flags = 0, array &$references = [])
    {
        self::initialize($flags);
        $value = \trim($value);
        if ('' === $value) {
            return '';
        }
        if (2 & (int) \ini_get('mbstring.func_overload')) {
            $mbEncoding = \mb_internal_encoding();
            \mb_internal_encoding('ASCII');
        }
        try {
            $i = 0;
            $tag = self::parseTag($value, $i, $flags);
            switch ($value[$i]) {
                case '[':
                    $result = self::parseSequence($value, $flags, $i, $references);
                    ++$i;
                    break;
                case '{':
                    $result = self::parseMapping($value, $flags, $i, $references);
                    ++$i;
                    break;
                default:
                    $result = self::parseScalar($value, $flags, null, $i, null === $tag, $references);
            }
            if (\preg_replace('/\\s*#.*$/A', '', \substr($value, $i))) {
                throw new ParseException(\sprintf('Unexpected characters near "%s".', \substr($value, $i)), self::$parsedLineNumber + 1, $value, self::$parsedFilename);
            }
            if (null !== $tag && '' !== $tag) {
                return new TaggedValue($tag, $result);
            }
            return $result;
        } finally {
            if (isset($mbEncoding)) {
                \mb_internal_encoding($mbEncoding);
            }
        }
    }
    public static function dump($value, int $flags = 0) : string
    {
        switch (\true) {
            case \is_resource($value):
                if (Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE & $flags) {
                    throw new DumpException(\sprintf('Unable to dump PHP resources in a YAML file ("%s").', \get_resource_type($value)));
                }
                return self::dumpNull($flags);
            case $value instanceof \DateTimeInterface:
                return $value->format('c');
            case $value instanceof \UnitEnum:
                return \sprintf('!php/const %s::%s', \get_class($value), $value->name);
            case \is_object($value):
                if ($value instanceof TaggedValue) {
                    return '!' . $value->getTag() . ' ' . self::dump($value->getValue(), $flags);
                }
                if (Yaml::DUMP_OBJECT & $flags) {
                    return '!php/object ' . self::dump(\serialize($value));
                }
                if (Yaml::DUMP_OBJECT_AS_MAP & $flags && ($value instanceof \stdClass || $value instanceof \ArrayObject)) {
                    $output = [];
                    foreach ($value as $key => $val) {
                        $output[] = \sprintf('%s: %s', self::dump($key, $flags), self::dump($val, $flags));
                    }
                    return \sprintf('{ %s }', \implode(', ', $output));
                }
                if (Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE & $flags) {
                    throw new DumpException('Object support when dumping a YAML file has been disabled.');
                }
                return self::dumpNull($flags);
            case \is_array($value):
                return self::dumpArray($value, $flags);
            case null === $value:
                return self::dumpNull($flags);
            case \true === $value:
                return 'true';
            case \false === $value:
                return 'false';
            case \is_int($value):
                return $value;
            case \is_numeric($value) && \false === \strpbrk($value, "\f\n\r\t\v"):
                $locale = \setlocale(\LC_NUMERIC, 0);
                if (\false !== $locale) {
                    \setlocale(\LC_NUMERIC, 'C');
                }
                if (\is_float($value)) {
                    $repr = (string) $value;
                    if (\is_infinite($value)) {
                        $repr = \str_ireplace('INF', '.Inf', $repr);
                    } elseif (\floor($value) == $value && $repr == $value) {
                        if (\false === \strpos($repr, 'E')) {
                            $repr = $repr . '.0';
                        }
                    }
                } else {
                    $repr = \is_string($value) ? "'{$value}'" : (string) $value;
                }
                if (\false !== $locale) {
                    \setlocale(\LC_NUMERIC, $locale);
                }
                return $repr;
            case '' == $value:
                return "''";
            case self::isBinaryString($value):
                return '!!binary ' . \base64_encode($value);
            case Escaper::requiresDoubleQuoting($value):
                return Escaper::escapeWithDoubleQuotes($value);
            case Escaper::requiresSingleQuoting($value):
            case Parser::preg_match('{^[0-9]+[_0-9]*$}', $value):
            case Parser::preg_match(self::getHexRegex(), $value):
            case Parser::preg_match(self::getTimestampRegex(), $value):
                return Escaper::escapeWithSingleQuotes($value);
            default:
                return $value;
        }
    }
    public static function isHash($value) : bool
    {
        if ($value instanceof \stdClass || $value instanceof \ArrayObject) {
            return \true;
        }
        $expectedKey = 0;
        foreach ($value as $key => $val) {
            if ($key !== $expectedKey++) {
                return \true;
            }
        }
        return \false;
    }
    private static function dumpArray(array $value, int $flags) : string
    {
        if (($value || Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE & $flags) && !self::isHash($value)) {
            $output = [];
            foreach ($value as $val) {
                $output[] = self::dump($val, $flags);
            }
            return \sprintf('[%s]', \implode(', ', $output));
        }
        $output = [];
        foreach ($value as $key => $val) {
            $output[] = \sprintf('%s: %s', self::dump($key, $flags), self::dump($val, $flags));
        }
        return \sprintf('{ %s }', \implode(', ', $output));
    }
    private static function dumpNull(int $flags) : string
    {
        if (Yaml::DUMP_NULL_AS_TILDE & $flags) {
            return '~';
        }
        return 'null';
    }
    public static function parseScalar(string $scalar, int $flags = 0, array $delimiters = null, int &$i = 0, bool $evaluate = \true, array &$references = [], bool &$isQuoted = null)
    {
        if (\in_array($scalar[$i], ['"', "'"], \true)) {
            $isQuoted = \true;
            $output = self::parseQuotedScalar($scalar, $i);
            if (null !== $delimiters) {
                $tmp = \ltrim(\substr($scalar, $i), " \n");
                if ('' === $tmp) {
                    throw new ParseException(\sprintf('Unexpected end of line, expected one of "%s".', \implode('', $delimiters)), self::$parsedLineNumber + 1, $scalar, self::$parsedFilename);
                }
                if (!\in_array($tmp[0], $delimiters)) {
                    throw new ParseException(\sprintf('Unexpected characters (%s).', \substr($scalar, $i)), self::$parsedLineNumber + 1, $scalar, self::$parsedFilename);
                }
            }
        } else {
            $isQuoted = \false;
            if (!$delimiters) {
                $output = \substr($scalar, $i);
                $i += \strlen($output);
                if (Parser::preg_match('/[ \\t]+#/', $output, $match, \PREG_OFFSET_CAPTURE)) {
                    $output = \substr($output, 0, $match[0][1]);
                }
            } elseif (Parser::preg_match('/^(.*?)(' . \implode('|', $delimiters) . ')/', \substr($scalar, $i), $match)) {
                $output = $match[1];
                $i += \strlen($output);
                $output = \trim($output);
            } else {
                throw new ParseException(\sprintf('Malformed inline YAML string: "%s".', $scalar), self::$parsedLineNumber + 1, null, self::$parsedFilename);
            }
            if ($output && ('@' === $output[0] || '`' === $output[0] || '|' === $output[0] || '>' === $output[0] || '%' === $output[0])) {
                throw new ParseException(\sprintf('The reserved indicator "%s" cannot start a plain scalar; you need to quote the scalar.', $output[0]), self::$parsedLineNumber + 1, $output, self::$parsedFilename);
            }
            if ($evaluate) {
                $output = self::evaluateScalar($output, $flags, $references, $isQuoted);
            }
        }
        return $output;
    }
    private static function parseQuotedScalar(string $scalar, int &$i = 0) : string
    {
        if (!Parser::preg_match('/' . self::REGEX_QUOTED_STRING . '/Au', \substr($scalar, $i), $match)) {
            throw new ParseException(\sprintf('Malformed inline YAML string: "%s".', \substr($scalar, $i)), self::$parsedLineNumber + 1, $scalar, self::$parsedFilename);
        }
        $output = \substr($match[0], 1, -1);
        $unescaper = new Unescaper();
        if ('"' == $scalar[$i]) {
            $output = $unescaper->unescapeDoubleQuotedString($output);
        } else {
            $output = $unescaper->unescapeSingleQuotedString($output);
        }
        $i += \strlen($match[0]);
        return $output;
    }
    private static function parseSequence(string $sequence, int $flags, int &$i = 0, array &$references = []) : array
    {
        $output = [];
        $len = \strlen($sequence);
        ++$i;
        while ($i < $len) {
            if (']' === $sequence[$i]) {
                return $output;
            }
            if (',' === $sequence[$i] || ' ' === $sequence[$i]) {
                ++$i;
                continue;
            }
            $tag = self::parseTag($sequence, $i, $flags);
            switch ($sequence[$i]) {
                case '[':
                    $value = self::parseSequence($sequence, $flags, $i, $references);
                    break;
                case '{':
                    $value = self::parseMapping($sequence, $flags, $i, $references);
                    break;
                default:
                    $value = self::parseScalar($sequence, $flags, [',', ']'], $i, null === $tag, $references, $isQuoted);
                    if (\is_string($value) && !$isQuoted && \false !== \strpos($value, ': ')) {
                        try {
                            $pos = 0;
                            $value = self::parseMapping('{' . $value . '}', $flags, $pos, $references);
                        } catch (\InvalidArgumentException $e) {
                        }
                    }
                    if (!$isQuoted && \is_string($value) && '' !== $value && '&' === $value[0] && Parser::preg_match(Parser::REFERENCE_PATTERN, $value, $matches)) {
                        $references[$matches['ref']] = $matches['value'];
                        $value = $matches['value'];
                    }
                    --$i;
            }
            if (null !== $tag && '' !== $tag) {
                $value = new TaggedValue($tag, $value);
            }
            $output[] = $value;
            ++$i;
        }
        throw new ParseException(\sprintf('Malformed inline YAML string: "%s".', $sequence), self::$parsedLineNumber + 1, null, self::$parsedFilename);
    }
    private static function parseMapping(string $mapping, int $flags, int &$i = 0, array &$references = [])
    {
        $output = [];
        $len = \strlen($mapping);
        ++$i;
        $allowOverwrite = \false;
        while ($i < $len) {
            switch ($mapping[$i]) {
                case ' ':
                case ',':
                case "\n":
                    ++$i;
                    continue 2;
                case '}':
                    if (self::$objectForMap) {
                        return (object) $output;
                    }
                    return $output;
            }
            $offsetBeforeKeyParsing = $i;
            $isKeyQuoted = \in_array($mapping[$i], ['"', "'"], \true);
            $key = self::parseScalar($mapping, $flags, [':', ' '], $i, \false);
            if ($offsetBeforeKeyParsing === $i) {
                throw new ParseException('Missing mapping key.', self::$parsedLineNumber + 1, $mapping);
            }
            if ('!php/const' === $key) {
                $key .= ' ' . self::parseScalar($mapping, $flags, [':'], $i, \false);
                $key = self::evaluateScalar($key, $flags);
            }
            if (\false === ($i = \strpos($mapping, ':', $i))) {
                break;
            }
            if (!$isKeyQuoted) {
                $evaluatedKey = self::evaluateScalar($key, $flags, $references);
                if ('' !== $key && $evaluatedKey !== $key && !\is_string($evaluatedKey) && !\is_int($evaluatedKey)) {
                    throw new ParseException('Implicit casting of incompatible mapping keys to strings is not supported. Quote your evaluable mapping keys instead.', self::$parsedLineNumber + 1, $mapping);
                }
            }
            if (!$isKeyQuoted && (!isset($mapping[$i + 1]) || !\in_array($mapping[$i + 1], [' ', ',', '[', ']', '{', '}', "\n"], \true))) {
                throw new ParseException('Colons must be followed by a space or an indication character (i.e. " ", ",", "[", "]", "{", "}").', self::$parsedLineNumber + 1, $mapping);
            }
            if ('<<' === $key) {
                $allowOverwrite = \true;
            }
            while ($i < $len) {
                if (':' === $mapping[$i] || ' ' === $mapping[$i] || "\n" === $mapping[$i]) {
                    ++$i;
                    continue;
                }
                $tag = self::parseTag($mapping, $i, $flags);
                switch ($mapping[$i]) {
                    case '[':
                        $value = self::parseSequence($mapping, $flags, $i, $references);
                        if ('<<' === $key) {
                            foreach ($value as $parsedValue) {
                                $output += $parsedValue;
                            }
                        } elseif ($allowOverwrite || !isset($output[$key])) {
                            if (null !== $tag) {
                                $output[$key] = new TaggedValue($tag, $value);
                            } else {
                                $output[$key] = $value;
                            }
                        } elseif (isset($output[$key])) {
                            throw new ParseException(\sprintf('Duplicate key "%s" detected.', $key), self::$parsedLineNumber + 1, $mapping);
                        }
                        break;
                    case '{':
                        $value = self::parseMapping($mapping, $flags, $i, $references);
                        if ('<<' === $key) {
                            $output += $value;
                        } elseif ($allowOverwrite || !isset($output[$key])) {
                            if (null !== $tag) {
                                $output[$key] = new TaggedValue($tag, $value);
                            } else {
                                $output[$key] = $value;
                            }
                        } elseif (isset($output[$key])) {
                            throw new ParseException(\sprintf('Duplicate key "%s" detected.', $key), self::$parsedLineNumber + 1, $mapping);
                        }
                        break;
                    default:
                        $value = self::parseScalar($mapping, $flags, [',', '}', "\n"], $i, null === $tag, $references, $isValueQuoted);
                        if ('<<' === $key) {
                            $output += $value;
                        } elseif ($allowOverwrite || !isset($output[$key])) {
                            if (!$isValueQuoted && \is_string($value) && '' !== $value && '&' === $value[0] && Parser::preg_match(Parser::REFERENCE_PATTERN, $value, $matches)) {
                                $references[$matches['ref']] = $matches['value'];
                                $value = $matches['value'];
                            }
                            if (null !== $tag) {
                                $output[$key] = new TaggedValue($tag, $value);
                            } else {
                                $output[$key] = $value;
                            }
                        } elseif (isset($output[$key])) {
                            throw new ParseException(\sprintf('Duplicate key "%s" detected.', $key), self::$parsedLineNumber + 1, $mapping);
                        }
                        --$i;
                }
                ++$i;
                continue 2;
            }
        }
        throw new ParseException(\sprintf('Malformed inline YAML string: "%s".', $mapping), self::$parsedLineNumber + 1, null, self::$parsedFilename);
    }
    private static function evaluateScalar(string $scalar, int $flags, array &$references = [], bool &$isQuotedString = null)
    {
        $isQuotedString = \false;
        $scalar = \trim($scalar);
        if (0 === \strpos($scalar, '*')) {
            if (\false !== ($pos = \strpos($scalar, '#'))) {
                $value = \substr($scalar, 1, $pos - 2);
            } else {
                $value = \substr($scalar, 1);
            }
            if (\false === $value || '' === $value) {
                throw new ParseException('A reference must contain at least one character.', self::$parsedLineNumber + 1, $value, self::$parsedFilename);
            }
            if (!\array_key_exists($value, $references)) {
                throw new ParseException(\sprintf('Reference "%s" does not exist.', $value), self::$parsedLineNumber + 1, $value, self::$parsedFilename);
            }
            return $references[$value];
        }
        $scalarLower = \strtolower($scalar);
        switch (\true) {
            case 'null' === $scalarLower:
            case '' === $scalar:
            case '~' === $scalar:
                return null;
            case 'true' === $scalarLower:
                return \true;
            case 'false' === $scalarLower:
                return \false;
            case '!' === $scalar[0]:
                switch (\true) {
                    case 0 === \strpos($scalar, '!!str '):
                        $s = (string) \substr($scalar, 6);
                        if (\in_array($s[0] ?? '', ['"', "'"], \true)) {
                            $isQuotedString = \true;
                            $s = self::parseQuotedScalar($s);
                        }
                        return $s;
                    case 0 === \strpos($scalar, '! '):
                        return \substr($scalar, 2);
                    case 0 === \strpos($scalar, '!php/object'):
                        if (self::$objectSupport) {
                            if (!isset($scalar[12])) {
                                trigger_deprecation('symfony/yaml', '5.1', 'Using the !php/object tag without a value is deprecated.');
                                return \false;
                            }
                            return \unserialize(self::parseScalar(\substr($scalar, 12)));
                        }
                        if (self::$exceptionOnInvalidType) {
                            throw new ParseException('Object support when parsing a YAML file has been disabled.', self::$parsedLineNumber + 1, $scalar, self::$parsedFilename);
                        }
                        return null;
                    case 0 === \strpos($scalar, '!php/const'):
                        if (self::$constantSupport) {
                            if (!isset($scalar[11])) {
                                trigger_deprecation('symfony/yaml', '5.1', 'Using the !php/const tag without a value is deprecated.');
                                return '';
                            }
                            $i = 0;
                            if (\defined($const = self::parseScalar(\substr($scalar, 11), 0, null, $i, \false))) {
                                return \constant($const);
                            }
                            throw new ParseException(\sprintf('The constant "%s" is not defined.', $const), self::$parsedLineNumber + 1, $scalar, self::$parsedFilename);
                        }
                        if (self::$exceptionOnInvalidType) {
                            throw new ParseException(\sprintf('The string "%s" could not be parsed as a constant. Did you forget to pass the "Yaml::PARSE_CONSTANT" flag to the parser?', $scalar), self::$parsedLineNumber + 1, $scalar, self::$parsedFilename);
                        }
                        return null;
                    case 0 === \strpos($scalar, '!!float '):
                        return (float) \substr($scalar, 8);
                    case 0 === \strpos($scalar, '!!binary '):
                        return self::evaluateBinaryScalar(\substr($scalar, 9));
                }
                throw new ParseException(\sprintf('The string "%s" could not be parsed as it uses an unsupported built-in tag.', $scalar), self::$parsedLineNumber, $scalar, self::$parsedFilename);
            case \preg_match('/^(?:\\+|-)?0o(?P<value>[0-7_]++)$/', $scalar, $matches):
                $value = \str_replace('_', '', $matches['value']);
                if ('-' === $scalar[0]) {
                    return -\octdec($value);
                }
                return \octdec($value);
            case \in_array($scalar[0], ['+', '-', '.'], \true) || \is_numeric($scalar[0]):
                if (Parser::preg_match('{^[+-]?[0-9][0-9_]*$}', $scalar)) {
                    $scalar = \str_replace('_', '', $scalar);
                }
                switch (\true) {
                    case \ctype_digit($scalar):
                        if (\preg_match('/^0[0-7]+$/', $scalar)) {
                            trigger_deprecation('symfony/yaml', '5.1', 'Support for parsing numbers prefixed with 0 as octal numbers. They will be parsed as strings as of 6.0. Use "%s" to represent the octal number.', '0o' . \substr($scalar, 1));
                            return \octdec($scalar);
                        }
                        $cast = (int) $scalar;
                        return $scalar === (string) $cast ? $cast : $scalar;
                    case '-' === $scalar[0] && \ctype_digit(\substr($scalar, 1)):
                        if (\preg_match('/^-0[0-7]+$/', $scalar)) {
                            trigger_deprecation('symfony/yaml', '5.1', 'Support for parsing numbers prefixed with 0 as octal numbers. They will be parsed as strings as of 6.0. Use "%s" to represent the octal number.', '-0o' . \substr($scalar, 2));
                            return -\octdec(\substr($scalar, 1));
                        }
                        $cast = (int) $scalar;
                        return $scalar === (string) $cast ? $cast : $scalar;
                    case \is_numeric($scalar):
                    case Parser::preg_match(self::getHexRegex(), $scalar):
                        $scalar = \str_replace('_', '', $scalar);
                        return '0x' === $scalar[0] . $scalar[1] ? \hexdec($scalar) : (float) $scalar;
                    case '.inf' === $scalarLower:
                    case '.nan' === $scalarLower:
                        return -\log(0);
                    case '-.inf' === $scalarLower:
                        return \log(0);
                    case Parser::preg_match('/^(-|\\+)?[0-9][0-9_]*(\\.[0-9_]+)?$/', $scalar):
                        return (float) \str_replace('_', '', $scalar);
                    case Parser::preg_match(self::getTimestampRegex(), $scalar):
                        $time = new \DateTime($scalar, new \DateTimeZone('UTC'));
                        if (Yaml::PARSE_DATETIME & $flags) {
                            return $time;
                        }
                        try {
                            if (\false !== ($scalar = $time->getTimestamp())) {
                                return $scalar;
                            }
                        } catch (\ValueError $e) {
                        }
                        return $time->format('U');
                }
        }
        return (string) $scalar;
    }
    private static function parseTag(string $value, int &$i, int $flags) : ?string
    {
        if ('!' !== $value[$i]) {
            return null;
        }
        $tagLength = \strcspn($value, " \t\n[]{},", $i + 1);
        $tag = \substr($value, $i + 1, $tagLength);
        $nextOffset = $i + $tagLength + 1;
        $nextOffset += \strspn($value, ' ', $nextOffset);
        if ('' === $tag && (!isset($value[$nextOffset]) || \in_array($value[$nextOffset], [']', '}', ','], \true))) {
            throw new ParseException('Using the unquoted scalar value "!" is not supported. You must quote it.', self::$parsedLineNumber + 1, $value, self::$parsedFilename);
        }
        if ('' !== $tag && (!isset($value[$nextOffset]) || !\in_array($value[$nextOffset], ['[', '{'], \true)) && ('!' === $tag[0] || 'str' === $tag || 'php/const' === $tag || 'php/object' === $tag)) {
            return null;
        }
        $i = $nextOffset;
        if ('' !== $tag && '!' === $tag[0]) {
            throw new ParseException(\sprintf('The built-in tag "!%s" is not implemented.', $tag), self::$parsedLineNumber + 1, $value, self::$parsedFilename);
        }
        if ('' !== $tag && !isset($value[$i])) {
            throw new ParseException(\sprintf('Missing value for tag "%s".', $tag), self::$parsedLineNumber + 1, $value, self::$parsedFilename);
        }
        if ('' === $tag || Yaml::PARSE_CUSTOM_TAGS & $flags) {
            return $tag;
        }
        throw new ParseException(\sprintf('Tags support is not enabled. Enable the "Yaml::PARSE_CUSTOM_TAGS" flag to use "!%s".', $tag), self::$parsedLineNumber + 1, $value, self::$parsedFilename);
    }
    public static function evaluateBinaryScalar(string $scalar) : string
    {
        $parsedBinaryData = self::parseScalar(\preg_replace('/\\s/', '', $scalar));
        if (0 !== \strlen($parsedBinaryData) % 4) {
            throw new ParseException(\sprintf('The normalized base64 encoded data (data without whitespace characters) length must be a multiple of four (%d bytes given).', \strlen($parsedBinaryData)), self::$parsedLineNumber + 1, $scalar, self::$parsedFilename);
        }
        if (!Parser::preg_match('#^[A-Z0-9+/]+={0,2}$#i', $parsedBinaryData)) {
            throw new ParseException(\sprintf('The base64 encoded data (%s) contains invalid characters.', $parsedBinaryData), self::$parsedLineNumber + 1, $scalar, self::$parsedFilename);
        }
        return \base64_decode($parsedBinaryData, \true);
    }
    private static function isBinaryString(string $value) : bool
    {
        return !\preg_match('//u', $value) || \preg_match('/[^\\x00\\x07-\\x0d\\x1B\\x20-\\xff]/', $value);
    }
    private static function getTimestampRegex() : string
    {
        return <<<EOF
        ~^
        (?P<year>[0-9][0-9][0-9][0-9])
        -(?P<month>[0-9][0-9]?)
        -(?P<day>[0-9][0-9]?)
        (?:(?:[Tt]|[ \t]+)
        (?P<hour>[0-9][0-9]?)
        :(?P<minute>[0-9][0-9])
        :(?P<second>[0-9][0-9])
        (?:\\.(?P<fraction>[0-9]*))?
        (?:[ \t]*(?P<tz>Z|(?P<tz_sign>[-+])(?P<tz_hour>[0-9][0-9]?)
        (?::(?P<tz_minute>[0-9][0-9]))?))?)?
        \$~x
EOF;
    }
    private static function getHexRegex() : string
    {
        return '~^0x[0-9a-f_]++$~i';
    }
}
