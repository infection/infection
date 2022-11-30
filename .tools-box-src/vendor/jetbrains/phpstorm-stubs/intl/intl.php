<?php



use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\ExpectedValues as EV;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware as TypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable as ElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;

class Collator
{
public const DEFAULT_VALUE = -1;
public const PRIMARY = 0;
public const SECONDARY = 1;
public const TERTIARY = 2;
public const DEFAULT_STRENGTH = 2;
public const QUATERNARY = 3;
public const IDENTICAL = 15;
public const OFF = 16;
public const ON = 17;
public const SHIFTED = 20;
public const NON_IGNORABLE = 21;
public const LOWER_FIRST = 24;
public const UPPER_FIRST = 25;























public const FRENCH_COLLATION = 0;












































public const ALTERNATE_HANDLING = 1;

































public const CASE_FIRST = 2;

























public const CASE_LEVEL = 3;

























public const NORMALIZATION_MODE = 4;






















public const STRENGTH = 5;


















public const HIRAGANA_QUATERNARY_MODE = 6;















public const NUMERIC_COLLATION = 7;
public const SORT_REGULAR = 0;
public const SORT_STRING = 1;
public const SORT_NUMERIC = 2;







#[Pure]
public function __construct(#[TypeAware(['8.0' => 'string'], default: '')] $locale) {}














#[TentativeType]
public static function create(#[TypeAware(['8.0' => 'string'], default: '')] $locale): ?Collator {}






























#[Pure]
#[TentativeType]
public function compare(
#[TypeAware(['8.0' => 'string'], default: '')] $string1,
#[TypeAware(['8.0' => 'string'], default: '')] $string2
): int|false {}

















#[TentativeType]
public function sort(
array &$array,
#[TypeAware(['8.0' => 'int'], default: '')] #[EV([Collator::SORT_REGULAR])] $flags = null
): bool {}








#[TentativeType]
public function sortWithSortKeys(
array &$array,
#[ElementAvailable(from: '5.3', to: '5.6')] $flags = []
): bool {}













#[TentativeType]
public function asort(
array &$array,
#[TypeAware(['8.0' => 'int'], default: '')] #[EV([Collator::SORT_REGULAR])] $flags = null
): bool {}










#[Pure]
#[TentativeType]
public function getAttribute(#[TypeAware(['8.0' => 'int'], default: '')] $attribute): int|false {}











#[TentativeType]
public function setAttribute(
#[TypeAware(['8.0' => 'int'], default: '')] $attribute,
#[TypeAware(['8.0' => 'int'], default: '')] $value
): bool {}







#[Pure]
#[TentativeType]
public function getStrength(): int {}












public function setStrength(#[TypeAware(['8.0' => 'int'], default: '')] #[EV([Collator::PRIMARY])] $strength) {}







#[Pure]
#[TentativeType]
public function getErrorCode(): int|false {}















#[Pure]
#[TentativeType]
public function getLocale(
#[TypeAware(['8.0' => 'int'], default: '')]
#[EV([Locale::VALID_LOCALE, Locale::ACTUAL_LOCALE])]
$type
): string|false {}







#[Pure]
#[TentativeType]
public function getErrorMessage(): string|false {}










#[Pure]
#[TentativeType]
public function getSortKey(
#[TypeAware(['8.0' => 'string'], default: '')] $string,
#[ElementAvailable(from: '5.3', to: '5.6')] $arg2
): string|false {}
}

class NumberFormatter
{
public const CURRENCY_ACCOUNTING = 12;





public const PATTERN_DECIMAL = 0;





public const DECIMAL = 1;





public const CURRENCY = 2;





public const PERCENT = 3;





public const SCIENTIFIC = 4;





public const SPELLOUT = 5;





public const ORDINAL = 6;





public const DURATION = 7;





public const PATTERN_RULEBASED = 9;





public const IGNORE = 0;





public const DEFAULT_STYLE = 1;





public const ROUND_CEILING = 0;





public const ROUND_FLOOR = 1;





public const ROUND_DOWN = 2;





public const ROUND_UP = 3;







public const ROUND_HALFEVEN = 4;






public const ROUND_HALFDOWN = 5;






public const ROUND_HALFUP = 6;





public const PAD_BEFORE_PREFIX = 0;





public const PAD_AFTER_PREFIX = 1;





public const PAD_BEFORE_SUFFIX = 2;





public const PAD_AFTER_SUFFIX = 3;





public const PARSE_INT_ONLY = 0;





public const GROUPING_USED = 1;





public const DECIMAL_ALWAYS_SHOWN = 2;





public const MAX_INTEGER_DIGITS = 3;





public const MIN_INTEGER_DIGITS = 4;





public const INTEGER_DIGITS = 5;





public const MAX_FRACTION_DIGITS = 6;





public const MIN_FRACTION_DIGITS = 7;





public const FRACTION_DIGITS = 8;





public const MULTIPLIER = 9;





public const GROUPING_SIZE = 10;





public const ROUNDING_MODE = 11;





public const ROUNDING_INCREMENT = 12;





public const FORMAT_WIDTH = 13;






public const PADDING_POSITION = 14;





public const SECONDARY_GROUPING_SIZE = 15;





public const SIGNIFICANT_DIGITS_USED = 16;





public const MIN_SIGNIFICANT_DIGITS = 17;





public const MAX_SIGNIFICANT_DIGITS = 18;





public const LENIENT_PARSE = 19;





public const POSITIVE_PREFIX = 0;





public const POSITIVE_SUFFIX = 1;





public const NEGATIVE_PREFIX = 2;





public const NEGATIVE_SUFFIX = 3;





public const PADDING_CHARACTER = 4;





public const CURRENCY_CODE = 5;






public const DEFAULT_RULESET = 6;








public const PUBLIC_RULESETS = 7;





public const DECIMAL_SEPARATOR_SYMBOL = 0;





public const GROUPING_SEPARATOR_SYMBOL = 1;





public const PATTERN_SEPARATOR_SYMBOL = 2;





public const PERCENT_SYMBOL = 3;





public const ZERO_DIGIT_SYMBOL = 4;





public const DIGIT_SYMBOL = 5;





public const MINUS_SIGN_SYMBOL = 6;





public const PLUS_SIGN_SYMBOL = 7;





public const CURRENCY_SYMBOL = 8;





public const INTL_CURRENCY_SYMBOL = 9;





public const MONETARY_SEPARATOR_SYMBOL = 10;





public const EXPONENTIAL_SYMBOL = 11;





public const PERMILL_SYMBOL = 12;





public const PAD_ESCAPE_SYMBOL = 13;





public const INFINITY_SYMBOL = 14;





public const NAN_SYMBOL = 15;





public const SIGNIFICANT_DIGIT_SYMBOL = 16;





public const MONETARY_GROUPING_SEPARATOR_SYMBOL = 17;





public const TYPE_DEFAULT = 0;





public const TYPE_INT32 = 1;





public const TYPE_INT64 = 2;





public const TYPE_DOUBLE = 3;





public const TYPE_CURRENCY = 4;







#[Pure]
public function __construct(
#[TypeAware(['8.0' => 'string'], default: '')] $locale,
#[TypeAware(['8.0' => 'int'], default: '')] $style,
#[TypeAware(['8.0' => 'string|null'], default: '')] $pattern = null
) {}

























#[TentativeType]
public static function create(
#[TypeAware(['8.0' => 'string'], default: '')] $locale,
#[TypeAware(['8.0' => 'int'], default: '')] #[EV([NumberFormatter::PATTERN_DECIMAL, NumberFormatter::PATTERN_RULEBASED])] $style,
#[TypeAware(['8.0' => 'string|null'], default: '')] $pattern = null
): ?NumberFormatter {}















#[Pure]
#[TentativeType]
public function format(
#[TypeAware(['8.0' => 'int|float'], default: '')] $num,
#[TypeAware(['8.0' => 'int'], default: '')] $type = null
): string|false {}

















#[TentativeType]
public function parse(
#[TypeAware(['8.0' => 'string'], default: '')] $string,
#[TypeAware(['8.0' => 'int'], default: '')] $type = NumberFormatter::TYPE_DOUBLE,
&$offset = null
): int|float|false {}













#[Pure]
#[TentativeType]
public function formatCurrency(
#[TypeAware(['8.0' => 'float'], default: '')] $amount,
#[TypeAware(['8.0' => 'string'], default: '')] $currency
): string|false {}
















#[TentativeType]
public function parseCurrency(#[TypeAware(['8.0' => 'string'], default: '')] $string, &$currency, &$offset = null): float|false {}














#[TentativeType]
public function setAttribute(
#[TypeAware(['8.0' => 'int'], default: '')] $attribute,
#[TypeAware(['8.0' => 'int|float'], default: '')] $value
): bool {}











#[Pure]
#[TentativeType]
public function getAttribute(#[TypeAware(['8.0' => 'int'], default: '')] $attribute): int|float|false {}















#[TentativeType]
public function setTextAttribute(
#[TypeAware(['8.0' => 'int'], default: '')] $attribute,
#[TypeAware(['8.0' => 'string'], default: '')] $value
): bool {}











#[Pure]
#[TentativeType]
public function getTextAttribute(#[TypeAware(['8.0' => 'int'], default: '')] $attribute): string|false {}














#[TentativeType]
public function setSymbol(
#[TypeAware(['8.0' => 'int'], default: '')] $symbol,
#[TypeAware(['8.0' => 'string'], default: '')] $value
): bool {}











#[Pure]
#[TentativeType]
public function getSymbol(#[TypeAware(['8.0' => 'int'], default: '')] $symbol): string|false {}












#[TentativeType]
public function setPattern(#[TypeAware(['8.0' => 'string'], default: '')] $pattern): bool {}







#[Pure]
#[TentativeType]
public function getPattern(): string|false {}













#[Pure]
#[TentativeType]
public function getLocale(
#[TypeAware(['8.0' => 'int'], default: '')] #[EV([Locale::VALID_LOCALE, Locale::ACTUAL_LOCALE])] $type = null
): string|false {}







#[Pure]
#[TentativeType]
public function getErrorCode(): int {}







#[Pure]
#[TentativeType]
public function getErrorMessage(): string {}
}

class Normalizer
{
public const NFKC_CF = 48;
public const FORM_KC_CF = 48;





public const OPTION_DEFAULT = "";

/**
@removed


*/
public const NONE = "1";





public const FORM_D = 4;
public const NFD = 4;





public const FORM_KD = 8;
public const NFKD = 8;






public const FORM_C = 16;
public const NFC = 16;






public const FORM_KC = 32;
public const NFKC = 32;









#[TentativeType]
public static function normalize(
#[TypeAware(['8.0' => 'string'], default: '')] $string,
#[ElementAvailable(from: '5.3', to: '5.6')] $form,
#[ElementAvailable(from: '7.0')] #[TypeAware(['8.0' => 'int'], default: '')] $form = Normalizer::FORM_C,
#[ElementAvailable(from: '5.3', to: '5.6')] $arg3
): string|false {}











#[TentativeType]
public static function isNormalized(
#[TypeAware(['8.0' => 'string'], default: '')] $string,
#[ElementAvailable(from: '5.3', to: '5.6')] $form,
#[ElementAvailable(from: '7.0')] #[TypeAware(['8.0' => 'int'], default: '')] $form = Normalizer::FORM_C,
#[ElementAvailable(from: '5.3', to: '5.6')] $arg3
): bool {}









#[TentativeType]
public static function getRawDecomposition(
string $string,
#[ElementAvailable(from: '8.0')] int $form = 16
): ?string {}
}

class Locale
{




public const ACTUAL_LOCALE = 0;





public const VALID_LOCALE = 1;







public const DEFAULT_LOCALE = null;





public const LANG_TAG = "language";





public const EXTLANG_TAG = "extlang";





public const SCRIPT_TAG = "script";





public const REGION_TAG = "region";





public const VARIANT_TAG = "variant";





public const GRANDFATHERED_LANG_TAG = "grandfathered";





public const PRIVATE_TAG = "private";







#[TentativeType]
public static function getDefault(): string {}










public static function setDefault(#[TypeAware(['8.0' => 'string'], default: '')] $locale) {}










#[TentativeType]
public static function getPrimaryLanguage(#[TypeAware(['8.0' => 'string'], default: '')] $locale): ?string {}










#[TentativeType]
public static function getScript(#[TypeAware(['8.0' => 'string'], default: '')] $locale): ?string {}










#[TentativeType]
public static function getRegion(#[TypeAware(['8.0' => 'string'], default: '')] $locale): ?string {}










#[TentativeType]
public static function getKeywords(#[TypeAware(['8.0' => 'string'], default: '')] $locale): array|false|null {}














#[TentativeType]
public static function getDisplayScript(
#[TypeAware(['8.0' => 'string'], default: '')] $locale,
#[ElementAvailable(from: '5.3', to: '5.6')] $displayLocale,
#[ElementAvailable(from: '7.0')] #[TypeAware(['8.0' => 'string|null'], default: '')] $displayLocale = null
): string|false {}














#[TentativeType]
public static function getDisplayRegion(
#[TypeAware(['8.0' => 'string'], default: '')] $locale,
#[ElementAvailable(from: '5.3', to: '5.6')] $displayLocale,
#[ElementAvailable(from: '7.0')] #[TypeAware(['8.0' => 'string|null'], default: '')] $displayLocale = null
): string|false {}











#[TentativeType]
public static function getDisplayName(
#[TypeAware(['8.0' => 'string'], default: '')] $locale,
#[ElementAvailable(from: '5.3', to: '5.6')] $displayLocale,
#[ElementAvailable(from: '7.0')] #[TypeAware(['8.0' => 'string|null'], default: '')] $displayLocale = null
): string|false {}














#[TentativeType]
public static function getDisplayLanguage(
#[TypeAware(['8.0' => 'string'], default: '')] $locale,
#[ElementAvailable(from: '5.3', to: '5.6')] $displayLocale,
#[ElementAvailable(from: '7.0')] #[TypeAware(['8.0' => 'string|null'], default: '')] $displayLocale = null
): string|false {}














#[TentativeType]
public static function getDisplayVariant(
#[TypeAware(['8.0' => 'string'], default: '')] $locale,
#[ElementAvailable(from: '5.3', to: '5.6')] $displayLocale,
#[ElementAvailable(from: '7.0')] #[TypeAware(['8.0' => 'string|null'], default: '')] $displayLocale = null
): string|false {}

























#[TentativeType]
public static function composeLocale(array $subtags): string|false {}

















#[TentativeType]
public static function parseLocale(#[TypeAware(['8.0' => 'string'], default: '')] $locale): ?array {}











#[TentativeType]
public static function getAllVariants(#[TypeAware(['8.0' => 'string'], default: '')] $locale): ?array {}

















#[TentativeType]
public static function filterMatches(
#[TypeAware(['8.0' => 'string'], default: '')] $languageTag,
#[TypeAware(['8.0' => 'string'], default: '')] $locale,
#[ElementAvailable(from: '5.3', to: '5.6')] $canonicalize,
#[ElementAvailable(from: '7.0')] #[TypeAware(['8.0' => 'bool'], default: '')] $canonicalize = false
): ?bool {}





















#[TentativeType]
public static function lookup(
array $languageTag,
#[TypeAware(['8.0' => 'string'], default: '')] $locale,
#[ElementAvailable(from: '5.3', to: '5.6')] $canonicalize,
#[ElementAvailable(from: '5.3', to: '5.6')] $defaultLocale,
#[ElementAvailable(from: '7.0')] #[TypeAware(['8.0' => 'bool'], default: '')] $canonicalize = false,
#[ElementAvailable(from: '7.0')] #[TypeAware(['8.0' => 'string|null'], default: '')] $defaultLocale = null
): ?string {}






#[TentativeType]
public static function canonicalize(#[TypeAware(['8.0' => 'string'], default: '')] $locale): ?string {}










#[TentativeType]
public static function acceptFromHttp(#[TypeAware(['8.0' => 'string'], default: '')] $header): string|false {}
}

class MessageFormatter
{















#[Pure]
public function __construct(
#[TypeAware(['8.0' => 'string'], default: '')] $locale,
#[TypeAware(['8.0' => 'string'], default: '')] $pattern
) {}
















#[TentativeType]
public static function create(
#[TypeAware(['8.0' => 'string'], default: '')] $locale,
#[TypeAware(['8.0' => 'string'], default: '')] $pattern
): ?MessageFormatter {}










#[Pure]
#[TentativeType]
public function format(array $values): string|false {}



















#[TentativeType]
public static function formatMessage(
#[TypeAware(['8.0' => 'string'], default: '')] $locale,
#[TypeAware(['8.0' => 'string'], default: '')] $pattern,
array $values
): string|false {}










#[Pure]
#[TentativeType]
public function parse(#[TypeAware(['8.0' => 'string'], default: '')] $string): array|false {}
















#[TentativeType]
public static function parseMessage(
#[TypeAware(['8.0' => 'string'], default: '')] $locale,
#[TypeAware(['8.0' => 'string'], default: '')] $pattern,
#[TypeAware(['8.0' => 'string'], default: '')] $message
): array|false {}













#[TentativeType]
public function setPattern(#[TypeAware(['8.0' => 'string'], default: '')] $pattern): bool {}







#[Pure]
#[TentativeType]
public function getPattern(): string|false {}







#[Pure]
#[TentativeType]
public function getLocale(): string {}







#[Pure]
#[TentativeType]
public function getErrorCode(): int {}







#[Pure]
#[TentativeType]
public function getErrorMessage(): string {}
}

class IntlDateFormatter
{




public const FULL = 0;





public const LONG = 1;





public const MEDIUM = 2;





public const SHORT = 3;





public const NONE = -1;





public const GREGORIAN = 1;





public const TRADITIONAL = 0;
public const RELATIVE_FULL = 128;
public const RELATIVE_LONG = 129;
public const RELATIVE_MEDIUM = 130;
public const RELATIVE_SHORT = 131;









#[Pure]
public function __construct(
#[TypeAware(['8.0' => 'string|null'], default: '')] $locale,
#[ElementAvailable(from: '5.3', to: '8.0')] #[TypeAware(['8.0' => 'int'], default: '')] $dateType,
#[ElementAvailable(from: '5.3', to: '8.0')] #[TypeAware(['8.0' => 'int'], default: '')] $timeType,
#[ElementAvailable(from: '8.1')] int $dateType = 0,
#[ElementAvailable(from: '8.1')] int $timeType = 0,
$timezone = null,
$calendar = null,
#[TypeAware(['8.0' => 'string|null'], default: '')] $pattern = ''
) {}




































#[TentativeType]
public static function create(
#[TypeAware(['8.0' => 'string|null'], default: '')] $locale,
#[ElementAvailable(from: '5.3', to: '8.0')] #[TypeAware(['8.0' => 'int'], default: '')] $dateType,
#[ElementAvailable(from: '5.3', to: '8.0')] #[TypeAware(['8.0' => 'int'], default: '')] $timeType,
#[ElementAvailable(from: '8.1')] int $dateType = 0,
#[ElementAvailable(from: '8.1')] int $timeType = 0,
$timezone = null,
#[TypeAware(['8.0' => 'IntlCalendar|int|null'], default: '')] $calendar = null,
#[TypeAware(['8.0' => 'string|null'], default: '')] $pattern = ''
): ?IntlDateFormatter {}







#[Pure]
#[TentativeType]
public function getDateType(): int|false {}







#[Pure]
#[TentativeType]
public function getTimeType(): int|false {}







#[Pure]
#[TentativeType]
public function getCalendar(): int|false {}











#[TentativeType]
public function setCalendar(#[TypeAware(['8.0' => 'IntlCalendar|int|null'], default: '')] $calendar): bool {}







#[Pure]
#[TentativeType]
public function getTimeZoneId(): string|false {}







#[Pure]
#[TentativeType]
public function getCalendarObject(): IntlCalendar|false|null {}







#[Pure]
#[TentativeType]
public function getTimeZone(): IntlTimeZone|false {}

/**
@removed









*/
#[Deprecated(replacement: "%class%->setTimeZone(%parametersList%)", since: "5.5")]
public function setTimeZoneId($zone) {}







































#[TentativeType]
public function setTimeZone($timezone): ?bool {}












#[TentativeType]
public function setPattern(#[TypeAware(['8.0' => 'string'], default: '')] $pattern): bool {}







#[Pure]
#[TentativeType]
public function getPattern(): string|false {}








#[Pure]
#[TentativeType]
public function getLocale(
#[ElementAvailable(from: '8.0')]
#[TypeAware(['8.0' => 'int'], default: '')]
$type = null
): string|false {}










#[TentativeType]
public function setLenient(#[TypeAware(['8.0' => 'bool'], default: '')] $lenient): void {}







#[Pure]
#[TentativeType]
public function isLenient(): bool {}













#[TentativeType]
public function format(
#[ElementAvailable(from: '5.3', to: '7.4')] $datetime = null,
#[ElementAvailable(from: '8.0')] $datetime,
#[ElementAvailable(from: '5.3', to: '7.4')] $array = null
): string|false {}

























#[TentativeType]
public static function formatObject($datetime, $format = null, #[TypeAware(['8.0' => 'string|null'], default: '')] $locale = null): string|false {}

















#[TentativeType]
public function parse(#[TypeAware(['8.0' => 'string'], default: '')] $string, &$offset = null): int|float|false {}
















#[TentativeType]
public function localtime(#[TypeAware(['8.0' => 'string'], default: '')] $string, &$offset = null): array|false {}







#[Pure]
#[TentativeType]
public function getErrorCode(): int {}







#[Pure]
#[TentativeType]
public function getErrorMessage(): string {}
}

class ResourceBundle implements IteratorAggregate, Countable
{






#[Pure]
public function __construct(
#[TypeAware(['8.0' => 'string|null'], default: '')] $locale,
#[TypeAware(['8.0' => 'string|null'], default: '')] $bundle,
#[TypeAware(['8.0' => 'bool'], default: '')] $fallback = true
) {}
















#[TentativeType]
public static function create(
#[TypeAware(['8.0' => 'string|null'], default: '')] $locale,
#[TypeAware(['8.0' => 'string|null'], default: '')] $bundle,
#[TypeAware(['8.0' => 'bool'], default: '')] $fallback = true
): ?ResourceBundle {}













#[Pure]
#[TentativeType]
public function get($index, #[TypeAware(['8.0' => 'bool'], default: '')] $fallback = true): mixed {}







#[Pure]
#[TentativeType]
public function count(): int {}











#[TentativeType]
public static function getLocales(#[TypeAware(['8.0' => 'string'], default: '')] $bundle): array|false {}







#[Pure]
#[TentativeType]
public function getErrorCode(): int {}







#[Pure]
#[TentativeType]
public function getErrorMessage(): string {}





#[Pure]
public function getIterator(): Iterator {}
}




class Transliterator
{
public const FORWARD = 0;
public const REVERSE = 1;

#[TypeAware(['8.1' => 'string'], default: '')]
public $id;






final private function __construct() {}

















#[TentativeType]
public static function create(
#[TypeAware(['8.0' => 'string'], default: '')] $id,
#[TypeAware(['8.0' => 'int'], default: '')] #[EV([Transliterator::FORWARD, Transliterator::REVERSE])] $direction = null
): ?Transliterator {}

















#[TentativeType]
public static function createFromRules(
#[TypeAware(['8.0' => 'string'], default: '')] $rules,
#[TypeAware(['8.0' => 'int'], default: '')] #[EV([Transliterator::FORWARD, Transliterator::REVERSE])] $direction = null
): ?Transliterator {}








#[Pure]
#[TentativeType]
public function createInverse(): ?Transliterator {}








#[TentativeType]
public static function listIDs(): array|false {}




















#[Pure]
#[TentativeType]
public function transliterate(
#[TypeAware(['8.0' => 'string'], default: '')] $string,
#[TypeAware(['8.0' => 'int'], default: '')] $start = null,
#[TypeAware(['8.0' => 'int'], default: '')] $end = -1
): string|false {}








#[Pure]
#[TentativeType]
public function getErrorCode(): int|false {}








#[Pure]
#[TentativeType]
public function getErrorMessage(): string|false {}
}




class Spoofchecker
{
public const SINGLE_SCRIPT_CONFUSABLE = 1;
public const MIXED_SCRIPT_CONFUSABLE = 2;
public const WHOLE_SCRIPT_CONFUSABLE = 4;
public const ANY_CASE = 8;
public const SINGLE_SCRIPT = 16;
public const INVISIBLE = 32;
public const CHAR_LIMIT = 64;
public const ASCII = 268435456;
public const HIGHLY_RESTRICTIVE = 805306368;
public const MODERATELY_RESTRICTIVE = 1073741824;
public const MINIMALLY_RESTRICTIVE = 1342177280;
public const UNRESTRICTIVE = 1610612736;
public const SINGLE_SCRIPT_RESTRICTIVE = 536870912;






#[Pure]
public function __construct() {}











#[TentativeType]
public function isSuspicious(#[TypeAware(['8.0' => 'string'], default: '')] $string, &$errorCode = null): bool {}













#[TentativeType]
public function areConfusable(
#[TypeAware(['8.0' => 'string'], default: '')] $string1,
#[TypeAware(['8.0' => 'string'], default: '')] $string2,
&$errorCode = null
): bool {}









#[TentativeType]
public function setAllowedLocales(#[TypeAware(['8.0' => 'string'], default: '')] $locales): void {}









#[TentativeType]
public function setChecks(#[TypeAware(['8.0' => 'int'], default: '')] $checks): void {}

#[TentativeType]
public function setRestrictionLevel(int $level): void {}
}




class IntlGregorianCalendar extends IntlCalendar
{









public function __construct($timezoneOrYear, $localeOrMonth, $day, $hour, $minute, $second) {}







public static function createInstance($timeZone = null, $locale = null) {}




#[TentativeType]
public function setGregorianChange(#[TypeAware(['8.0' => 'float'], default: '')] $timestamp): bool {}




#[Pure]
#[TentativeType]
public function getGregorianChange(): float {}





#[Pure]
#[TentativeType]
public function isLeapYear(#[TypeAware(['8.0' => 'int'], default: '')] $year): bool {}
}




class IntlCalendar
{

public const FIELD_ERA = 0;
public const FIELD_YEAR = 1;
public const FIELD_MONTH = 2;
public const FIELD_WEEK_OF_YEAR = 3;
public const FIELD_WEEK_OF_MONTH = 4;
public const FIELD_DATE = 5;
public const FIELD_DAY_OF_YEAR = 6;
public const FIELD_DAY_OF_WEEK = 7;
public const FIELD_DAY_OF_WEEK_IN_MONTH = 8;
public const FIELD_AM_PM = 9;
public const FIELD_HOUR = 10;
public const FIELD_HOUR_OF_DAY = 11;
public const FIELD_MINUTE = 12;
public const FIELD_SECOND = 13;
public const FIELD_MILLISECOND = 14;
public const FIELD_ZONE_OFFSET = 15;
public const FIELD_DST_OFFSET = 16;
public const FIELD_YEAR_WOY = 17;
public const FIELD_DOW_LOCAL = 18;
public const FIELD_EXTENDED_YEAR = 19;
public const FIELD_JULIAN_DAY = 20;
public const FIELD_MILLISECONDS_IN_DAY = 21;
public const FIELD_IS_LEAP_MONTH = 22;
public const FIELD_FIELD_COUNT = 23;
public const FIELD_DAY_OF_MONTH = 5;
public const DOW_SUNDAY = 1;
public const DOW_MONDAY = 2;
public const DOW_TUESDAY = 3;
public const DOW_WEDNESDAY = 4;
public const DOW_THURSDAY = 5;
public const DOW_FRIDAY = 6;
public const DOW_SATURDAY = 7;
public const DOW_TYPE_WEEKDAY = 0;
public const DOW_TYPE_WEEKEND = 1;
public const DOW_TYPE_WEEKEND_OFFSET = 2;
public const DOW_TYPE_WEEKEND_CEASE = 3;
public const WALLTIME_FIRST = 1;
public const WALLTIME_LAST = 0;
public const WALLTIME_NEXT_VALID = 2;
















#[TentativeType]
public function add(
#[TypeAware(['8.0' => 'int'], default: '')] $field,
#[TypeAware(['8.0' => 'int'], default: '')] $value
): bool {}












#[Pure]
#[TentativeType]
public function after(IntlCalendar $other): bool {}












#[Pure]
#[TentativeType]
public function before(IntlCalendar $other): bool {}












public function clear(#[TypeAware(['8.0' => 'int|null'], default: '')] $field = null) {}






private function __construct() {}














































#[TentativeType]
public static function createInstance($timezone = null, #[TypeAware(['8.0' => 'string|null'], default: '')] $locale = null): ?IntlCalendar {}














#[Pure]
#[TentativeType]
public function equals(#[TypeAware(['8.0' => 'IntlCalendar'], default: '')] $other): bool {}























#[Pure]
#[TentativeType]
public function fieldDifference(
#[TypeAware(['8.0' => 'float'], default: '')] $timestamp,
#[TypeAware(['8.0' => 'int'], default: '')] $field
): int|false {}















#[TentativeType]
public static function fromDateTime(
#[TypeAware(['8.0' => 'DateTime|string'], default: '')] $datetime,
#[TypeAware(['8.0' => 'string|null'], default: '')] #[ElementAvailable(from: '8.0')] $locale
): ?IntlCalendar {}












#[Pure]
#[TentativeType]
public function get(#[TypeAware(['8.0' => 'int'], default: '')] $field): int|false {}














#[Pure]
#[TentativeType]
public function getActualMaximum(#[TypeAware(['8.0' => 'int'], default: '')] $field): int|false {}














#[Pure]
#[TentativeType]
public function getActualMinimum(#[TypeAware(['8.0' => 'int'], default: '')] $field): int|false {}







#[TentativeType]
public static function getAvailableLocales(): array {}
















#[Pure]
#[TentativeType]
public function getDayOfWeekType(#[TypeAware(['8.0' => 'int'], default: '')] $dayOfWeek): int|false {}







#[Pure]
#[TentativeType]
public function getErrorCode(): int|false {}







#[Pure]
#[TentativeType]
public function getErrorMessage(): string|false {}










#[Pure]
#[TentativeType]
public function getFirstDayOfWeek(): int|false {}













#[Pure]
#[TentativeType]
public function getGreatestMinimum(#[TypeAware(['8.0' => 'int'], default: '')] $field): int|false {}

















#[TentativeType]
public static function getKeywordValuesForLocale(
#[TypeAware(['8.0' => 'string'], default: '')] $keyword,
#[TypeAware(['8.0' => 'string'], default: '')] $locale,
#[TypeAware(['8.0' => 'bool'], default: '')] $onlyCommon
): IntlIterator|false {}














#[Pure]
#[TentativeType]
public function getLeastMaximum(#[TypeAware(['8.0' => 'int'], default: '')] $field): int|false {}















#[Pure]
#[TentativeType]
public function getLocale(#[TypeAware(['8.0' => 'int'], default: '')] $type): string|false {}












#[Pure]
#[TentativeType]
public function getMaximum(#[TypeAware(['8.0' => 'int'], default: '')] $field): int|false {}








#[Pure]
#[TentativeType]
public function getMinimalDaysInFirstWeek(): int|false {}













#[Pure]
#[TentativeType]
public function getMinimum(#[TypeAware(['8.0' => 'int'], default: '')] $field): int|false {}






#[TentativeType]
public static function getNow(): float {}









#[Pure]
#[TentativeType]
public function getRepeatedWallTimeOption(): int {}










#[Pure]
#[TentativeType]
public function getSkippedWallTimeOption(): int {}








#[Pure]
#[TentativeType]
public function getTime(): float|false {}









#[Pure]
#[TentativeType]
public function getTimeZone(): IntlTimeZone|false {}









#[Pure]
#[TentativeType]
public function getType(): string {}














#[Pure]
#[TentativeType]
public function getWeekendTransition(#[TypeAware(['8.0' => 'int'], default: '')] $dayOfWeek): int|false {}











#[Pure]
#[TentativeType]
public function inDaylightTime(): bool {}









#[Pure]
#[TentativeType]
public function isEquivalentTo(IntlCalendar $other): bool {}








#[Pure]
#[TentativeType]
public function isLenient(): bool {}



















#[Pure]
#[TentativeType]
public function isWeekend(#[TypeAware(['8.0' => 'float|null'], default: '')] $timestamp = null): bool {}


















#[TentativeType]
public function roll(#[TypeAware(['8.0' => 'int'], default: '')] $field, $value): bool {}













#[TentativeType]
public function PS_UNRESERVE_PREFIX_isSet(#[TypeAware(['8.0' => 'int'], default: '')] $field): bool {}
































public function set($year, $month, $dayOfMonth = null, $hour = null, $minute = null, $second = null) {}










public function set($field, $value) {}












public function setFirstDayOfWeek(#[TypeAware(['8.0' => 'int'], default: '')] $dayOfWeek) {}










public function setLenient(#[TypeAware(['8.0' => 'bool'], default: '')] $lenient) {}












public function setRepeatedWallTimeOption(#[TypeAware(['8.0' => 'int'], default: '')] $option) {}















public function setSkippedWallTimeOption(#[TypeAware(['8.0' => 'int'], default: '')] $option) {}












#[TentativeType]
public function setTime(#[TypeAware(['8.0' => 'float'], default: '')] $timestamp): bool {}








































#[TentativeType]
public function setTimeZone($timezone): bool {}











#[Pure]
#[TentativeType]
public function toDateTime(): DateTime|false {}






public function setMinimalDaysInFirstWeek(#[TypeAware(['8.0' => 'int'], default: '')] $days) {}
}




class IntlIterator implements Iterator
{
#[TentativeType]
public function current(): mixed {}

#[TentativeType]
public function key(): mixed {}

#[TentativeType]
public function next(): void {}

#[TentativeType]
public function rewind(): void {}

#[TentativeType]
public function valid(): bool {}
}




class IntlException extends Exception {}




class IntlTimeZone
{

public const DISPLAY_SHORT = 1;
public const DISPLAY_LONG = 2;
public const DISPLAY_SHORT_GENERIC = 3;
public const DISPLAY_LONG_GENERIC = 4;
public const DISPLAY_SHORT_GMT = 5;
public const DISPLAY_LONG_GMT = 6;
public const DISPLAY_SHORT_COMMONLY_USED = 7;
public const DISPLAY_GENERIC_LOCATION = 8;
public const TYPE_ANY = 0;
public const TYPE_CANONICAL = 1;
public const TYPE_CANONICAL_LOCATION = 2;



private function __construct() {}








#[TentativeType]
public static function countEquivalentIDs(#[TypeAware(['8.0' => 'string'], default: '')] $timezoneId): int|false {}







#[TentativeType]
public static function createDefault(): IntlTimeZone {}








#[TentativeType]
public static function createEnumeration($countryOrRawOffset): IntlIterator|false {}








#[TentativeType]
public static function createTimeZone(#[TypeAware(['8.0' => 'string'], default: '')] $timezoneId): ?IntlTimeZone {}










#[TentativeType]
public static function createTimeZoneIDEnumeration(
#[TypeAware(['8.0' => 'int'], default: '')] $type,
#[TypeAware(['8.0' => 'string|null'], default: '')] $region = null,
#[TypeAware(['8.0' => 'int|null'], default: '')] $rawOffset = 0
): IntlIterator|false {}








#[TentativeType]
public static function fromDateTimeZone(#[TypeAware(['8.0' => 'DateTimeZone'], default: '')] $timezone): ?IntlTimeZone {}









#[TentativeType]
public static function getCanonicalID(#[TypeAware(['8.0' => 'string'], default: '')] $timezoneId, &$isSystemId): string|false {}









#[Pure]
#[TentativeType]
public function getDisplayName(
#[TypeAware(['8.0' => 'bool'], default: '')] $dst = false,
#[TypeAware(['8.0' => 'int'], default: '')] $style = 2,
#[TypeAware(['8.0' => 'string|null'], default: '')] $locale
): string|false {}







#[Pure]
#[TentativeType]
public function getDSTSavings(): int {}









#[TentativeType]
public static function getEquivalentID(
#[TypeAware(['8.0' => 'string'], default: '')] $timezoneId,
#[TypeAware(['8.0' => 'int'], default: '')] $offset
): string|false {}







#[Pure]
#[TentativeType]
public function getErrorCode(): int|false {}







#[Pure]
#[TentativeType]
public function getErrorMessage(): string|false {}







#[TentativeType]
public static function getGMT(): IntlTimeZone {}






#[Pure]
#[TentativeType]
public function getID(): string|false {}





















#[TentativeType]
public function getOffset(
#[TypeAware(['8.0' => 'float'], default: '')] $timestamp,
#[TypeAware(['8.0' => 'bool'], default: '')] $local,
&$rawOffset,
&$dstOffset
): bool {}







#[Pure]
#[TentativeType]
public function getRawOffset(): int {}








#[TentativeType]
public static function getRegion(#[TypeAware(['8.0' => 'string'], default: '')] $timezoneId): string|false {}







#[TentativeType]
public static function getTZDataVersion(): string|false {}







#[TentativeType]
public static function getUnknown(): IntlTimeZone {}










#[TentativeType]
public static function getWindowsID(string $timezoneId): string|false {}








#[TentativeType]
public static function getIDForWindowsID(string $timezoneId, ?string $region = null): string|false {}








#[Pure]
#[TentativeType]
public function hasSameRules(IntlTimeZone $other): bool {}







#[Pure]
#[TentativeType]
public function toDateTimeZone(): DateTimeZone|false {}







#[TentativeType]
public function useDaylightTime(): bool {}
}














#[Pure]
function collator_create(string $locale): ?Collator {}































#[Pure]
function collator_compare(Collator $object, string $string1, string $string2): int|false {}











#[Pure]
function collator_get_attribute(Collator $object, int $attribute): int|false {}












function collator_set_attribute(Collator $object, int $attribute, int $value): bool {}








#[Pure]
function collator_get_strength(Collator $object): int {}













function collator_set_strength(Collator $object, int $strength): bool {}


















function collator_sort(Collator $object, array &$array, int $flags = 0): bool {}









function collator_sort_with_sort_keys(
Collator $object,
array &$array,
#[ElementAvailable(from: '5.3', to: '5.6')] $sort_flags = []
): bool {}














function collator_asort(Collator $object, array &$array, int $flags = 0): bool {}
















#[Pure]
function collator_get_locale(Collator $object, int $type): string|false {}








#[Pure(true)]
function collator_get_error_code(Collator $object): int|false {}








#[Pure]
function collator_get_error_message(Collator $object): string|false {}











#[Pure]
function collator_get_sort_key(
Collator $object,
string $string,
#[ElementAvailable(from: '5.3', to: '5.6')] $arg3
): string|false {}

























#[Pure]
function numfmt_create(string $locale, int $style, #[TypeAware(['8.0' => 'string|null'], default: 'string')] $pattern = null): ?NumberFormatter {}
















#[Pure]
function numfmt_format(NumberFormatter $formatter, int|float $num, int $type = 0): string|false {}


















#[Pure]
function numfmt_parse(NumberFormatter $formatter, string $string, int $type = NumberFormatter::TYPE_DOUBLE, &$offset = null): int|float|false {}














#[Pure]
function numfmt_format_currency(NumberFormatter $formatter, float $amount, string $currency): string|false {}

















function numfmt_parse_currency(NumberFormatter $formatter, string $string, &$currency, &$offset = null): float|false {}















function numfmt_set_attribute(NumberFormatter $formatter, int $attribute, int|float $value): bool {}












#[Pure]
function numfmt_get_attribute(NumberFormatter $formatter, int $attribute): int|float|false {}
















function numfmt_set_text_attribute(NumberFormatter $formatter, int $attribute, string $value): bool {}












#[Pure]
function numfmt_get_text_attribute(NumberFormatter $formatter, int $attribute): string|false {}















function numfmt_set_symbol(NumberFormatter $formatter, int $symbol, string $value): bool {}












#[Pure]
function numfmt_get_symbol(NumberFormatter $formatter, int $symbol): string|false {}













function numfmt_set_pattern(NumberFormatter $formatter, string $pattern): bool {}








#[Pure]
function numfmt_get_pattern(NumberFormatter $formatter): string|false {}














#[Pure]
function numfmt_get_locale(NumberFormatter $formatter, int $type = 0): string|false {}








#[Pure(true)]
function numfmt_get_error_code(NumberFormatter $formatter): int {}








#[Pure(true)]
function numfmt_get_error_message(NumberFormatter $formatter): string {}









#[Pure]
function normalizer_normalize(string $string, int $form = Normalizer::FORM_C): string|false {}












#[Pure]
function normalizer_is_normalized(string $string, int $form = Normalizer::FORM_C): bool {}






#[Pure]
function locale_get_default(): string {}











function locale_set_default(string $locale): bool {}










#[Pure]
function locale_get_primary_language(string $locale): ?string {}










#[Pure]
function locale_get_script(string $locale): ?string {}










#[Pure]
function locale_get_region(string $locale): ?string {}










#[Pure]
function locale_get_keywords(string $locale): array|false|null {}














#[Pure]
function locale_get_display_script(
string $locale,
#[ElementAvailable(from: '5.3', to: '5.6')] ?string $displayLocale,
#[ElementAvailable(from: '7.0')] ?string $displayLocale = null
): string|false {}














#[Pure]
function locale_get_display_region(
string $locale,
#[ElementAvailable(from: '5.3', to: '5.6')] ?string $displayLocale,
#[ElementAvailable(from: '7.0')] ?string $displayLocale = null
): string|false {}











#[Pure]
function locale_get_display_name(
string $locale,
#[ElementAvailable(from: '5.3', to: '5.6')] ?string $displayLocale,
#[ElementAvailable(from: '7.0')] ?string $displayLocale = null
): string|false {}














#[Pure]
function locale_get_display_language(
string $locale,
#[ElementAvailable(from: '5.3', to: '5.6')] ?string $displayLocale,
#[ElementAvailable(from: '7.0')] ?string $displayLocale = null
): string|false {}














#[Pure]
function locale_get_display_variant(
string $locale,
#[ElementAvailable(from: '5.3', to: '5.6')] ?string $displayLocale,
#[ElementAvailable(from: '7.0')] ?string $displayLocale = null
): string|false {}

























#[Pure]
function locale_compose(array $subtags): string|false {}

















#[Pure]
function locale_parse(string $locale): ?array {}











#[Pure]
function locale_get_all_variants(string $locale): ?array {}

















#[Pure]
function locale_filter_matches(
string $languageTag,
string $locale,
#[ElementAvailable(from: '5.3', to: '5.6')] bool $canonicalize,
#[ElementAvailable(from: '7.0')] bool $canonicalize = false
): ?bool {}







#[Pure]
function locale_canonicalize(string $locale): ?string {}





















#[Pure]
function locale_lookup(
array $languageTag,
string $locale,
#[ElementAvailable(from: '5.3', to: '5.6')] bool $canonicalize,
#[ElementAvailable(from: '5.3', to: '5.6')] ?string $defaultLocale,
#[ElementAvailable(from: '7.0')] bool $canonicalize = false,
#[ElementAvailable(from: '7.0')] ?string $defaultLocale = null,
): ?string {}










#[Pure]
function locale_accept_from_http(string $header): string|false {}







#[Pure]
function msgfmt_create(string $locale, string $pattern): ?MessageFormatter {}











#[Pure]
function msgfmt_format(MessageFormatter $formatter, array $values): string|false {}



















#[Pure]
function msgfmt_format_message(string $locale, string $pattern, array $values): string|false {}











#[Pure]
function msgfmt_parse(MessageFormatter $formatter, string $string): array|false {}
















#[Pure]
function msgfmt_parse_message(string $locale, string $pattern, string $message): array|false {}














function msgfmt_set_pattern(MessageFormatter $formatter, string $pattern): bool {}








#[Pure]
function msgfmt_get_pattern(MessageFormatter $formatter): string|false {}








#[Pure]
function msgfmt_get_locale(MessageFormatter $formatter): string {}








#[Pure(true)]
function msgfmt_get_error_code(MessageFormatter $formatter): int {}








#[Pure(true)]
function msgfmt_get_error_message(MessageFormatter $formatter): string {}




































#[Pure]
function datefmt_create(
?string $locale,
#[ElementAvailable(from: '5.3', to: '8.0')] int $dateType,
#[ElementAvailable(from: '8.1')] int $dateType = 0,
#[ElementAvailable(from: '5.3', to: '8.0')] int $timeType,
#[ElementAvailable(from: '8.1')] int $timeType = 0,
$timezone = null,
IntlCalendar|int|null $calendar = null,
#[TypeAware(['8.0' => 'string|null'], default: 'string')] $pattern = null
): ?IntlDateFormatter {}








#[Pure]
function datefmt_get_datetype(IntlDateFormatter $formatter): int|false {}








#[Pure]
function datefmt_get_timetype(IntlDateFormatter $formatter): int|false {}








#[Pure]
function datefmt_get_calendar(IntlDateFormatter $formatter): int|false {}












function datefmt_set_calendar(IntlDateFormatter $formatter, IntlCalendar|int|null $calendar): bool {}









#[Pure]
function datefmt_get_locale(
IntlDateFormatter $formatter,
#[ElementAvailable(from: '8.0')] int $type = ULOC_ACTUAL_LOCALE
): string|false {}








#[Pure]
function datefmt_get_timezone_id(IntlDateFormatter $formatter): string|false {}








#[Pure]
function datefmt_get_calendar_object(IntlDateFormatter $formatter): IntlCalendar|false|null {}








#[Pure]
function datefmt_get_timezone(IntlDateFormatter $formatter): IntlTimeZone|false {}

/**
@removed










*/
#[Deprecated(replacement: "datefmt_set_timezone(%parametersList%)", since: "5.5")]
function datefmt_set_timezone_id(MessageFormatter $mf, $zone) {}








































function datefmt_set_timezone(IntlDateFormatter $formatter, $timezone): ?bool {}








#[Pure]
function datefmt_get_pattern(IntlDateFormatter $formatter): string|false {}













function datefmt_set_pattern(IntlDateFormatter $formatter, string $pattern): bool {}








#[Pure]
function datefmt_is_lenient(IntlDateFormatter $formatter): bool {}











function datefmt_set_lenient(
IntlDateFormatter $formatter,
#[ElementAvailable(from: '8.0')] bool $lenient
): void {}














#[Pure]
function datefmt_format(
#[TypeAware(['8.0' => 'IntlDateFormatter'], default: '')] #[ElementAvailable(from: '5.3', to: '7.4')] $formatter = null,
#[TypeAware(['8.0' => 'IntlDateFormatter'], default: '')] #[ElementAvailable(from: '8.0')] $formatter,
#[ElementAvailable(from: '5.3', to: '7.4')] $datetime = null,
#[ElementAvailable(from: '8.0')] $datetime
): string|false {}

























#[Pure]
function datefmt_format_object($datetime, $format = null, ?string $locale = null): string|false {}


















function datefmt_parse(IntlDateFormatter $formatter, string $string, &$offset = null): int|float|false {}

















function datefmt_localtime(IntlDateFormatter $formatter, string $string, &$offset = null): array|false {}








#[Pure(true)]
function datefmt_get_error_code(IntlDateFormatter $formatter): int {}








#[Pure(true)]
function datefmt_get_error_message(IntlDateFormatter $formatter): string {}










#[Pure]
function grapheme_strlen(string $string): int|false|null {}



















#[Pure]
function grapheme_strpos(string $haystack, string $needle, int $offset = 0): int|false {}



















#[Pure]
function grapheme_stripos(string $haystack, string $needle, int $offset = 0): int|false {}



















#[Pure]
function grapheme_strrpos(string $haystack, string $needle, int $offset = 0): int|false {}



















#[Pure]
function grapheme_strripos(string $haystack, string $needle, int $offset = 0): int|false {}




























#[Pure]
function grapheme_substr(string $string, int $offset, ?int $length = null): string|false {}

















#[Pure]
function grapheme_strstr(string $haystack, string $needle, bool $beforeNeedle = false): string|false {}

















#[Pure]
function grapheme_stristr(string $haystack, string $needle, bool $beforeNeedle = false): string|false {}



































function grapheme_extract(string $haystack, int $size, int $type = 0, int $offset = 0, &$next = null): string|false {}



























function idn_to_ascii(string $domain, int $flags = 0, int $variant = INTL_IDNA_VARIANT_UTS46, &$idna_info): string|false {}



























function idn_to_utf8(string $domain, int $flags = 0, int $variant = INTL_IDNA_VARIANT_UTS46, &$idna_info): string|false {}















































#[Pure]
function intlcal_create_instance($timezone = null, ?string $locale = null): ?IntlCalendar {}


















#[Pure]
function intlcal_get_keyword_values_for_locale(string $keyword, string $locale, bool $onlyCommon): IntlIterator|false {}








#[Pure(true)]
function intlcal_get_now(): float {}








#[Pure]
function intlcal_get_available_locales(): array {}
















#[Pure]
function intl_get($calendar, $field) {}










#[Pure]
#[TypeAware(['8.0' => 'float|false'], default: 'float')]
function intlcal_get_time(IntlCalendar $calendar) {}
















function intlcal_set_time(IntlCalendar $calendar, float $timestamp): bool {}


















function intlcal_add(IntlCalendar $calendar, int $field, int $value): bool {}












































function intlcal_set_time_zone(IntlCalendar $calendar, $timezone): bool {}
















#[Pure]
function intlcal_after(IntlCalendar $calendar, IntlCalendar $other): bool {}


















#[Pure]
function intlcal_before(IntlCalendar $calendar, IntlCalendar $other): bool {}




































function intlcal_set(IntlCalendar $calendar, int $year, int $month, int $dayOfMonth = null, int $hour = null, int $minute = null, int $second = null): bool {}






















function intlcal_roll(
IntlCalendar $calendar,
int $field,
#[ElementAvailable(from: '5.3', to: '7.4')] $value = null,
#[ElementAvailable(from: '8.0')] $value
): bool {}
















function intlcal_clear(IntlCalendar $calendar, ?int $field = null): bool {}



























#[Pure]
#[TypeAware(['8.0' => 'int|false'], default: 'int')]
function intlcal_field_difference(IntlCalendar $calendar, float $timestamp, int $field) {}


















#[Pure]
#[TypeAware(['8.0' => 'int|false'], default: 'int')]
function intlcal_get_actual_maximum(IntlCalendar $calendar, int $field) {}


















#[Pure]
#[TypeAware(['8.0' => 'int|false'], default: 'int')]
function intlcal_get_actual_minimum(IntlCalendar $calendar, int $field) {}





















#[Pure]
#[TypeAware(['8.0' => 'int|false'], default: 'int')]
function intlcal_get_day_of_week_type(IntlCalendar $calendar, int $dayOfWeek) {}














#[Pure]
#[TypeAware(['8.0' => 'int|false'], default: 'int')]
function intlcal_get_first_day_of_week(IntlCalendar $calendar) {}

















#[Pure]
function intlcal_greates_minimum($calendar, $field) {}













#[Pure]
#[TypeAware(['8.0' => 'int|false'], default: 'int')]
function intlcal_get(IntlCalendar $calendar, int $field) {}



















#[Pure]
#[TypeAware(['8.0' => 'int|false'], default: 'int')]
function intlcal_get_least_maximum(IntlCalendar $calendar, int $field) {}

















#[Pure]
#[TypeAware(['8.0' => 'int|false'], default: 'int')]
function intlcal_get_greatest_minimum(IntlCalendar $calendar, int $field) {}




















#[Pure]
#[TypeAware(['8.0' => 'string|false'], default: 'string')]
function intlcal_get_locale(IntlCalendar $calendar, int $type) {}
















#[Pure]
function intcal_get_maximum($calendar, $field) {}












#[Pure]
#[TypeAware(['8.0' => 'int|false'], default: 'int')]
function intlcal_get_minimal_days_in_first_week(IntlCalendar $calendar) {}

















#[Pure]
#[TypeAware(['8.0' => 'int|false'], default: 'int')]
function intlcal_get_minimum(IntlCalendar $calendar, int $field) {}













#[Pure]
function intlcal_get_time_zone(IntlCalendar $calendar): IntlTimeZone|false {}













#[Pure]
function intlcal_get_type(IntlCalendar $calendar): string {}


















#[Pure]
#[TypeAware(['8.0' => 'int|false'], default: 'int')]
function intlcal_get_weekend_transition(IntlCalendar $calendar, int $dayOfWeek) {}















#[Pure]
function intlcal_in_daylight_time(IntlCalendar $calendar): bool {}












#[Pure]
function intlcal_is_lenient(IntlCalendar $calendar): bool {}
















#[Pure]
function intlcal_is_set(IntlCalendar $calendar, int $field): bool {}
















#[Pure]
#[TypeAware(['8.0' => 'int|false'], default: 'int')]
function intlcal_get_maximum(IntlCalendar $calendar, int $field) {}













#[Pure]
function intlcal_is_equivalent_to(IntlCalendar $calendar, IntlCalendar $other): bool {}























#[Pure]
function intlcal_is_weekend(IntlCalendar $calendar, ?float $timestamp = null): bool {}
















function intlcal_set_first_day_of_week(IntlCalendar $calendar, int $dayOfWeek): bool {}














function intlcal_set_lenient(IntlCalendar $calendar, bool $lenient): bool {}













#[Pure]
function intlcal_get_repeated_wall_time_option(IntlCalendar $calendar): int {}


















#[Pure]
function intlcal_equals(IntlCalendar $calendar, IntlCalendar $other): bool {}














#[Pure]
function intlcal_get_skipped_wall_time_option(IntlCalendar $calendar): int {}
















function intlcal_set_repeated_wall_time_option(IntlCalendar $calendar, int $option): bool {}



















function intlcal_set_skipped_wall_time_option(IntlCalendar $calendar, int $option): bool {}
















#[Pure]
function intlcal_from_date_time(
DateTime|string $datetime,
#[ElementAvailable(from: '8.0')] ?string $locale = null
): ?IntlCalendar {}















#[Pure]
function intlcal_to_date_time(IntlCalendar $calendar): DateTime|false {}











#[Pure(true)]
function intlcal_get_error_code(IntlCalendar $calendar): int|false {}











#[Pure(true)]
function intlcal_get_error_message(IntlCalendar $calendar): string|false {}









#[Pure]
function intltz_count_equivalent_ids(string $timezoneId): int|false {}








#[Pure]
function intlz_create_default() {}








#[Pure]
function intltz_create_enumeration($countryOrRawOffset): IntlIterator|false {}








#[Pure]
function intltz_create_time_zone(string $timezoneId): ?IntlTimeZone {}








#[Pure]
function intltz_from_date_time_zone(DateTimeZone $timezone): ?IntlTimeZone {}










#[Pure]
function intltz_get_canonical_id(string $timezoneId, &$isSystemId): string|false {}













#[Pure]
function intltz_get_display_name(IntlTimeZone $timezone, bool $dst = false, int $style = 2, ?string $locale): string|false {}











#[Pure]
function intltz_get_dst_savings(IntlTimeZone $timezone): int {}










#[Pure]
function intltz_get_equivalent_id(string $timezoneId, int $offset): string|false {}











#[Pure(true)]
function intltz_get_error_code(IntlTimeZone $timezone): int|false {}











#[Pure(true)]
function intltz_get_error_message(IntlTimeZone $timezone): string|false {}








#[Pure]
function intltz_getGMT(): IntlTimeZone {}









#[Pure]
function intltz_get_id(IntlTimeZone $timezone): string|false {}













#[Pure]
function intltz_get_offset(IntlTimeZone $timezone, float $timestamp, bool $local, &$rawOffset, &$dstOffset): bool {}







#[Pure]
function intltz_get_raw_offset(IntlTimeZone $timezone): int {}








#[Pure]
function intltz_get_tz_data_version(): string|false {}










#[Pure]
function intltz_has_same_rules(
IntlTimeZone $timezone,
#[ElementAvailable(from: '5.5', to: '7.4')] IntlTimeZone $other = null,
#[ElementAvailable(from: '8.0')] IntlTimeZone $other
): bool {}









#[Pure]
function intltz_to_date_time_zone(IntlTimeZone $timezone): DateTimeZone|false {}









#[Pure]
function intltz_use_daylight_time(IntlTimeZone $timezone): bool {}












#[Pure]
function intlgregcal_create_instance($timezoneOrYear = null, $localeOrMonth = null, $day = null, $hour = null, $minute = null, $second = null): ?IntlGregorianCalendar {}






function intlgregcal_set_gregorian_change(IntlGregorianCalendar $calendar, float $timestamp): bool {}





#[Pure]
function intlgregcal_get_gregorian_change(IntlGregorianCalendar $calendar): float {}






#[Pure]
function intlgregcal_is_leap_year(IntlGregorianCalendar $calendar, int $year): bool {}
















#[Pure]
function resourcebundle_create(?string $locale, ?string $bundle, bool $fallback = true): ?ResourceBundle {}














#[Pure]
function resourcebundle_get(ResourceBundle $bundle, $index, bool $fallback = true): mixed {}








#[Pure]
function resourcebundle_count(ResourceBundle $bundle): int {}











#[Pure]
function resourcebundle_locales(string $bundle): array|false {}








#[Pure(true)]
function resourcebundle_get_error_code(ResourceBundle $bundle): int {}








#[Pure(true)]
function resourcebundle_get_error_message(ResourceBundle $bundle): string {}


















#[Pure]
function transliterator_create(string $id, int $direction = 0): ?Transliterator {}


















#[Pure]
function transliterator_create_from_rules(string $rules, int $direction = 0): ?Transliterator {}









#[Pure]
function transliterator_list_ids(): array|false {}










#[Pure]
function transliterator_create_inverse(Transliterator $transliterator): ?Transliterator {}






















#[Pure]
function transliterator_transliterate(Transliterator|string $transliterator, string $string, int $start = 0, int $end = -1): string|false {}










#[Pure(true)]
function transliterator_get_error_code(Transliterator $transliterator): int|false {}










#[Pure(true)]
function transliterator_get_error_message(Transliterator $transliterator): string|false {}







#[Pure(true)]
function intl_get_error_code(): int {}







#[Pure(true)]
function intl_get_error_message(): string {}













#[Pure]
function intl_is_failure(int $errorCode): bool {}











#[Pure]
function intl_error_name(int $errorCode): string {}












#[Pure]
function normalizer_get_raw_decomposition(string $string, #[ElementAvailable(from: '8.0')] int $form = Normalizer::FORM_C): ?string {}





#[Pure]
function intltz_create_default(): IntlTimeZone {}





#[Pure]
function intltz_get_gmt(): IntlTimeZone {}





#[Pure]
function intltz_get_unknown(): IntlTimeZone {}








#[Pure]
function intltz_create_time_zone_id_enumeration(int $type, ?string $region = null, ?int $rawOffset = null): IntlIterator|false {}






#[Pure]
function intltz_get_region(string $timezoneId): string|false {}












function intlcal_set_minimal_days_in_first_week(IntlCalendar $calendar, int $days): bool {}

function intltz_get_windows_id(string $timezoneId): string|false {}

function intltz_get_id_for_windows_id(string $timezoneId, ?string $region = null): string|false {}






define('INTL_MAX_LOCALE_LEN', 156);
define('INTL_ICU_VERSION', "71.1");
define('INTL_ICU_DATA_VERSION', "71.1");
define('ULOC_ACTUAL_LOCALE', 0);
define('ULOC_VALID_LOCALE', 1);
define('GRAPHEME_EXTR_COUNT', 0);
define('GRAPHEME_EXTR_MAXBYTES', 1);
define('GRAPHEME_EXTR_MAXCHARS', 2);
define('U_USING_FALLBACK_WARNING', -128);
define('U_ERROR_WARNING_START', -128);
define('U_USING_DEFAULT_WARNING', -127);
define('U_SAFECLONE_ALLOCATED_WARNING', -126);
define('U_STATE_OLD_WARNING', -125);
define('U_STRING_NOT_TERMINATED_WARNING', -124);
define('U_SORT_KEY_TOO_SHORT_WARNING', -123);
define('U_AMBIGUOUS_ALIAS_WARNING', -122);
define('U_DIFFERENT_UCA_VERSION', -121);
define('U_ERROR_WARNING_LIMIT', -119);
define('U_ZERO_ERROR', 0);
define('U_ILLEGAL_ARGUMENT_ERROR', 1);
define('U_MISSING_RESOURCE_ERROR', 2);
define('U_INVALID_FORMAT_ERROR', 3);
define('U_FILE_ACCESS_ERROR', 4);
define('U_INTERNAL_PROGRAM_ERROR', 5);
define('U_MESSAGE_PARSE_ERROR', 6);
define('U_MEMORY_ALLOCATION_ERROR', 7);
define('U_INDEX_OUTOFBOUNDS_ERROR', 8);
define('U_PARSE_ERROR', 9);
define('U_INVALID_CHAR_FOUND', 10);
define('U_TRUNCATED_CHAR_FOUND', 11);
define('U_ILLEGAL_CHAR_FOUND', 12);
define('U_INVALID_TABLE_FORMAT', 13);
define('U_INVALID_TABLE_FILE', 14);
define('U_BUFFER_OVERFLOW_ERROR', 15);
define('U_UNSUPPORTED_ERROR', 16);
define('U_RESOURCE_TYPE_MISMATCH', 17);
define('U_ILLEGAL_ESCAPE_SEQUENCE', 18);
define('U_UNSUPPORTED_ESCAPE_SEQUENCE', 19);
define('U_NO_SPACE_AVAILABLE', 20);
define('U_CE_NOT_FOUND_ERROR', 21);
define('U_PRIMARY_TOO_LONG_ERROR', 22);
define('U_STATE_TOO_OLD_ERROR', 23);
define('U_TOO_MANY_ALIASES_ERROR', 24);
define('U_ENUM_OUT_OF_SYNC_ERROR', 25);
define('U_INVARIANT_CONVERSION_ERROR', 26);
define('U_INVALID_STATE_ERROR', 27);
define('U_COLLATOR_VERSION_MISMATCH', 28);
define('U_USELESS_COLLATOR_ERROR', 29);
define('U_NO_WRITE_PERMISSION', 30);
define('U_STANDARD_ERROR_LIMIT', 32);
define('U_BAD_VARIABLE_DEFINITION', 65536);
define('U_PARSE_ERROR_START', 65536);
define('U_MALFORMED_RULE', 65537);
define('U_MALFORMED_SET', 65538);
define('U_MALFORMED_SYMBOL_REFERENCE', 65539);
define('U_MALFORMED_UNICODE_ESCAPE', 65540);
define('U_MALFORMED_VARIABLE_DEFINITION', 65541);
define('U_MALFORMED_VARIABLE_REFERENCE', 65542);
define('U_MISMATCHED_SEGMENT_DELIMITERS', 65543);
define('U_MISPLACED_ANCHOR_START', 65544);
define('U_MISPLACED_CURSOR_OFFSET', 65545);
define('U_MISPLACED_QUANTIFIER', 65546);
define('U_MISSING_OPERATOR', 65547);
define('U_MISSING_SEGMENT_CLOSE', 65548);
define('U_MULTIPLE_ANTE_CONTEXTS', 65549);
define('U_MULTIPLE_CURSORS', 65550);
define('U_MULTIPLE_POST_CONTEXTS', 65551);
define('U_TRAILING_BACKSLASH', 65552);
define('U_UNDEFINED_SEGMENT_REFERENCE', 65553);
define('U_UNDEFINED_VARIABLE', 65554);
define('U_UNQUOTED_SPECIAL', 65555);
define('U_UNTERMINATED_QUOTE', 65556);
define('U_RULE_MASK_ERROR', 65557);
define('U_MISPLACED_COMPOUND_FILTER', 65558);
define('U_MULTIPLE_COMPOUND_FILTERS', 65559);
define('U_INVALID_RBT_SYNTAX', 65560);
define('U_INVALID_PROPERTY_PATTERN', 65561);
define('U_MALFORMED_PRAGMA', 65562);
define('U_UNCLOSED_SEGMENT', 65563);
define('U_ILLEGAL_CHAR_IN_SEGMENT', 65564);
define('U_VARIABLE_RANGE_EXHAUSTED', 65565);
define('U_VARIABLE_RANGE_OVERLAP', 65566);
define('U_ILLEGAL_CHARACTER', 65567);
define('U_INTERNAL_TRANSLITERATOR_ERROR', 65568);
define('U_INVALID_ID', 65569);
define('U_INVALID_FUNCTION', 65570);
define('U_PARSE_ERROR_LIMIT', 65571);
define('U_UNEXPECTED_TOKEN', 65792);
define('U_FMT_PARSE_ERROR_START', 65792);
define('U_MULTIPLE_DECIMAL_SEPARATORS', 65793);
define('U_MULTIPLE_DECIMAL_SEPERATORS', 65793);
define('U_MULTIPLE_EXPONENTIAL_SYMBOLS', 65794);
define('U_MALFORMED_EXPONENTIAL_PATTERN', 65795);
define('U_MULTIPLE_PERCENT_SYMBOLS', 65796);
define('U_MULTIPLE_PERMILL_SYMBOLS', 65797);
define('U_MULTIPLE_PAD_SPECIFIERS', 65798);
define('U_PATTERN_SYNTAX_ERROR', 65799);
define('U_ILLEGAL_PAD_POSITION', 65800);
define('U_UNMATCHED_BRACES', 65801);
define('U_UNSUPPORTED_PROPERTY', 65802);
define('U_UNSUPPORTED_ATTRIBUTE', 65803);
define('U_FMT_PARSE_ERROR_LIMIT', 65812);
define('U_BRK_INTERNAL_ERROR', 66048);
define('U_BRK_ERROR_START', 66048);
define('U_BRK_HEX_DIGITS_EXPECTED', 66049);
define('U_BRK_SEMICOLON_EXPECTED', 66050);
define('U_BRK_RULE_SYNTAX', 66051);
define('U_BRK_UNCLOSED_SET', 66052);
define('U_BRK_ASSIGN_ERROR', 66053);
define('U_BRK_VARIABLE_REDFINITION', 66054);
define('U_BRK_MISMATCHED_PAREN', 66055);
define('U_BRK_NEW_LINE_IN_QUOTED_STRING', 66056);
define('U_BRK_UNDEFINED_VARIABLE', 66057);
define('U_BRK_INIT_ERROR', 66058);
define('U_BRK_RULE_EMPTY_SET', 66059);
define('U_BRK_UNRECOGNIZED_OPTION', 66060);
define('U_BRK_MALFORMED_RULE_TAG', 66061);
define('U_BRK_ERROR_LIMIT', 66062);
define('U_REGEX_INTERNAL_ERROR', 66304);
define('U_REGEX_ERROR_START', 66304);
define('U_REGEX_RULE_SYNTAX', 66305);
define('U_REGEX_INVALID_STATE', 66306);
define('U_REGEX_BAD_ESCAPE_SEQUENCE', 66307);
define('U_REGEX_PROPERTY_SYNTAX', 66308);
define('U_REGEX_UNIMPLEMENTED', 66309);
define('U_REGEX_MISMATCHED_PAREN', 66310);
define('U_REGEX_NUMBER_TOO_BIG', 66311);
define('U_REGEX_BAD_INTERVAL', 66312);
define('U_REGEX_MAX_LT_MIN', 66313);
define('U_REGEX_INVALID_BACK_REF', 66314);
define('U_REGEX_INVALID_FLAG', 66315);
define('U_REGEX_LOOK_BEHIND_LIMIT', 66316);
define('U_REGEX_SET_CONTAINS_STRING', 66317);
define('U_REGEX_ERROR_LIMIT', 66326);
define('U_IDNA_PROHIBITED_ERROR', 66560);
define('U_IDNA_ERROR_START', 66560);
define('U_IDNA_UNASSIGNED_ERROR', 66561);
define('U_IDNA_CHECK_BIDI_ERROR', 66562);
define('U_IDNA_STD3_ASCII_RULES_ERROR', 66563);
define('U_IDNA_ACE_PREFIX_ERROR', 66564);
define('U_IDNA_VERIFICATION_ERROR', 66565);
define('U_IDNA_LABEL_TOO_LONG_ERROR', 66566);
define('U_IDNA_ZERO_LENGTH_LABEL_ERROR', 66567);
define('U_IDNA_DOMAIN_NAME_TOO_LONG_ERROR', 66568);
define('U_IDNA_ERROR_LIMIT', 66569);
define('U_STRINGPREP_PROHIBITED_ERROR', 66560);
define('U_STRINGPREP_UNASSIGNED_ERROR', 66561);
define('U_STRINGPREP_CHECK_BIDI_ERROR', 66562);
define('U_ERROR_LIMIT', 66818);






define('IDNA_DEFAULT', 0);





define('IDNA_ALLOW_UNASSIGNED', 1);





define('IDNA_USE_STD3_RULES', 2);






define('IDNA_CHECK_BIDI', 4);






define('IDNA_CHECK_CONTEXTJ', 8);







define('IDNA_NONTRANSITIONAL_TO_ASCII', 16);







define('IDNA_NONTRANSITIONAL_TO_UNICODE', 32);







define('INTL_IDNA_VARIANT_2003', 0);






define('INTL_IDNA_VARIANT_UTS46', 1);







define('IDNA_ERROR_EMPTY_LABEL', 1);




define('IDNA_ERROR_LABEL_TOO_LONG', 2);




define('IDNA_ERROR_DOMAIN_NAME_TOO_LONG', 4);




define('IDNA_ERROR_LEADING_HYPHEN', 8);




define('IDNA_ERROR_TRAILING_HYPHEN', 16);




define('IDNA_ERROR_HYPHEN_3_4', 32);




define('IDNA_ERROR_LEADING_COMBINING_MARK', 64);




define('IDNA_ERROR_DISALLOWED', 128);




define('IDNA_ERROR_PUNYCODE', 256);




define('IDNA_ERROR_LABEL_HAS_DOT', 512);




define('IDNA_ERROR_INVALID_ACE_LABEL', 1024);




define('IDNA_ERROR_BIDI', 2048);




define('IDNA_ERROR_CONTEXTJ', 4096);




class IntlBreakIterator implements IteratorAggregate
{

public const DONE = -1;
public const WORD_NONE = 0;
public const WORD_NONE_LIMIT = 100;
public const WORD_NUMBER = 100;
public const WORD_NUMBER_LIMIT = 200;
public const WORD_LETTER = 200;
public const WORD_LETTER_LIMIT = 300;
public const WORD_KANA = 300;
public const WORD_KANA_LIMIT = 400;
public const WORD_IDEO = 400;
public const WORD_IDEO_LIMIT = 500;
public const LINE_SOFT = 0;
public const LINE_SOFT_LIMIT = 100;
public const LINE_HARD = 100;
public const LINE_HARD_LIMIT = 200;
public const SENTENCE_TERM = 0;
public const SENTENCE_TERM_LIMIT = 100;
public const SENTENCE_SEP = 100;
public const SENTENCE_SEP_LIMIT = 200;






private function __construct() {}








#[TentativeType]
public static function createCharacterInstance(#[TypeAware(['8.0' => 'string|null'], default: '')] $locale = null): ?IntlBreakIterator {}







#[TentativeType]
public static function createCodePointInstance(): IntlCodePointBreakIterator {}








#[TentativeType]
public static function createLineInstance(#[TypeAware(['8.0' => 'string|null'], default: '')] $locale): ?IntlBreakIterator {}








#[TentativeType]
public static function createSentenceInstance(#[TypeAware(['8.0' => 'string|null'], default: '')] $locale): ?IntlBreakIterator {}








#[TentativeType]
public static function createTitleInstance(#[TypeAware(['8.0' => 'string|null'], default: '')] $locale): ?IntlBreakIterator {}








#[TentativeType]
public static function createWordInstance(#[TypeAware(['8.0' => 'string|null'], default: '')] $locale): ?IntlBreakIterator {}







#[Pure]
#[TentativeType]
public function current(): int {}






#[TentativeType]
public function first(): int {}







#[TentativeType]
public function following(#[TypeAware(['8.0' => 'int'], default: '')] $offset): int {}







#[Pure]
#[TentativeType]
public function getErrorCode(): int {}







#[Pure]
#[TentativeType]
public function getErrorMessage(): string {}







#[Pure]
#[TentativeType]
public function getLocale(#[TypeAware(['8.0' => 'int'], default: '')] $type): string|false {}























#[Pure]
#[TentativeType]
public function getPartsIterator(#[TypeAware(['8.0' => 'string'], default: '')] $type = IntlPartsIterator::KEY_SEQUENTIAL): IntlPartsIterator {}






#[Pure]
#[TentativeType]
public function getText(): ?string {}







#[Pure]
#[TentativeType]
public function isBoundary(#[TypeAware(['8.0' => 'int'], default: '')] $offset): bool {}







#[TentativeType]
public function last(): int {}







#[TentativeType]
public function next(#[TypeAware(['8.0' => 'int|null'], default: '')] $offset = null): int {}






#[TentativeType]
public function preceding(#[TypeAware(['8.0' => 'int'], default: '')] $offset): int {}







#[TentativeType]
public function previous(): int {}







#[TentativeType]
public function setText(#[TypeAware(['8.0' => 'string'], default: '')] $text): ?bool {}





#[Pure]
public function getIterator(): Iterator {}
}

class IntlRuleBasedBreakIterator extends IntlBreakIterator implements Traversable
{







#[Pure]
public function __construct(
#[TypeAware(['8.0' => 'string'], default: '')] $rules,
#[TypeAware(['8.0' => 'bool'], default: '')] $compiled = false
) {}








public static function createCharacterInstance($locale) {}







public static function createCodePointInstance() {}








public static function createLineInstance($locale) {}








public static function createSentenceInstance($locale) {}








public static function createTitleInstance($locale) {}








public static function createWordInstance($locale) {}







#[Pure]
#[TentativeType]
public function getBinaryRules(): string|false {}







#[Pure]
#[TentativeType]
public function getRules(): string|false {}







#[Pure]
#[TentativeType]
public function getRuleStatus(): int {}







#[Pure]
#[TentativeType]
public function getRuleStatusVec(): array|false {}
}





class IntlPartsIterator extends IntlIterator implements Iterator
{
public const KEY_SEQUENTIAL = 0;
public const KEY_LEFT = 1;
public const KEY_RIGHT = 2;




#[Pure]
#[TentativeType]
public function getBreakIterator(): IntlBreakIterator {}




#[TentativeType]
public function getRuleStatus(): int {}
}

class IntlCodePointBreakIterator extends IntlBreakIterator implements Traversable
{






#[Pure]
#[TentativeType]
public function getLastCodePoint(): int {}
}

class UConverter
{

public const REASON_UNASSIGNED = 0;
public const REASON_ILLEGAL = 1;
public const REASON_IRREGULAR = 2;
public const REASON_RESET = 3;
public const REASON_CLOSE = 4;
public const REASON_CLONE = 5;
public const UNSUPPORTED_CONVERTER = -1;
public const SBCS = 0;
public const DBCS = 1;
public const MBCS = 2;
public const LATIN_1 = 3;
public const UTF8 = 4;
public const UTF16_BigEndian = 5;
public const UTF16_LittleEndian = 6;
public const UTF32_BigEndian = 7;
public const UTF32_LittleEndian = 8;
public const EBCDIC_STATEFUL = 9;
public const ISO_2022 = 10;
public const LMBCS_1 = 11;
public const LMBCS_2 = 12;
public const LMBCS_3 = 13;
public const LMBCS_4 = 14;
public const LMBCS_5 = 15;
public const LMBCS_6 = 16;
public const LMBCS_8 = 17;
public const LMBCS_11 = 18;
public const LMBCS_16 = 19;
public const LMBCS_17 = 20;
public const LMBCS_18 = 21;
public const LMBCS_19 = 22;
public const LMBCS_LAST = 22;
public const HZ = 23;
public const SCSU = 24;
public const ISCII = 25;
public const US_ASCII = 26;
public const UTF7 = 27;
public const BOCU1 = 28;
public const UTF16 = 29;
public const UTF32 = 30;
public const CESU8 = 31;
public const IMAP_MAILBOX = 32;









#[Pure]
public function __construct(
#[TypeAware(['8.0' => 'string|null'], default: '')] $destination_encoding = null,
#[TypeAware(['8.0' => 'string|null'], default: '')] $source_encoding = null
) {}









#[Pure]
#[TentativeType]
public function convert(
#[TypeAware(['8.0' => 'string'], default: '')] $str,
#[TypeAware(['8.0' => 'bool'], default: '')] $reverse = false
): string|false {}











#[TentativeType]
public function fromUCallback(
#[TypeAware(['8.0' => 'int'], default: '')] $reason,
#[TypeAware(['8.0' => 'array'], default: '')] $source,
#[TypeAware(['8.0' => 'int'], default: '')] $codePoint,
&$error
): array|string|int|null {}








#[TentativeType]
public static function getAliases(
#[ElementAvailable(from: '5.5', to: '5.6')] $name = '',
#[ElementAvailable(from: '7.0')] #[TypeAware(['8.0' => 'string'], default: '')] $name
): array|false|null {}







#[TentativeType]
public static function getAvailable(): array {}







#[Pure]
#[TentativeType]
public function getDestinationEncoding(): string|false|null {}







#[Pure]
#[TentativeType]
public function getDestinationType(): int|false|null {}







#[Pure]
#[TentativeType]
public function getErrorCode(): int {}







#[Pure]
#[TentativeType]
public function getErrorMessage(): ?string {}







#[Pure]
#[TentativeType]
public function getSourceEncoding(): string|false|null {}







#[Pure]
#[TentativeType]
public function getSourceType(): int|false|null {}







#[Pure]
#[TentativeType]
public static function getStandards(): ?array {}







#[Pure]
#[TentativeType]
public function getSubstChars(): string|false|null {}








#[Pure]
#[TentativeType]
public static function reasonText(
#[ElementAvailable(from: '5.3', to: '7.4')] $reason = 0,
#[ElementAvailable(from: '8.0')] int $reason
): string {}








#[TentativeType]
public function setDestinationEncoding(#[TypeAware(['8.0' => 'string'], default: '')] $encoding): bool {}








#[TentativeType]
public function setSourceEncoding(#[TypeAware(['8.0' => 'string'], default: '')] $encoding): bool {}








#[TentativeType]
public function setSubstChars(#[TypeAware(['8.0' => 'string'], default: '')] $chars): bool {}











#[TentativeType]
public function toUCallback(
#[TypeAware(['8.0' => 'int'], default: '')] $reason,
#[TypeAware(['8.0' => 'string'], default: '')] $source,
#[TypeAware(['8.0' => 'string'], default: '')] $codeUnits,
&$error
): array|string|int|null {}











#[TentativeType]
public static function transcode(
#[TypeAware(['8.0' => 'string'], default: '')] $str,
#[TypeAware(['8.0' => 'string'], default: '')] $toEncoding,
#[TypeAware(['8.0' => 'string'], default: '')] $fromEncoding,
?array $options = []
): string|false {}
}

