<?php







define('YAML_ANY_SCALAR_STYLE', 0);
define('YAML_PLAIN_SCALAR_STYLE', 1);
define('YAML_SINGLE_QUOTED_SCALAR_STYLE', 2);
define('YAML_DOUBLE_QUOTED_SCALAR_STYLE', 3);
define('YAML_LITERAL_SCALAR_STYLE', 4);
define('YAML_FOLDED_SCALAR_STYLE', 5);





define('YAML_NULL_TAG', 'tag:yaml.org,2002:null');
define('YAML_BOOL_TAG', 'tag:yaml.org,2002:bool');
define('YAML_STR_TAG', 'tag:yaml.org,2002:str');
define('YAML_INT_TAG', 'tag:yaml.org,2002:int');
define('YAML_FLOAT_TAG', 'tag:yaml.org,2002:float');
define('YAML_TIMESTAMP_TAG', 'tag:yaml.org,2002:timestamp');
define('YAML_SEQ_TAG', 'tag:yaml.org,2002:seq');
define('YAML_MAP_TAG', 'tag:yaml.org,2002:map');
define('YAML_PHP_TAG', '!php/object');





define('YAML_ANY_ENCODING', 0);
define('YAML_UTF8_ENCODING', 1);
define('YAML_UTF16LE_ENCODING', 2);
define('YAML_UTF16BE_ENCODING', 3);





define('YAML_ANY_BREAK', 0);




define('YAML_CR_BREAK', 1);




define('YAML_LN_BREAK', 2);




define('YAML_CRLN_BREAK', 3);

define('YAML_MERGE_TAG', 'tag:yaml.org,2002:merge');
define('YAML_BINARY_TAG', 'tag:yaml.org,2002:binary');












function yaml_emit_file($filename, $data, $encoding = YAML_ANY_ENCODING, $linebreak = YAML_ANY_BREAK, array $callbacks = []) {}










function yaml_emit($data, $encoding = YAML_ANY_ENCODING, $linebreak = YAML_ANY_BREAK, array $callbacks = []) {}











function yaml_parse_file($filename, $pos = 0, &$ndocs = null, array $callbacks = []) {}











function yaml_parse_url($url, $pos = 0, &$ndocs = null, array $callbacks = []) {}











function yaml_parse($input, $pos = 0, &$ndocs = null, array $callbacks = []) {}
