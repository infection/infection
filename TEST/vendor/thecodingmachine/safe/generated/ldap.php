<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\LdapException;
function ldap_8859_to_t61(string $value) : string
{
    \error_clear_last();
    $result = \ldap_8859_to_t61($value);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
    return $result;
}
function ldap_add($ldap, string $dn, array $entry, array $controls = null) : void
{
    \error_clear_last();
    if ($controls !== null) {
        $result = \ldap_add($ldap, $dn, $entry, $controls);
    } else {
        $result = \ldap_add($ldap, $dn, $entry);
    }
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_bind($ldap, ?string $dn = null, ?string $password = null) : void
{
    \error_clear_last();
    if ($password !== null) {
        $result = \ldap_bind($ldap, $dn, $password);
    } elseif ($dn !== null) {
        $result = \ldap_bind($ldap, $dn);
    } else {
        $result = \ldap_bind($ldap);
    }
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_control_paged_result_response($link, $result, ?string &$cookie = null, ?int &$estimated = null) : void
{
    \error_clear_last();
    $result = \ldap_control_paged_result_response($link, $result, $cookie, $estimated);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_control_paged_result($link, int $pagesize, bool $iscritical = \false, string $cookie = "") : void
{
    \error_clear_last();
    $result = \ldap_control_paged_result($link, $pagesize, $iscritical, $cookie);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_count_entries($ldap, $result) : int
{
    \error_clear_last();
    $result = \ldap_count_entries($ldap, $result);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
    return $result;
}
function ldap_delete($ldap, string $dn, array $controls = null) : void
{
    \error_clear_last();
    if ($controls !== null) {
        $result = \ldap_delete($ldap, $dn, $controls);
    } else {
        $result = \ldap_delete($ldap, $dn);
    }
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_dn2ufn(string $dn) : string
{
    \error_clear_last();
    $result = \ldap_dn2ufn($dn);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
    return $result;
}
function ldap_exop_passwd($ldap, string $user = "", string $old_password = "", string $new_password = "", array &$controls = null)
{
    \error_clear_last();
    $result = \ldap_exop_passwd($ldap, $user, $old_password, $new_password, $controls);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
    return $result;
}
function ldap_exop_whoami($ldap)
{
    \error_clear_last();
    $result = \ldap_exop_whoami($ldap);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
    return $result;
}
function ldap_exop($ldap, string $reqoid, string $reqdata = null, ?array $serverctrls = null, ?string &$retdata = null, ?string &$retoid = null)
{
    \error_clear_last();
    if ($retoid !== null) {
        $result = \ldap_exop($ldap, $reqoid, $reqdata, $serverctrls, $retdata, $retoid);
    } elseif ($retdata !== null) {
        $result = \ldap_exop($ldap, $reqoid, $reqdata, $serverctrls, $retdata);
    } elseif ($serverctrls !== null) {
        $result = \ldap_exop($ldap, $reqoid, $reqdata, $serverctrls);
    } elseif ($reqdata !== null) {
        $result = \ldap_exop($ldap, $reqoid, $reqdata);
    } else {
        $result = \ldap_exop($ldap, $reqoid);
    }
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
    return $result;
}
function ldap_explode_dn(string $dn, int $with_attrib) : array
{
    \error_clear_last();
    $result = \ldap_explode_dn($dn, $with_attrib);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
    return $result;
}
function ldap_first_attribute($ldap, $entry) : string
{
    \error_clear_last();
    $result = \ldap_first_attribute($ldap, $entry);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
    return $result;
}
function ldap_first_entry($ldap, $result)
{
    \error_clear_last();
    $result = \ldap_first_entry($ldap, $result);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
    return $result;
}
function ldap_free_result($result) : void
{
    \error_clear_last();
    $result = \ldap_free_result($result);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_get_attributes($ldap, $entry) : array
{
    \error_clear_last();
    $result = \ldap_get_attributes($ldap, $entry);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
    return $result;
}
function ldap_get_dn($ldap, $entry) : string
{
    \error_clear_last();
    $result = \ldap_get_dn($ldap, $entry);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
    return $result;
}
function ldap_get_entries($ldap, $result) : array
{
    \error_clear_last();
    $result = \ldap_get_entries($ldap, $result);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
    return $result;
}
function ldap_get_option($ldap, int $option, &$value = null) : void
{
    \error_clear_last();
    $result = \ldap_get_option($ldap, $option, $value);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_get_values_len($ldap, $entry, string $attribute) : array
{
    \error_clear_last();
    $result = \ldap_get_values_len($ldap, $entry, $attribute);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
    return $result;
}
function ldap_get_values($ldap, $entry, string $attribute) : array
{
    \error_clear_last();
    $result = \ldap_get_values($ldap, $entry, $attribute);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
    return $result;
}
function ldap_mod_add($ldap, string $dn, array $entry, array $controls = null) : void
{
    \error_clear_last();
    if ($controls !== null) {
        $result = \ldap_mod_add($ldap, $dn, $entry, $controls);
    } else {
        $result = \ldap_mod_add($ldap, $dn, $entry);
    }
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_mod_del($ldap, string $dn, array $entry, array $controls = null) : void
{
    \error_clear_last();
    if ($controls !== null) {
        $result = \ldap_mod_del($ldap, $dn, $entry, $controls);
    } else {
        $result = \ldap_mod_del($ldap, $dn, $entry);
    }
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_mod_replace($ldap, string $dn, array $entry, array $controls = null) : void
{
    \error_clear_last();
    if ($controls !== null) {
        $result = \ldap_mod_replace($ldap, $dn, $entry, $controls);
    } else {
        $result = \ldap_mod_replace($ldap, $dn, $entry);
    }
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_modify_batch($ldap, string $dn, array $modifications_info, array $controls = null) : void
{
    \error_clear_last();
    if ($controls !== null) {
        $result = \ldap_modify_batch($ldap, $dn, $modifications_info, $controls);
    } else {
        $result = \ldap_modify_batch($ldap, $dn, $modifications_info);
    }
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_next_attribute($ldap, $entry) : string
{
    \error_clear_last();
    $result = \ldap_next_attribute($ldap, $entry);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
    return $result;
}
function ldap_parse_exop($ldap, $result, ?string &$response_data = null, ?string &$response_oid = null) : void
{
    \error_clear_last();
    $result = \ldap_parse_exop($ldap, $result, $response_data, $response_oid);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_parse_result($ldap, $result, ?int &$error_code, ?string &$matched_dn = null, ?string &$error_message = null, ?array &$referrals = null, ?array &$controls = null) : void
{
    \error_clear_last();
    $result = \ldap_parse_result($ldap, $result, $error_code, $matched_dn, $error_message, $referrals, $controls);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_rename($ldap, string $dn, string $new_rdn, string $new_parent, bool $delete_old_rdn, array $controls = null) : void
{
    \error_clear_last();
    if ($controls !== null) {
        $result = \ldap_rename($ldap, $dn, $new_rdn, $new_parent, $delete_old_rdn, $controls);
    } else {
        $result = \ldap_rename($ldap, $dn, $new_rdn, $new_parent, $delete_old_rdn);
    }
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_sasl_bind($ldap, string $dn = null, string $password = null, string $mech = null, string $realm = null, string $authc_id = null, string $authz_id = null, string $props = null) : void
{
    \error_clear_last();
    if ($props !== null) {
        $result = \ldap_sasl_bind($ldap, $dn, $password, $mech, $realm, $authc_id, $authz_id, $props);
    } elseif ($authz_id !== null) {
        $result = \ldap_sasl_bind($ldap, $dn, $password, $mech, $realm, $authc_id, $authz_id);
    } elseif ($authc_id !== null) {
        $result = \ldap_sasl_bind($ldap, $dn, $password, $mech, $realm, $authc_id);
    } elseif ($realm !== null) {
        $result = \ldap_sasl_bind($ldap, $dn, $password, $mech, $realm);
    } elseif ($mech !== null) {
        $result = \ldap_sasl_bind($ldap, $dn, $password, $mech);
    } elseif ($password !== null) {
        $result = \ldap_sasl_bind($ldap, $dn, $password);
    } elseif ($dn !== null) {
        $result = \ldap_sasl_bind($ldap, $dn);
    } else {
        $result = \ldap_sasl_bind($ldap);
    }
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_set_option($ldap, int $option, $value) : void
{
    \error_clear_last();
    $result = \ldap_set_option($ldap, $option, $value);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
function ldap_unbind($ldap) : void
{
    \error_clear_last();
    $result = \ldap_unbind($ldap);
    if ($result === \false) {
        throw LdapException::createFromPhpError();
    }
}
