<?php

namespace _HumbugBox9658796bb9f0\Safe;

use DateInterval;
use DateTimeInterface;
use DateTimeZone;
use _HumbugBox9658796bb9f0\Safe\Exceptions\DatetimeException;
class DateTime extends \DateTime
{
    private static function createFromRegular(\DateTime $datetime) : self
    {
        return new self($datetime->format('Y-m-d H:i:s.u'), $datetime->getTimezone());
    }
    public static function createFromFormat($format, $time, $timezone = null) : self
    {
        $datetime = \DateTime::createFromFormat($format, $time, $timezone);
        if ($datetime === \false) {
            throw DatetimeException::createFromPhpError();
        }
        return self::createFromRegular($datetime);
    }
    public function diff($datetime2, $absolute = \false) : DateInterval
    {
        $result = parent::diff($datetime2, $absolute);
        if ($result === \false) {
            throw DatetimeException::createFromPhpError();
        }
        return $result;
    }
    public function modify($modify) : self
    {
        $result = parent::modify($modify);
        if ($result === \false) {
            throw DatetimeException::createFromPhpError();
        }
        return $result;
    }
    public function setDate($year, $month, $day) : self
    {
        $result = parent::setDate($year, $month, $day);
        if ($result === \false) {
            throw DatetimeException::createFromPhpError();
        }
        return $result;
    }
}
