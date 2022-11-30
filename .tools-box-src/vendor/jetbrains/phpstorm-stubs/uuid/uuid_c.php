<?php



define('UUID_VARIANT_NCS', 0);
define('UUID_VARIANT_DCE', 1);
define('UUID_VARIANT_MICROSOFT', 2);
define('UUID_VARIANT_OTHER', 3);

define('UUID_TYPE_DEFAULT', 0);
define('UUID_TYPE_DCE', 4);
define('UUID_TYPE_NAME', 1);
define('UUID_TYPE_TIME', 1);
define('UUID_TYPE_SECURITY', 2);
define('UUID_TYPE_MD5', 3);
define('UUID_TYPE_RANDOM', 4);
define('UUID_TYPE_SHA1', 5);
define('UUID_TYPE_NULL', -1);
define('UUID_TYPE_INVALID', -42);







function uuid_create($uuid_type = UUID_TYPE_DEFAULT) {}







function uuid_is_valid($uuid) {}












function uuid_compare($uuid1, $uuid2) {}







function uuid_is_null($uuid) {}











function uuid_generate_md5($uuid_ns, $name) {}











function uuid_generate_sha1($uuid_ns, $name) {}







function uuid_type($uuid) {}







function uuid_variant($uuid) {}







function uuid_time($uuid) {}







function uuid_mac($uuid) {}







function uuid_parse($uuid) {}







function uuid_unparse($uuid) {}
