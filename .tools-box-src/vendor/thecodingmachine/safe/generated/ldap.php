<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\LdapException;
function ldap_8859_to_t61(string $value) : string
{
    \error_clear_last();
    $safeResult = \ldap_8859_to_t61($value);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
    return $safeResult;
}
function ldap_add($ldap, string $dn, array $entry, array $controls = null) : void
{
    \error_clear_last();
    if ($controls !== null) {
        $safeResult = \ldap_add($ldap, $dn, $entry, $controls);
    } else {
        $safeResult = \ldap_add($ldap, $dn, $entry);
    }
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_bind($ldap, ?string $dn = null, ?string $password = null) : void
{
    \error_clear_last();
    if ($password !== null) {
        $safeResult = \ldap_bind($ldap, $dn, $password);
    } elseif ($dn !== null) {
        $safeResult = \ldap_bind($ldap, $dn);
    } else {
        $safeResult = \ldap_bind($ldap);
    }
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_control_paged_result_response($link, $result, ?string &$cookie = null, ?int &$estimated = null) : void
{
    \error_clear_last();
    $safeResult = \ldap_control_paged_result_response($link, $result, $cookie, $estimated);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_control_paged_result($link, int $pagesize, bool $iscritical = \false, string $cookie = "") : void
{
    \error_clear_last();
    $safeResult = \ldap_control_paged_result($link, $pagesize, $iscritical, $cookie);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_count_entries($ldap, $result) : int
{
    \error_clear_last();
    $safeResult = \ldap_count_entries($ldap, $result);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
    return $safeResult;
}
function ldap_delete($ldap, string $dn, array $controls = null) : void
{
    \error_clear_last();
    if ($controls !== null) {
        $safeResult = \ldap_delete($ldap, $dn, $controls);
    } else {
        $safeResult = \ldap_delete($ldap, $dn);
    }
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_dn2ufn(string $dn) : string
{
    \error_clear_last();
    $safeResult = \ldap_dn2ufn($dn);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
    return $safeResult;
}
function ldap_exop_passwd($ldap, string $user = "", string $old_password = "", string $new_password = "", array &$controls = null)
{
    \error_clear_last();
    $safeResult = \ldap_exop_passwd($ldap, $user, $old_password, $new_password, $controls);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
    return $safeResult;
}
function ldap_exop_whoami($ldap)
{
    \error_clear_last();
    $safeResult = \ldap_exop_whoami($ldap);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
    return $safeResult;
}
function ldap_exop($ldap, string $request_oid, string $request_data = null, ?array $controls = null, ?string &$response_data = null, ?string &$response_oid = null)
{
    \error_clear_last();
    if ($response_oid !== null) {
        $safeResult = \ldap_exop($ldap, $request_oid, $request_data, $controls, $response_data, $response_oid);
    } elseif ($response_data !== null) {
        $safeResult = \ldap_exop($ldap, $request_oid, $request_data, $controls, $response_data);
    } elseif ($controls !== null) {
        $safeResult = \ldap_exop($ldap, $request_oid, $request_data, $controls);
    } elseif ($request_data !== null) {
        $safeResult = \ldap_exop($ldap, $request_oid, $request_data);
    } else {
        $safeResult = \ldap_exop($ldap, $request_oid);
    }
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
    return $safeResult;
}
function ldap_explode_dn(string $dn, int $with_attrib) : array
{
    \error_clear_last();
    $safeResult = \ldap_explode_dn($dn, $with_attrib);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
    return $safeResult;
}
function ldap_first_attribute($ldap, $entry) : string
{
    \error_clear_last();
    $safeResult = \ldap_first_attribute($ldap, $entry);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
    return $safeResult;
}
function ldap_first_entry($ldap, $result)
{
    \error_clear_last();
    $safeResult = \ldap_first_entry($ldap, $result);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
    return $safeResult;
}
function ldap_free_result($result) : void
{
    \error_clear_last();
    $safeResult = \ldap_free_result($result);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_get_attributes($ldap, $entry) : array
{
    \error_clear_last();
    $safeResult = \ldap_get_attributes($ldap, $entry);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
    return $safeResult;
}
function ldap_get_dn($ldap, $entry) : string
{
    \error_clear_last();
    $safeResult = \ldap_get_dn($ldap, $entry);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
    return $safeResult;
}
function ldap_get_entries($ldap, $result) : array
{
    \error_clear_last();
    $safeResult = \ldap_get_entries($ldap, $result);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
    return $safeResult;
}
function ldap_get_option($ldap, int $option, &$value = null) : void
{
    \error_clear_last();
    $safeResult = \ldap_get_option($ldap, $option, $value);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_get_values_len($ldap, $entry, string $attribute) : array
{
    \error_clear_last();
    $safeResult = \ldap_get_values_len($ldap, $entry, $attribute);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
    return $safeResult;
}
function ldap_get_values($ldap, $entry, string $attribute) : array
{
    \error_clear_last();
    $safeResult = \ldap_get_values($ldap, $entry, $attribute);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
    return $safeResult;
}
function ldap_mod_add($ldap, string $dn, array $entry, array $controls = null) : void
{
    \error_clear_last();
    if ($controls !== null) {
        $safeResult = \ldap_mod_add($ldap, $dn, $entry, $controls);
    } else {
        $safeResult = \ldap_mod_add($ldap, $dn, $entry);
    }
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_mod_del($ldap, string $dn, array $entry, array $controls = null) : void
{
    \error_clear_last();
    if ($controls !== null) {
        $safeResult = \ldap_mod_del($ldap, $dn, $entry, $controls);
    } else {
        $safeResult = \ldap_mod_del($ldap, $dn, $entry);
    }
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_mod_replace($ldap, string $dn, array $entry, array $controls = null) : void
{
    \error_clear_last();
    if ($controls !== null) {
        $safeResult = \ldap_mod_replace($ldap, $dn, $entry, $controls);
    } else {
        $safeResult = \ldap_mod_replace($ldap, $dn, $entry);
    }
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_modify_batch($ldap, string $dn, array $modifications_info, array $controls = null) : void
{
    \error_clear_last();
    if ($controls !== null) {
        $safeResult = \ldap_modify_batch($ldap, $dn, $modifications_info, $controls);
    } else {
        $safeResult = \ldap_modify_batch($ldap, $dn, $modifications_info);
    }
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_next_attribute($ldap, $entry) : string
{
    \error_clear_last();
    $safeResult = \ldap_next_attribute($ldap, $entry);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
    return $safeResult;
}
function ldap_parse_exop($ldap, $result, ?string &$response_data = null, ?string &$response_oid = null) : void
{
    \error_clear_last();
    $safeResult = \ldap_parse_exop($ldap, $result, $response_data, $response_oid);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_parse_result($ldap, $result, ?int &$error_code, ?string &$matched_dn = null, ?string &$error_message = null, ?array &$referrals = null, ?array &$controls = null) : void
{
    \error_clear_last();
    $safeResult = \ldap_parse_result($ldap, $result, $error_code, $matched_dn, $error_message, $referrals, $controls);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_rename($ldap, string $dn, string $new_rdn, string $new_parent, bool $delete_old_rdn, array $controls = null) : void
{
    \error_clear_last();
    if ($controls !== null) {
        $safeResult = \ldap_rename($ldap, $dn, $new_rdn, $new_parent, $delete_old_rdn, $controls);
    } else {
        $safeResult = \ldap_rename($ldap, $dn, $new_rdn, $new_parent, $delete_old_rdn);
    }
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_sasl_bind($ldap, string $dn = null, string $password = null, string $mech = null, string $realm = null, string $authc_id = null, string $authz_id = null, string $props = null) : void
{
    \error_clear_last();
    if ($props !== null) {
        $safeResult = \ldap_sasl_bind($ldap, $dn, $password, $mech, $realm, $authc_id, $authz_id, $props);
    } elseif ($authz_id !== null) {
        $safeResult = \ldap_sasl_bind($ldap, $dn, $password, $mech, $realm, $authc_id, $authz_id);
    } elseif ($authc_id !== null) {
        $safeResult = \ldap_sasl_bind($ldap, $dn, $password, $mech, $realm, $authc_id);
    } elseif ($realm !== null) {
        $safeResult = \ldap_sasl_bind($ldap, $dn, $password, $mech, $realm);
    } elseif ($mech !== null) {
        $safeResult = \ldap_sasl_bind($ldap, $dn, $password, $mech);
    } elseif ($password !== null) {
        $safeResult = \ldap_sasl_bind($ldap, $dn, $password);
    } elseif ($dn !== null) {
        $safeResult = \ldap_sasl_bind($ldap, $dn);
    } else {
        $safeResult = \ldap_sasl_bind($ldap);
    }
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_set_option($ldap, int $option, $value) : void
{
    \error_clear_last();
    $safeResult = \ldap_set_option($ldap, $option, $value);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_unbind($ldap) : void
{
    \error_clear_last();
    $safeResult = \ldap_unbind($ldap);
    if ($safeResult === \false) {
        throw LdapException::createFromPhpError();
    }
}
