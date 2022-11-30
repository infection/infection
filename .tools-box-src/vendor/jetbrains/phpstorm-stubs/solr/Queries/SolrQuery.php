<?php

namespace _HumbugBoxb47773b41c19;

class SolrQuery extends \SolrModifiableParams implements \Serializable
{
    public const ORDER_ASC = 0;
    public const ORDER_DESC = 1;
    public const FACET_SORT_INDEX = 0;
    public const FACET_SORT_COUNT = 1;
    public const TERMS_SORT_INDEX = 0;
    public const TERMS_SORT_COUNT = 1;
    public function addExpandFilterQuery($fq)
    {
    }
    public function addExpandSortField($field, $order)
    {
    }
    public function addFacetDateField($dateField)
    {
    }
    public function addFacetDateOther($value, $field_override)
    {
    }
    public function addFacetField($field)
    {
    }
    public function addFacetQuery($facetQuery)
    {
    }
    public function addField($field)
    {
    }
    public function addFilterQuery($fq)
    {
    }
    public function addGroupField($value)
    {
    }
    public function addGroupFunction($value)
    {
    }
    public function addGroupQuery($value)
    {
    }
    public function addGroupSortField($field, $order)
    {
    }
    public function addHighlightField($field)
    {
    }
    public function addMltField($field)
    {
    }
    public function addMltQueryField($field, $boost)
    {
    }
    public function addSortField($field, $order = \SolrQuery::ORDER_DESC)
    {
    }
    public function addStatsFacet($field)
    {
    }
    public function addStatsField($field)
    {
    }
    public function collapse(\SolrCollapseFunction $collapseFunction)
    {
    }
    public function __construct($q = '')
    {
    }
    public function __destruct()
    {
    }
    public function getExpand()
    {
    }
    public function getExpandFilterQueries()
    {
    }
    public function getExpandQuery()
    {
    }
    public function getExpandRows()
    {
    }
    public function getExpandSortFields()
    {
    }
    public function getFacet()
    {
    }
    public function getFacetDateEnd($field_override)
    {
    }
    public function getFacetDateFields()
    {
    }
    public function getFacetDateGap($field_override)
    {
    }
    public function getFacetDateHardEnd($field_override)
    {
    }
    public function getFacetDateOther($field_override)
    {
    }
    public function getFacetDateStart($field_override)
    {
    }
    public function getFacetFields()
    {
    }
    public function getFacetLimit($field_override)
    {
    }
    public function getFacetMethod($field_override)
    {
    }
    public function getFacetMinCount($field_override)
    {
    }
    public function getFacetMissing($field_override)
    {
    }
    public function getFacetOffset($field_override)
    {
    }
    public function getFacetPrefix($field_override)
    {
    }
    public function getFacetQueries()
    {
    }
    public function getFacetSort($field_override)
    {
    }
    public function getFields()
    {
    }
    public function getFilterQueries()
    {
    }
    public function getGroup()
    {
    }
    public function getGroupCachePercent()
    {
    }
    public function getGroupFacet()
    {
    }
    public function getGroupFields()
    {
    }
    public function getGroupFormat()
    {
    }
    public function getGroupFunctions()
    {
    }
    public function getGroupLimit()
    {
    }
    public function getGroupMain()
    {
    }
    public function getGroupNGroups()
    {
    }
    public function getGroupOffset()
    {
    }
    public function getGroupQueries()
    {
    }
    public function getGroupSortFields()
    {
    }
    public function getGroupTruncate()
    {
    }
    public function getHighlight()
    {
    }
    public function getHighlightAlternateField($field_override)
    {
    }
    public function getHighlightFields()
    {
    }
    public function getHighlightFormatter($field_override)
    {
    }
    public function getHighlightFragmenter($field_override)
    {
    }
    public function getHighlightFragsize($field_override)
    {
    }
    public function getHighlightHighlightMultiTerm()
    {
    }
    public function getHighlightMaxAlternateFieldLength($field_override)
    {
    }
    public function getHighlightMaxAnalyzedChars()
    {
    }
    public function getHighlightMergeContiguous($field_override)
    {
    }
    public function getHighlightRegexMaxAnalyzedChars()
    {
    }
    public function getHighlightRegexPattern()
    {
    }
    public function getHighlightRegexSlop()
    {
    }
    public function getHighlightRequireFieldMatch()
    {
    }
    public function getHighlightSimplePost($field_override)
    {
    }
    public function getHighlightSimplePre($field_override)
    {
    }
    public function getHighlightSnippets($field_override)
    {
    }
    public function getHighlightUsePhraseHighlighter()
    {
    }
    public function getMlt()
    {
    }
    public function getMltBoost()
    {
    }
    public function getMltCount()
    {
    }
    public function getMltFields()
    {
    }
    public function getMltMaxNumQueryTerms()
    {
    }
    public function getMltMaxNumTokens()
    {
    }
    public function getMltMaxWordLength()
    {
    }
    public function getMltMinDocFrequency()
    {
    }
    public function getMltMinTermFrequency()
    {
    }
    public function getMltMinWordLength()
    {
    }
    public function getMltQueryFields()
    {
    }
    public function getQuery()
    {
    }
    public function getRows()
    {
    }
    public function getSortFields()
    {
    }
    public function getStart()
    {
    }
    public function getStats()
    {
    }
    public function getStatsFacets()
    {
    }
    public function getStatsFields()
    {
    }
    public function getTerms()
    {
    }
    public function getTermsField()
    {
    }
    public function getTermsIncludeLowerBound()
    {
    }
    public function getTermsIncludeUpperBound()
    {
    }
    public function getTermsLimit()
    {
    }
    public function getTermsLowerBound()
    {
    }
    public function getTermsMaxCount()
    {
    }
    public function getTermsMinCount()
    {
    }
    public function getTermsPrefix()
    {
    }
    public function getTermsReturnRaw()
    {
    }
    public function getTermsSort()
    {
    }
    public function getTermsUpperBound()
    {
    }
    public function getTimeAllowed()
    {
    }
    public function removeExpandFilterQuery($fq)
    {
    }
    public function removeExpandSortField($field)
    {
    }
    public function removeFacetDateField($field)
    {
    }
    public function removeFacetDateOther($value, $field_override)
    {
    }
    public function removeFacetField($field)
    {
    }
    public function removeFacetQuery($value)
    {
    }
    public function removeField($field)
    {
    }
    public function removeFilterQuery($fq)
    {
    }
    public function removeHighlightField($field)
    {
    }
    public function removeMltField($field)
    {
    }
    public function removeMltQueryField($queryField)
    {
    }
    public function removeSortField($field)
    {
    }
    public function removeStatsFacet($value)
    {
    }
    public function removeStatsField($field)
    {
    }
    public function setEchoHandler($flag)
    {
    }
    public function setEchoParams($type)
    {
    }
    public function setExpand($value)
    {
    }
    public function setExpandQuery($q)
    {
    }
    public function setExpandRows($value)
    {
    }
    public function setExplainOther($query)
    {
    }
    public function setFacet($flag)
    {
    }
    public function setFacetDateEnd($value, $field_override)
    {
    }
    public function setFacetDateGap($value, $field_override)
    {
    }
    public function setFacetDateHardEnd($value, $field_override)
    {
    }
    public function setFacetDateStart($value, $field_override)
    {
    }
    public function setFacetEnumCacheMinDefaultFrequency($frequency, $field_override)
    {
    }
    public function setFacetLimit($limit, $field_override)
    {
    }
    public function setFacetMethod($method, $field_override)
    {
    }
    public function setFacetMinCount($mincount, $field_override)
    {
    }
    public function setFacetMissing($flag, $field_override)
    {
    }
    public function setFacetOffset($offset, $field_override)
    {
    }
    public function setFacetPrefix($prefix, $field_override)
    {
    }
    public function setFacetSort($facetSort, $field_override)
    {
    }
    public function setGroup($value)
    {
    }
    public function setGroupCachePercent($percent)
    {
    }
    public function setGroupFacet($value)
    {
    }
    public function setGroupFormat($value)
    {
    }
    public function setGroupLimit($value)
    {
    }
    public function setGroupMain($value)
    {
    }
    public function setGroupNGroups($value)
    {
    }
    public function setGroupOffset($value)
    {
    }
    public function setGroupTruncate($value)
    {
    }
    public function setHighlight($flag)
    {
    }
    public function setHighlightAlternateField($field, $field_override)
    {
    }
    public function setHighlightFormatter($formatter, $field_override)
    {
    }
    public function setHighlightFragmenter($fragmenter, $field_override)
    {
    }
    public function setHighlightFragsize($size, $field_override)
    {
    }
    public function setHighlightHighlightMultiTerm($flag)
    {
    }
    public function setHighlightMaxAlternateFieldLength($fieldLength, $field_override)
    {
    }
    public function setHighlightMaxAnalyzedChars($value)
    {
    }
    public function setHighlightMergeContiguous($flag, $field_override)
    {
    }
    public function setHighlightRegexMaxAnalyzedChars($maxAnalyzedChars)
    {
    }
    public function setHighlightRegexPattern($value)
    {
    }
    public function setHighlightRegexSlop($factor)
    {
    }
    public function setHighlightRequireFieldMatch($flag)
    {
    }
    public function setHighlightSimplePost($simplePost, $field_override)
    {
    }
    public function setHighlightSimplePre($simplePre, $field_override)
    {
    }
    public function setHighlightSnippets($value, $field_override)
    {
    }
    public function setHighlightUsePhraseHighlighter($flag)
    {
    }
    public function setMlt($flag)
    {
    }
    public function setMltBoost($flag)
    {
    }
    public function setMltCount($count)
    {
    }
    public function setMltMaxNumQueryTerms($value)
    {
    }
    public function setMltMaxNumTokens($value)
    {
    }
    public function setMltMaxWordLength($maxWordLength)
    {
    }
    public function setMltMinDocFrequency($minDocFrequency)
    {
    }
    public function setMltMinTermFrequency($minTermFrequency)
    {
    }
    public function setMltMinWordLength($minWordLength)
    {
    }
    public function setOmitHeader($flag)
    {
    }
    public function setQuery($query)
    {
    }
    public function setRows($rows)
    {
    }
    public function setShowDebugInfo($flag)
    {
    }
    public function setStart($start)
    {
    }
    public function setStats($flag)
    {
    }
    public function setTerms($flag)
    {
    }
    public function setTermsField($fieldname)
    {
    }
    public function setTermsIncludeLowerBound($flag)
    {
    }
    public function setTermsIncludeUpperBound($flag)
    {
    }
    public function setTermsLimit($limit)
    {
    }
    public function setTermsLowerBound($lowerBound)
    {
    }
    public function setTermsMaxCount($frequency)
    {
    }
    public function setTermsMinCount($frequency)
    {
    }
    public function setTermsPrefix($prefix)
    {
    }
    public function setTermsReturnRaw($flag)
    {
    }
    public function setTermsSort($sortType)
    {
    }
    public function setTermsUpperBound($upperBound)
    {
    }
    public function setTimeAllowed($timeAllowed)
    {
    }
}
\class_alias('_HumbugBoxb47773b41c19\\SolrQuery', 'SolrQuery', \false);
