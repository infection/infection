<?php

namespace _HumbugBoxb47773b41c19\JsonSchema;

class Rfc3339
{
    const REGEX = '/^(\\d{4}-\\d{2}-\\d{2}[T ]{1}\\d{2}:\\d{2}:\\d{2})(\\.\\d+)?(Z|([+-]\\d{2}):?(\\d{2}))$/';
    public static function createFromString($string)
    {
        if (!\preg_match(self::REGEX, \strtoupper($string), $matches)) {
            return null;
        }
        $dateAndTime = $matches[1];
        $microseconds = $matches[2] ?: '.000000';
        $timeZone = 'Z' !== $matches[3] ? $matches[4] . ':' . $matches[5] : '+00:00';
        $dateFormat = \strpos($dateAndTime, 'T') === \false ? 'Y-m-d H:i:s.uP' : 'Y-m-d\\TH:i:s.uP';
        $dateTime = \DateTime::createFromFormat($dateFormat, $dateAndTime . $microseconds . $timeZone, new \DateTimeZone('UTC'));
        return $dateTime ?: null;
    }
}
