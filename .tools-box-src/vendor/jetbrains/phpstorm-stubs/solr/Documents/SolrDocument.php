<?php

namespace _HumbugBoxb47773b41c19;

final class SolrDocument implements \ArrayAccess, \Iterator, \Serializable
{
    public const SORT_DEFAULT = 1;
    public const SORT_ASC = 1;
    public const SORT_DESC = 2;
    public const SORT_FIELD_NAME = 1;
    public const SORT_FIELD_VALUE_COUNT = 2;
    public const SORT_FIELD_BOOST_VALUE = 4;
    public function addField($fieldName, $fieldValue)
    {
    }
    public function clear()
    {
    }
    public function __clone()
    {
    }
    public function __construct()
    {
    }
    public function current()
    {
    }
    public function deleteField($fieldName)
    {
    }
    public function __destruct()
    {
    }
    public function fieldExists($fieldName)
    {
    }
    public function __get($fieldName)
    {
    }
    public function getChildDocuments()
    {
    }
    public function getChildDocumentsCount()
    {
    }
    public function getField($fieldName)
    {
    }
    public function getFieldCount()
    {
    }
    public function getFieldNames()
    {
    }
    public function getInputDocument()
    {
    }
    public function hasChildDocuments()
    {
    }
    public function __isset($fieldName)
    {
    }
    public function key()
    {
    }
    public function merge(\SolrInputDocument $sourceDoc, $overwrite = \true)
    {
    }
    public function next()
    {
    }
    public function offsetExists($fieldName)
    {
    }
    public function offsetGet($fieldName)
    {
    }
    public function offsetSet($fieldName, $fieldValue)
    {
    }
    public function offsetUnset($fieldName)
    {
    }
    public function reset()
    {
    }
    public function rewind()
    {
    }
    public function serialize()
    {
    }
    public function __set($fieldName, $fieldValue)
    {
    }
    public function sort($sortOrderBy, $sortDirection = \SolrInputDocument::SORT_ASC)
    {
    }
    public function toArray()
    {
    }
    public function unserialize($serialized)
    {
    }
    public function __unset($fieldName)
    {
    }
    public function valid()
    {
    }
}
\class_alias('_HumbugBoxb47773b41c19\\SolrDocument', 'SolrDocument', \false);
