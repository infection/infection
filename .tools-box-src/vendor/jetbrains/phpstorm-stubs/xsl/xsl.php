<?php






class XSLTProcessor
{









public function importStylesheet($stylesheet) {}









public function transformToDoc(DOMNode $doc) {}












public function transformToUri($doc, $uri) {}









public function transformToXml($doc) {}












public function setParameter($namespace, $options) {}















public function setParameter($namespace, $name, $value) {}












public function getParameter($namespaceURI, $localName) {}












public function removeParameter($namespaceURI, $localName) {}







public function hasExsltSupport() {}















public function registerPHPFunctions($restrict = null) {}









public function setProfiling($filename) {}








public function setSecurityPrefs($securityPrefs) {}







public function getSecurityPrefs() {}
}
define('XSL_CLONE_AUTO', 0);
define('XSL_CLONE_NEVER', -1);
define('XSL_CLONE_ALWAYS', 1);


define('XSL_SECPREF_NONE', 0);

define('XSL_SECPREF_READ_FILE', 2);

define('XSL_SECPREF_WRITE_FILE', 4);

define('XSL_SECPREF_CREATE_DIRECTORY', 8);

define('XSL_SECPREF_READ_NETWORK', 16);

define('XSL_SECPREF_WRITE_NETWORK', 32);

define('XSL_SECPREF_DEFAULT', 44);





define('LIBXSLT_VERSION', 10128);





define('LIBXSLT_DOTTED_VERSION', "1.1.28");





define('LIBEXSLT_VERSION', 817);





define('LIBEXSLT_DOTTED_VERSION', "1.1.28");


