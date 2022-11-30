<?php

namespace Saxon;




class SaxonProcessor
{






public function __construct($license = false, $cwd = '') {}







public function createAtomicValue($primitive_type_val) {}







public function parseXmlFromString($value) {}







public function parseXmlFromFile($fileName) {}







public function setcwd($cwd) {}







public function setResourceDirectory($dir) {}









public function setConfigurationProperty($name, $value) {}






public function newXsltProcessor() {}






public function newXslt30Processor() {}






public function newXQueryProcessor() {}






public function newXPathProcessor() {}






public function newSchemaValidator() {}






public function version() {}







public function registerPHPFunctions($library) {}
}




class XsltProcessor
{








public function transformFileToFile($sourceFileName, $stylesheetFileName, $outputfileName) {}








public function transformFileToString($sourceFileName, $stylesheetFileName) {}







public function transformFileToValue($fileName) {}






public function transformToFile() {}




public function transformToString() {}






public function transformToValue() {}







public function compileFromFile($fileName) {}







public function compileFromString($str) {}







public function compileFromValue($node) {}







public function setOutputFile($fileName) {}







public function setSourceFromXdmValue($value) {}







public function setSourceFromFile($filename) {}








public function setParameter($name, $value) {}








public function setProperty($name, $value) {}






public function clearParameters() {}






public function clearProperties() {}






public function exceptionClear() {}







public function getErrorCode($i) {}







public function getErrorMessage($i) {}






public function getExceptionCount() {}
}




class Xslt30Processor
{






public function addPackages($packageFileNames) {}








public function applyTemplatesReturningFile($stylesheetFileName, $fileName) {}







public function applyTemplatesReturningString($stylesheetFileName) {}







public function applyTemplatesReturningValue($stylesheetFileName) {}







public function compileFromAssociatedFile($xmlFileName) {}







public function compileFromFile($fileName) {}







public function compileFromString($str) {}







public function compileFromValue($node) {}








public function compileFromFileAndSave($fileName, $outputFileName) {}








public function compileFromStringAndSave($str, $outputFileName) {}








public function compileFromValueAndSave($node, $outputFileName) {}









public function callFunctionReturningFile($functionName, $arguments, $outputFileName) {}








public function callFunctionReturningString($functionName, $arguments) {}








public function callFunctionReturningValue($functionName, $arguments) {}









public function callTemplateReturningFile($stylesheetFileName, $templateName, $outputFileName) {}








public function callTemplateReturningString($stylesheetFileName, $templateName) {}








public function callTemplateReturningValue($stylesheetFileName, $templateName) {}









public function transformFileToFile($sourceFileName, $stylesheetFileName, $outputFileName) {}







public function transformFileToValue($fileName) {}







public function transformFileToString($fileName) {}







public function transformToFile($context = null) {}







public function transformToString($context = null) {}







public function transformToValue($context = null) {}








public function setInitialTemplateParameters($parameters, $tunnel) {}







public function setInitialMatchSelection($value) {}







public function setInitialMatchSelectionAsFile($fileName) {}







public function setGlobalContextItem($item) {}







public function setGlobalContextFromFile($fileName) {}







public function setOutputFile($fileName) {}








public function setParameter($name, $value) {}








public function setProperty($name, $value) {}







public function setJustInTimeCompilation($value) {}







public function setResultAsRawValue($value) {}






public function clearParameters() {}






public function clearProperties() {}






public function exceptionClear() {}







public function getErrorCode($i) {}







public function getErrorMessage($i) {}






public function getExceptionCount() {}
}




class XQueryProcessor
{





public function runQueryToValue() {}






public function runQueryToString() {}







public function runQueryToFile($outfilename) {}







public function setQueryContent($str) {}





public function setQueryItem($item) {}







public function setQueryFile($filename) {}







public function setContextItemFromFile($fileName) {}








public function setContextItem($obj) {}







public function setQueryBaseURI($uri) {}








public function declareNamespace($prefix, $namespace) {}








public function setParameter($name, $value) {}








public function setProperty($name, $value) {}






public function clearParameters() {}






public function clearProperties() {}






public function exceptionClear() {}







public function getErrorCode($i) {}







public function getErrorMessage($i) {}






public function getExceptionCount() {}
}




class XPathProcessor
{






public function setContextItem($item) {}







public function setContextFile($fileName) {}







public function effectiveBooleanValue($xpathStr) {}







public function evaluate($xpathStr) {}







public function evaluateSingle($xpathStr) {}








public function declareNamespace($prefix, $namespace) {}







public function setBaseURI($uri) {}








public function setParameter($name, $value) {}








public function setProperty($name, $value) {}






public function clearParameters() {}






public function clearProperties() {}






public function exceptionClear() {}







public function getErrorCode($i) {}







public function getErrorMessage($i) {}






public function getExceptionCount() {}
}




class SchemaValidator
{






public function setSourceNode($node) {}







public function setOutputFile($fileName) {}







public function registerSchemaFromFile($fileName) {}







public function registerSchemaFromString($schemaStr) {}







public function validate($filename = null) {}







public function validateToNode($filename = null) {}






public function getValidationReport() {}








public function setParameter($name, $value) {}








public function setProperty($name, $value) {}






public function clearParameters() {}






public function clearProperties() {}






public function exceptionClear() {}







public function getErrorCode($i) {}







public function getErrorMessage($i) {}






public function getExceptionCount() {}
}




class XdmValue
{





public function getHead() {}







public function itemAt($index) {}






public function size() {}






public function addXdmItem($item) {}
}




class XdmItem extends XdmValue
{





public function getStringValue() {}






public function isNode() {}






public function isAtomic() {}






public function getAtomicValue() {}






public function getNodeValue() {}
}




class XdmNode extends XdmItem
{





public function getStringValue() {}






public function getNodeKind() {}






public function getNodeName() {}






public function isAtomic() {}






public function getChildCount() {}






public function getAttributeCount() {}







public function getChildNode($index) {}






public function getParent() {}







public function getAttributeNode($index) {}







public function getAttributeValue($index) {}
}




class XdmAtomicValue extends XdmItem
{





public function getStringValue() {}






public function getBooleanValue() {}






public function getDoubleValue() {}






public function getLongValue() {}






public function isAtomic() {}
}
