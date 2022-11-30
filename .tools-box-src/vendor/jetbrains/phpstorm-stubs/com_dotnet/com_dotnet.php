<?php







class COM
{








public function __construct($module_name, $server_name = null, $codepage = CP_ACP, $typelib = null) {}

public function __get($name) {}

public function __set($name, $value) {}

public function __call($name, $args) {}
}





class DOTNET
{







public function __construct($assembly_name, string $class_name, $codepage = CP_ACP) {}

public function __get($name) {}

public function __set($name, $value) {}

public function __call($name, $args) {}
}





class VARIANT
{







public function __construct($value = null, int $type = VT_EMPTY, $codepage = CP_ACP) {}

public function __get($name) {}

public function __set($name, $value) {}

public function __call($name, $args) {}
}





class com_exception extends \Exception {}







function com_create_guid() {}










function com_event_sink($comobject, $sinkobject, $sinkinterface = null) {}









function com_get_active_object($progid, $code_page = CP_ACP) {}









function com_load_typelib($typelib_name, $case_insensitive = true) {}








function com_message_pump($timeoutms = 0) {}










function com_print_typeinfo($comobject, $dispinterface = null, $wantsink = false) {}








function variant_abs($val) {}









function variant_add($left, $right) {}









function variant_and($left, $right) {}









function variant_cast($variant, $type) {}









function variant_cat($left, $right) {}











function variant_cmp($left, $right, $lcid = null, $flags = null) {}








function variant_date_from_timestamp($timestamp) {}








function variant_date_to_timestamp($variant) {}









function variant_div($left, $right) {}









function variant_eqv($left, $right) {}








function variant_fix($variant) {}








function variant_get_type($variant) {}









function variant_idiv($left, $right) {}









function variant_imp($left, $right) {}








function variant_int($variant) {}









function variant_mod($left, $right) {}









function variant_mul($left, $right) {}








function variant_neg($variant) {}








function variant_not($variant) {}









function variant_or($left, $right) {}









function variant_pow($left, $right) {}









function variant_round($variant, $decimals) {}









function variant_set_type($variant, $type) {}









function variant_set($variant, $value) {}









function variant_sub($left, $right) {}









function variant_xor($left, $right) {}

define('CLSCTX_INPROC_SERVER', 1);
define('CLSCTX_INPROC_HANDLER', 2);
define('CLSCTX_LOCAL_SERVER', 4);
define('CLSCTX_REMOTE_SERVER', 16);
define('CLSCTX_SERVER', 21);
define('CLSCTX_ALL', 23);

define('VT_NULL', 1);
define('VT_EMPTY', 0);
define('VT_UI1', 17);
define('VT_I2', 2);
define('VT_I4', 3);
define('VT_R4', 4);
define('VT_R8', 5);
define('VT_BOOL', 11);
define('VT_ERROR', 10);
define('VT_CY', 6);
define('VT_DATE', 7);
define('VT_BSTR', 8);
define('VT_DECIMAL', 14);
define('VT_UNKNOWN', 13);
define('VT_DISPATCH', 9);
define('VT_VARIANT', 12);
define('VT_I1', 16);
define('VT_UI2', 18);
define('VT_UI4', 19);
define('VT_INT', 22);
define('VT_UINT', 23);
define('VT_ARRAY', 8192);
define('VT_BYREF', 16384);

define('CP_ACP', 0);
define('CP_MACCP', 2);
define('CP_OEMCP', 1);
define('CP_UTF7', 65000);
define('CP_UTF8', 65001);
define('CP_SYMBOL', 42);
define('CP_THREAD_ACP', 3);

define('VARCMP_LT', 0);
define('VARCMP_EQ', 1);
define('VARCMP_GT', 2);
define('VARCMP_NULL', 3);

define('NORM_IGNORECASE', 1);
define('NORM_IGNORENONSPACE', 2);
define('NORM_IGNORESYMBOLS', 4);
define('NORM_IGNOREWIDTH', 131072);
define('NORM_IGNOREKANATYPE', 65536);
define('NORM_IGNOREKASHIDA', 262144);

define('DISP_E_DIVBYZERO', -2147352558);
define('DISP_E_OVERFLOW', -2147352566);
define('MK_E_UNAVAILABLE', -2147221021);


