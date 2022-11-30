<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\ComException;
function com_create_guid() : string
{
    \error_clear_last();
    $result = \com_create_guid();
    if ($result === \false) {
        throw ComException::createFromPhpError();
    }
    return $result;
}
function com_event_sink(object $variant, object $sink_object, $sink_interface = null) : void
{
    \error_clear_last();
    if ($sink_interface !== null) {
        $result = \com_event_sink($variant, $sink_object, $sink_interface);
    } else {
        $result = \com_event_sink($variant, $sink_object);
    }
    if ($result === \false) {
        throw ComException::createFromPhpError();
    }
}
function com_load_typelib(string $typelib, bool $case_insensitive = \true) : void
{
    \error_clear_last();
    $result = \com_load_typelib($typelib, $case_insensitive);
    if ($result === \false) {
        throw ComException::createFromPhpError();
    }
}
function com_print_typeinfo(object $variant, string $dispatch_interface = null, bool $display_sink = \false) : void
{
    \error_clear_last();
    if ($display_sink !== \false) {
        $result = \com_print_typeinfo($variant, $dispatch_interface, $display_sink);
    } elseif ($dispatch_interface !== null) {
        $result = \com_print_typeinfo($variant, $dispatch_interface);
    } else {
        $result = \com_print_typeinfo($variant);
    }
    if ($result === \false) {
        throw ComException::createFromPhpError();
    }
}
function variant_date_to_timestamp(object $variant) : int
{
    \error_clear_last();
    $result = \variant_date_to_timestamp($variant);
    if ($result === null) {
        throw ComException::createFromPhpError();
    }
    return $result;
}
function variant_round($value, int $decimals)
{
    \error_clear_last();
    $result = \variant_round($value, $decimals);
    if ($result === null) {
        throw ComException::createFromPhpError();
    }
    return $result;
}
