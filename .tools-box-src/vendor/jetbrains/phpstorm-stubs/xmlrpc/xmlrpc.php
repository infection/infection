<?php









function xmlrpc_encode($value) {}













function xmlrpc_decode($xml, $encoding = "iso-8859-1") {}









function xmlrpc_decode_request($xml, &$method, $encoding = null) {}
















function xmlrpc_encode_request($method, $params, ?array $output_options = null) {}









function xmlrpc_get_type($value) {}













function xmlrpc_set_type(&$value, $type) {}











function xmlrpc_is_fault(array $arg) {}






function xmlrpc_server_create() {}







function xmlrpc_server_destroy($server) {}









function xmlrpc_server_register_method($server, $method_name, $function) {}










function xmlrpc_server_call_method($server, $xml, $user_data, ?array $output_options = null) {}







function xmlrpc_parse_method_descriptions($xml) {}








function xmlrpc_server_add_introspection_data($server, array $desc) {}








function xmlrpc_server_register_introspection_callback($server, $function) {}


