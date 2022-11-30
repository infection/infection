<?php


use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;









function jdtogregorian(int $julian_day): string {}















function gregoriantojd(int $month, int $day, int $year): int {}









function jdtojulian(int $julian_day): string {}















function juliantojd(int $month, int $day, int $year): int {}


















function jdtojewish(int $julian_day, bool $hebrew = false, int $flags = 0): string {}















function jewishtojd(int $month, int $day, int $year): int {}







function jdtofrench(int $julian_day): string {}















function frenchtojd(int $month, int $day, int $year): int {}




































function jddayofweek(int $julian_day, int $mode = CAL_DOW_DAYNO): string|int {}








function jdmonthname(int $julian_day, int $mode): string {}










function easter_date(?int $year, #[PhpStormStubsElementAvailable(from: '8.0')] int $mode = CAL_EASTER_DEFAULT): int {}
















function easter_days(?int $year, int $mode = CAL_EASTER_DEFAULT): int {}









function unixtojd(?int $timestamp = null): int|false {}









function jdtounix(int $julian_day): int {}

























function cal_to_jd(int $calendar, int $month, int $day, int $year): int {}














function cal_from_jd(int $julian_day, int $calendar): array {}















function cal_days_in_month(int $calendar, int $month, int $year): int {}










function cal_info(int $calendar = -1): array {}

define('CAL_GREGORIAN', 0);
define('CAL_JULIAN', 1);
define('CAL_JEWISH', 2);
define('CAL_FRENCH', 3);
define('CAL_NUM_CALS', 4);
define('CAL_DOW_DAYNO', 0);
define('CAL_DOW_SHORT', 2);
define('CAL_DOW_LONG', 1);
define('CAL_MONTH_GREGORIAN_SHORT', 0);
define('CAL_MONTH_GREGORIAN_LONG', 1);
define('CAL_MONTH_JULIAN_SHORT', 2);
define('CAL_MONTH_JULIAN_LONG', 3);
define('CAL_MONTH_JEWISH', 4);
define('CAL_MONTH_FRENCH', 5);
define('CAL_EASTER_DEFAULT', 0);
define('CAL_EASTER_ROMAN', 1);
define('CAL_EASTER_ALWAYS_GREGORIAN', 2);
define('CAL_EASTER_ALWAYS_JULIAN', 3);
define('CAL_JEWISH_ADD_ALAFIM_GERESH', 2);
define('CAL_JEWISH_ADD_ALAFIM', 4);
define('CAL_JEWISH_ADD_GERESHAYIM', 8);


