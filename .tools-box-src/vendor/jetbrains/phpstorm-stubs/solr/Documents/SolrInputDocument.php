<?php

namespace _HumbugBoxb47773b41c19;

final class SolrInputDocument
{
    public const SORT_DEFAULT = 1;
    public const SORT_ASC = 1;
    public const SORT_DESC = 2;
    public const SORT_FIELD_NAME = 1;
    public const SORT_FIELD_VALUE_COUNT = 2;
    public const SORT_FIELD_BOOST_VALUE = 4;
    public function addChildDocument(\SolrInputDocument $child)
    {
    }
    public function addChildDocuments(array &$docs)
    {
    }
    public function addField($fieldName, $fieldValue, $fieldBoostValue = 0.0)
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
    public function deleteField($fieldName)
    {
    }
    public function __destruct()
    {
    }
    public function fieldExists($fieldName)
    {
    }
    public function getBoost()
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
    public function getFieldBoost($fieldName)
    {
    }
    public function getFieldCount()
    {
    }
    public function getFieldNames()
    {
    }
    public function hasChildDocuments()
    {
    }
    public function merge(\SolrInputDocument $sourceDoc, $overwrite = \true)
    {
    }
    public function reset()
    {
    }
    public function setBoost($documentBoostValue)
    {
    }
    public function setFieldBoost($fieldName, $fieldBoostValue)
    {
    }
    public function sort($sortOrderBy, $sortDirection = \SolrInputDocument::SORT_ASC)
    {
    }
    public function toArray()
    {
    }
}
\class_alias('_HumbugBoxb47773b41c19\\SolrInputDocument', 'SolrInputDocument', \false);
