<?php

namespace _HumbugBoxb47773b41c19\GetOpt;

class OptionParser
{
    public static $defaultMode = GetOpt::NO_ARGUMENT;
    public static function parseString($string)
    {
        if (!\mb_strlen($string)) {
            throw new \InvalidArgumentException('Option string must not be empty');
        }
        $options = [];
        $eol = \mb_strlen($string) - 1;
        $nextCanBeColon = \false;
        for ($i = 0; $i <= $eol; ++$i) {
            $ch = $string[$i];
            if (!\preg_match('/^[A-Za-z0-9]$/', $ch)) {
                $colon = $nextCanBeColon ? " or ':'" : '';
                throw new \InvalidArgumentException("Option string is not well formed: " . "expected a letter{$colon}, found '{$ch}' at position " . ($i + 1));
            }
            if ($i == $eol || $string[$i + 1] != ':') {
                $options[] = new Option($ch, null, GetOpt::NO_ARGUMENT);
                $nextCanBeColon = \true;
            } elseif ($i < $eol - 1 && $string[$i + 2] == ':') {
                $options[] = new Option($ch, null, GetOpt::OPTIONAL_ARGUMENT);
                $i += 2;
                $nextCanBeColon = \false;
            } else {
                $options[] = new Option($ch, null, GetOpt::REQUIRED_ARGUMENT);
                ++$i;
                $nextCanBeColon = \true;
            }
        }
        return $options;
    }
    public static function parseArray(array $array)
    {
        if (empty($array)) {
            throw new \InvalidArgumentException('Invalid option array (at least a name has to be given)');
        }
        $rowSize = \count($array);
        if ($rowSize < 3) {
            $array = self::completeOptionArray($array);
        }
        $option = new Option($array[0], $array[1], $array[2]);
        if ($rowSize >= 4) {
            $option->setDescription($array[3]);
        }
        if ($rowSize >= 5 && $array[2] != GetOpt::NO_ARGUMENT) {
            $option->setArgument(new Argument($array[4]));
        }
        return $option;
    }
    protected static function completeOptionArray(array $row)
    {
        $short = \strlen($row[0]) == 1 ? $row[0] : null;
        $long = null;
        if (\is_null($short)) {
            $long = $row[0];
        } elseif (\count($row) > 1 && $row[1][0] !== ':') {
            $long = $row[1];
        }
        $mode = self::$defaultMode;
        if (\count($row) == 2 && $row[1][0] === ':') {
            $mode = $row[1];
        }
        return [$short, $long, $mode];
    }
}
