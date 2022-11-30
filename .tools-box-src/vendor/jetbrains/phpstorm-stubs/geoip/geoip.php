<?php


use JetBrains\PhpStorm\Pure;












#[Pure]
function geoip_database_info($database = GEOIP_COUNTRY_EDITION) {}











#[Pure]
function geoip_country_code_by_name($hostname) {}











#[Pure]
function geoip_country_code3_by_name($hostname) {}











#[Pure]
function geoip_country_name_by_name($hostname) {}











#[Pure]
function geoip_continent_code_by_name($hostname) {}











#[Pure]
function geoip_org_by_name($hostname) {}











#[Pure]
function geoip_record_by_name($hostname) {}










#[Pure]
function geoip_id_by_name($hostname) {}











#[Pure]
function geoip_region_by_name($hostname) {}











#[Pure]
function geoip_isp_by_name($hostname) {}












#[Pure]
function geoip_db_avail($database) {}







#[Pure]
function geoip_db_get_all_info() {}












#[Pure]
function geoip_db_filename($database) {}
















#[Pure]
function geoip_region_name_by_code($country_code, $region_code) {}
















#[Pure]
function geoip_time_zone_by_country_and_region($country_code, $region_code = null) {}

define('GEOIP_COUNTRY_EDITION', 1);
define('GEOIP_REGION_EDITION_REV0', 7);
define('GEOIP_CITY_EDITION_REV0', 6);
define('GEOIP_ORG_EDITION', 5);
define('GEOIP_ISP_EDITION', 4);
define('GEOIP_CITY_EDITION_REV1', 2);
define('GEOIP_REGION_EDITION_REV1', 3);
define('GEOIP_PROXY_EDITION', 8);
define('GEOIP_ASNUM_EDITION', 9);
define('GEOIP_NETSPEED_EDITION', 10);
define('GEOIP_DOMAIN_EDITION', 11);
define('GEOIP_UNKNOWN_SPEED', 0);
define('GEOIP_DIALUP_SPEED', 1);
define('GEOIP_CABLEDSL_SPEED', 2);
define('GEOIP_CORPORATE_SPEED', 3);












function geoip_asnum_by_name($hostname) {}






















function geoip_netspeedcell_by_name($hostname) {}












function geoip_setup_custom_directory($path) {}


