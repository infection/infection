<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\DatetimeException;
class DateTimeImmutable extends \DateTimeImmutable
{
    private $innerDateTime;
    public function __construct($time = 'now', $timezone = null)
    {
        parent::__construct($time, $timezone);
        $this->innerDateTime = new parent($time, $timezone);
    }
    public static function createFromRegular(\DateTimeImmutable $datetime) : self
    {
        $safeDatetime = new self($datetime->format('Y-m-d H:i:s.u'), $datetime->getTimezone());
        $safeDatetime->innerDateTime = $datetime;
        return $safeDatetime;
    }
    public function getInnerDateTime() : \DateTimeImmutable
    {
        return $this->innerDateTime;
    }
    public static function createFromFormat($format, $time, $timezone = null) : self
    {
        $datetime = \DateTimeImmutable::createFromFormat($format, $time, $timezone);
        if ($datetime === \false) {
            throw DatetimeException::createFromPhpError();
        }
        return self::createFromRegular($datetime);
    }
    public function format($format) : string
    {
        $result = $this->innerDateTime->format($format);
        if ($result === \false) {
            throw DatetimeException::createFromPhpError();
        }
        return $result;
    }
    public function diff($datetime2, $absolute = \false) : \DateInterval
    {
        $result = $this->innerDateTime->diff($datetime2, $absolute);
        if ($result === \false) {
            throw DatetimeException::createFromPhpError();
        }
        return $result;
    }
    public function modify($modify) : self
    {
        $result = $this->innerDateTime->modify($modify);
        if ($result === \false) {
            throw DatetimeException::createFromPhpError();
        }
        return self::createFromRegular($result);
    }
    public function setDate($year, $month, $day) : self
    {
        $result = $this->innerDateTime->setDate($year, $month, $day);
        if ($result === \false) {
            throw DatetimeException::createFromPhpError();
        }
        return self::createFromRegular($result);
    }
    public function setISODate($year, $week, $day = 1) : self
    {
        $result = $this->innerDateTime->setISODate($year, $week, $day);
        if ($result === \false) {
            throw DatetimeException::createFromPhpError();
        }
        return self::createFromRegular($result);
    }
    public function setTime($hour, $minute, $second = 0, $microseconds = 0) : self
    {
        $result = $this->innerDateTime->setTime($hour, $minute, $second, $microseconds);
        if ($result === \false) {
            throw DatetimeException::createFromPhpError();
        }
        return self::createFromRegular($result);
    }
    public function setTimestamp($unixtimestamp) : self
    {
        $result = $this->innerDateTime->setTimestamp($unixtimestamp);
        if ($result === \false) {
            throw DatetimeException::createFromPhpError();
        }
        return self::createFromRegular($result);
    }
    public function setTimezone($timezone) : self
    {
        $result = $this->innerDateTime->setTimezone($timezone);
        if ($result === \false) {
            throw DatetimeException::createFromPhpError();
        }
        return self::createFromRegular($result);
    }
    public function sub($interval) : self
    {
        $result = $this->innerDateTime->sub($interval);
        if ($result === \false) {
            throw DatetimeException::createFromPhpError();
        }
        return self::createFromRegular($result);
    }
    public function getOffset() : int
    {
        $result = $this->innerDateTime->getOffset();
        if ($result === \false) {
            throw DatetimeException::createFromPhpError();
        }
        return $result;
    }
    public function add($interval) : self
    {
        return self::createFromRegular($this->innerDateTime->add($interval));
    }
    public static function createFromMutable($dateTime) : self
    {
        $date = \DateTimeImmutable::createFromMutable($dateTime);
        return self::createFromRegular($date);
    }
    public static function __set_state($array) : self
    {
        return self::createFromRegular(parent::__set_state($array));
    }
    public function getTimezone() : \DateTimeZone
    {
        return $this->innerDateTime->getTimezone();
    }
    public function getTimestamp() : int
    {
        return $this->innerDateTime->getTimestamp();
    }
}
