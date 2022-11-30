<?php

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;




interface DateTimeInterface
{



public const ATOM = 'Y-m-d\TH:i:sP';




public const COOKIE = 'l, d-M-Y H:i:s T';







public const ISO8601 = 'Y-m-d\TH:i:sO';




public const RFC822 = 'D, d M y H:i:s O';




public const RFC850 = 'l, d-M-y H:i:s T';




public const RFC1036 = 'D, d M y H:i:s O';




public const RFC1123 = 'D, d M Y H:i:s O';




public const RFC2822 = 'D, d M Y H:i:s O';




public const RFC3339 = 'Y-m-d\TH:i:sP';




public const RFC3339_EXTENDED = 'Y-m-d\TH:i:s.vP';




public const RFC7231 = 'D, d M Y H:i:s \G\M\T';




public const RSS = 'D, d M Y H:i:s O';




public const W3C = 'Y-m-d\TH:i:sP';












#[TentativeType]
public function diff(
DateTimeInterface $targetObject,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $absolute = false
): DateInterval;












#[TentativeType]
public function format(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $format): string;








#[LanguageLevelTypeAware(["8.0" => "int"], default: "int|false")]
#[TentativeType]
public function getOffset(): int;







#[TentativeType]
#[LanguageLevelTypeAware(['8.1' => 'int'], default: 'int|false')]
public function getTimestamp();









#[TentativeType]
public function getTimezone(): DateTimeZone|false;







#[TentativeType]
public function __wakeup(): void;

#[PhpStormStubsElementAvailable(from: '8.2')]
public function __serialize(): array;

#[PhpStormStubsElementAvailable(from: '8.2')]
public function __unserialize(array $data): void;
}




class DateTimeImmutable implements DateTimeInterface
{

















public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $datetime = "now",
#[LanguageLevelTypeAware(['8.0' => 'DateTimeZone|null'], default: 'DateTimeZone')] $timezone = null
) {}







#[TentativeType]
public function add(DateInterval $interval): DateTimeImmutable {}










#[TentativeType]
public static function createFromFormat(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $format,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $datetime,
#[LanguageLevelTypeAware(['8.0' => 'DateTimeZone|null'], default: 'DateTimeZone')] $timezone = null
): DateTimeImmutable|false {}








#[TentativeType]
#[LanguageLevelTypeAware(['8.2' => 'static'], default: 'DateTimeImmutable')]
public static function createFromMutable(DateTime $object) {}







#[ArrayShape(["warning_count" => "int", "warnings" => "string[]", "error_count" => "int", "errors" => "string[]"])]
#[TentativeType]
public static function getLastErrors(): array|false {}










#[Pure]
#[TentativeType]
public function modify(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $modifier): DateTimeImmutable|false {}









public static function __set_state(array $array) {}











#[TentativeType]
public function setDate(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $year,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $month,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $day
): DateTimeImmutable {}











#[TentativeType]
public function setISODate(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $year,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $week,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $dayOfWeek = 1
): DateTimeImmutable {}












#[TentativeType]
public function setTime(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $hour,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $minute,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $second = 0,
#[PhpStormStubsElementAvailable(from: '7.1')] #[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $microsecond = 0
): DateTimeImmutable {}









#[TentativeType]
public function setTimestamp(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $timestamp): DateTimeImmutable {}












#[TentativeType]
public function setTimezone(DateTimeZone $timezone): DateTimeImmutable {}











#[TentativeType]
public function sub(DateInterval $interval): DateTimeImmutable {}











#[TentativeType]
public function diff(
#[LanguageLevelTypeAware(['8.0' => 'DateTimeInterface'], default: '')] $targetObject,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $absolute = false
): DateInterval {}











#[TentativeType]
public function format(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $format): string {}








#[TentativeType]
public function getOffset(): int {}







#[TentativeType]
public function getTimestamp(): int {}









#[TentativeType]
public function getTimezone(): DateTimeZone|false {}







#[TentativeType]
public function __wakeup(): void {}






public static function createFromInterface(DateTimeInterface $object): DateTimeImmutable {}

#[PhpStormStubsElementAvailable(from: '8.2')]
public function __serialize(): array {}

#[PhpStormStubsElementAvailable(from: '8.2')]
public function __unserialize(array $data): void {}
}





class DateTime implements DateTimeInterface
{
/**
@removed
*/
public const ATOM = 'Y-m-d\TH:i:sP';

/**
@removed
*/
public const COOKIE = 'l, d-M-Y H:i:s T';

/**
@removed
*/
public const ISO8601 = 'Y-m-d\TH:i:sO';

/**
@removed
*/
public const RFC822 = 'D, d M y H:i:s O';

/**
@removed
*/
public const RFC850 = 'l, d-M-y H:i:s T';

/**
@removed
*/
public const RFC1036 = 'D, d M y H:i:s O';

/**
@removed
*/
public const RFC1123 = 'D, d M Y H:i:s O';

/**
@removed
*/
public const RFC2822 = 'D, d M Y H:i:s O';

/**
@removed
*/
public const RFC3339 = 'Y-m-d\TH:i:sP';

/**
@removed
*/
public const RFC3339_EXTENDED = 'Y-m-d\TH:i:s.vP';

/**
@removed
*/
public const RFC7231 = 'D, d M Y H:i:s \G\M\T';

/**
@removed
*/
public const RSS = 'D, d M Y H:i:s O';

/**
@removed
*/
public const W3C = 'Y-m-d\TH:i:sP';





























public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $datetime = 'now',
#[LanguageLevelTypeAware(['8.0' => 'DateTimeZone|null'], default: 'DateTimeZone')] $timezone = null
) {}





#[TentativeType]
public function __wakeup(): void {}







#[TentativeType]
public function format(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $format): string {}








#[TentativeType]
public function modify(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $modifier): DateTime|false {}







#[TentativeType]
public function add(DateInterval $interval): DateTime {}






#[TentativeType]
#[LanguageLevelTypeAware(['8.2' => 'static'], default: 'DateTime')]
public static function createFromImmutable(DateTimeImmutable $object) {}







#[TentativeType]
public function sub(DateInterval $interval): DateTime {}






#[TentativeType]
public function getTimezone(): DateTimeZone|false {}







#[TentativeType]
public function setTimezone(#[LanguageLevelTypeAware(['8.0' => 'DateTimeZone'], default: '')] $timezone): DateTime {}






#[TentativeType]
public function getOffset(): int {}










#[TentativeType]
public function setTime(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $hour,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $minute,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $second = 0,
#[PhpStormStubsElementAvailable(from: '7.1')] #[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $microsecond = 0
): DateTime {}









#[TentativeType]
public function setDate(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $year,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $month,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $day
): DateTime {}









#[TentativeType]
public function setISODate(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $year,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $week,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $dayOfWeek = 1
): DateTime {}







#[TentativeType]
public function setTimestamp(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $timestamp): DateTime {}






#[TentativeType]
public function getTimestamp(): int {}








#[TentativeType]
public function diff(
#[LanguageLevelTypeAware(['8.0' => 'DateTimeInterface'], default: '')] $targetObject,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $absolute = false
): DateInterval {}









#[TentativeType]
public static function createFromFormat(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $format,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $datetime,
#[LanguageLevelTypeAware(['8.0' => 'DateTimeZone|null'], default: 'DateTimeZone')] $timezone = null
): DateTime|false {}






#[ArrayShape(["warning_count" => "int", "warnings" => "string[]", "error_count" => "int", "errors" => "string[]"])]
#[TentativeType]
public static function getLastErrors(): array|false {}







public static function __set_state($array) {}






public static function createFromInterface(DateTimeInterface $object): DateTime {}

#[PhpStormStubsElementAvailable(from: '8.2')]
public function __serialize(): array {}

#[PhpStormStubsElementAvailable(from: '8.2')]
public function __unserialize(array $data): void {}
}





class DateTimeZone
{
public const AFRICA = 1;
public const AMERICA = 2;
public const ANTARCTICA = 4;
public const ARCTIC = 8;
public const ASIA = 16;
public const ATLANTIC = 32;
public const AUSTRALIA = 64;
public const EUROPE = 128;
public const INDIAN = 256;
public const PACIFIC = 512;
public const UTC = 1024;
public const ALL = 2047;
public const ALL_WITH_BC = 4095;
public const PER_COUNTRY = 4096;





public function __construct(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $timezone) {}






#[TentativeType]
public function getName(): string {}






#[TentativeType]
public function getLocation(): array|false {}







#[TentativeType]
public function getOffset(DateTimeInterface $datetime): int {}








#[TentativeType]
public function getTransitions(
#[PhpStormStubsElementAvailable(from: '5.3', to: '5.6')] $timestampBegin,
#[PhpStormStubsElementAvailable(from: '5.3', to: '5.6')] $timestampEnd,
#[PhpStormStubsElementAvailable(from: '7.0')] #[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $timestampBegin = null,
#[PhpStormStubsElementAvailable(from: '7.0')] #[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $timestampEnd = null
): array|false {}






#[TentativeType]
public static function listAbbreviations(): array {}








#[LanguageLevelTypeAware(["8.0" => "array"], default: "array|false")]
#[TentativeType]
public static function listIdentifiers(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $timezoneGroup = DateTimeZone::ALL,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $countryCode = null
): array {}




#[TentativeType]
public function __wakeup(): void {}

public static function __set_state($an_array) {}

#[PhpStormStubsElementAvailable(from: '8.2')]
public function __serialize(): array {}

#[PhpStormStubsElementAvailable(from: '8.2')]
public function __unserialize(array $data): void {}
}







class DateInterval
{




public $y;





public $m;





public $d;





public $h;





public $i;





public $s;






public $f;





public $invert;





public $days;






public function __construct(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $duration) {}







#[TentativeType]
public function format(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $format): string {}








#[TentativeType]
public static function createFromDateString(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $datetime): DateInterval|false {}

#[TentativeType]
public function __wakeup(): void {}

public static function __set_state($an_array) {}

#[PhpStormStubsElementAvailable(from: '8.2')]
public function __serialize(): array {}

#[PhpStormStubsElementAvailable(from: '8.2')]
public function __unserialize(array $data): void {}
}





class DatePeriod implements IteratorAggregate
{
public const EXCLUDE_START_DATE = 1;




public const INCLUDE_END_DATE = 2;





#[LanguageLevelTypeAware(['8.2' => 'DateTimeInterface|null'], default: '')]
#[Immutable]
public $start;





#[LanguageLevelTypeAware(['8.2' => 'DateTimeInterface|null'], default: '')]
public $current;





#[LanguageLevelTypeAware(['8.2' => 'DateTimeInterface|null'], default: '')]
#[Immutable]
public $end;





#[LanguageLevelTypeAware(['8.2' => 'DateInterval|null'], default: '')]
#[Immutable]
public $interval;





#[LanguageLevelTypeAware(['8.2' => 'int'], default: '')]
#[Immutable]
public $recurrences;





#[LanguageLevelTypeAware(['8.2' => 'bool'], default: '')]
#[Immutable]
public $include_start_date;




#[Immutable]
public bool $include_end_date;








public function __construct(DateTimeInterface $start, DateInterval $interval, DateTimeInterface $end, $options = 0) {}








public function __construct(DateTimeInterface $start, DateInterval $interval, $recurrences, $options = 0) {}






public function __construct($isostr, $options = 0) {}







#[TentativeType]
public function getDateInterval(): DateInterval {}







#[TentativeType]
public function getEndDate(): ?DateTimeInterface {}







#[TentativeType]
public function getStartDate(): DateTimeInterface {}

#[TentativeType]
public static function __set_state(#[PhpStormStubsElementAvailable(from: '7.3')] array $array): DatePeriod {}

#[TentativeType]
public function __wakeup(): void {}







#[TentativeType]
public function getRecurrences(): ?int {}





public function getIterator(): Iterator {}

#[PhpStormStubsElementAvailable(from: '8.2')]
public function __serialize(): array {}

#[PhpStormStubsElementAvailable(from: '8.2')]
public function __unserialize(array $data): void {}
}
