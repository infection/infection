<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\XmlException;
function xml_parser_free($parser) : void
{
    \error_clear_last();
    $result = \xml_parser_free($parser);
    if ($result === \false) {
        throw XmlException::createFromPhpError();
    }
}
function xml_set_character_data_handler($parser, callable $handler) : void
{
    \error_clear_last();
    $result = \xml_set_character_data_handler($parser, $handler);
    if ($result === \false) {
        throw XmlException::createFromPhpError();
    }
}
function xml_set_default_handler($parser, callable $handler) : void
{
    \error_clear_last();
    $result = \xml_set_default_handler($parser, $handler);
    if ($result === \false) {
        throw XmlException::createFromPhpError();
    }
}
function xml_set_element_handler($parser, callable $start_handler, callable $end_handler) : void
{
    \error_clear_last();
    $result = \xml_set_element_handler($parser, $start_handler, $end_handler);
    if ($result === \false) {
        throw XmlException::createFromPhpError();
    }
}
function xml_set_end_namespace_decl_handler($parser, callable $handler) : void
{
    \error_clear_last();
    $result = \xml_set_end_namespace_decl_handler($parser, $handler);
    if ($result === \false) {
        throw XmlException::createFromPhpError();
    }
}
function xml_set_external_entity_ref_handler($parser, callable $handler) : void
{
    \error_clear_last();
    $result = \xml_set_external_entity_ref_handler($parser, $handler);
    if ($result === \false) {
        throw XmlException::createFromPhpError();
    }
}
function xml_set_notation_decl_handler($parser, callable $handler) : void
{
    \error_clear_last();
    $result = \xml_set_notation_decl_handler($parser, $handler);
    if ($result === \false) {
        throw XmlException::createFromPhpError();
    }
}
function xml_set_object($parser, object $object) : void
{
    \error_clear_last();
    $result = \xml_set_object($parser, $object);
    if ($result === \false) {
        throw XmlException::createFromPhpError();
    }
}
function xml_set_processing_instruction_handler($parser, callable $handler) : void
{
    \error_clear_last();
    $result = \xml_set_processing_instruction_handler($parser, $handler);
    if ($result === \false) {
        throw XmlException::createFromPhpError();
    }
}
function xml_set_start_namespace_decl_handler($parser, callable $handler) : void
{
    \error_clear_last();
    $result = \xml_set_start_namespace_decl_handler($parser, $handler);
    if ($result === \false) {
        throw XmlException::createFromPhpError();
    }
}
function xml_set_unparsed_entity_decl_handler($parser, callable $handler) : void
{
    \error_clear_last();
    $result = \xml_set_unparsed_entity_decl_handler($parser, $handler);
    if ($result === \false) {
        throw XmlException::createFromPhpError();
    }
}
