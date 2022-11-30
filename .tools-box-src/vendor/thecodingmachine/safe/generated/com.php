<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\ComException;
function com_create_guid() : string
{
    \error_clear_last();
    $safeResult = \com_create_guid();
    if ($safeResult === \false) {
        throw ComException::createFromPhpError();
    }
    return $safeResult;
}
function com_event_sink(object $variant, object $sink_object, $sink_interface = null) : void
{
    \error_clear_last();
    if ($sink_interface !== null) {
        $safeResult = \com_event_sink($variant, $sink_object, $sink_interface);
    } else {
        $safeResult = \com_event_sink($variant, $sink_object);
    }
    if ($safeResult === \false) {
        throw ComException::createFromPhpError();
    }
}
function com_load_typelib(string $typelib, bool $case_insensitive = \true) : void
{
    \error_clear_last();
    $safeResult = \com_load_typelib($typelib, $case_insensitive);
    if ($safeResult === \false) {
        throw ComException::createFromPhpError();
    }
}
function com_print_typeinfo(object $variant, string $dispatch_interface = null, bool $display_sink = \false) : void
{
    \error_clear_last();
    if ($display_sink !== \false) {
        $safeResult = \com_print_typeinfo($variant, $dispatch_interface, $display_sink);
    } elseif ($dispatch_interface !== null) {
        $safeResult = \com_print_typeinfo($variant, $dispatch_interface);
    } else {
        $safeResult = \com_print_typeinfo($variant);
    }
    if ($safeResult === \false) {
        throw ComException::createFromPhpError();
    }
}
function variant_date_to_timestamp(object $variant) : int
{
    \error_clear_last();
    $safeResult = \variant_date_to_timestamp($variant);
    if ($safeResult === null) {
        throw ComException::createFromPhpError();
    }
    return $safeResult;
}
function variant_round($value, int $decimals)
{
    \error_clear_last();
    $safeResult = \variant_round($value, $decimals);
    if ($safeResult === null) {
        throw ComException::createFromPhpError();
    }
    return $safeResult;
}
