<?php


use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Language;





class DOMNode
{





#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $nodeName;






#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $nodeValue;







#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $nodeType;






#[LanguageLevelTypeAware(['8.1' => 'DOMNode|null'], default: '')]
public $parentNode;






#[LanguageLevelTypeAware(['8.1' => 'DOMNodeList'], default: '')]
public $childNodes;






#[LanguageLevelTypeAware(['8.1' => 'DOMNode|null'], default: '')]
public $firstChild;






#[LanguageLevelTypeAware(['8.1' => 'DOMNode|null'], default: '')]
public $lastChild;






#[LanguageLevelTypeAware(['8.1' => 'DOMNode|null'], default: '')]
public $previousSibling;






#[LanguageLevelTypeAware(['8.1' => 'DOMNode|null'], default: '')]
public $nextSibling;






#[LanguageLevelTypeAware(['8.1' => 'DOMNamedNodeMap|null'], default: '')]
public $attributes;






#[LanguageLevelTypeAware(['8.1' => 'DOMDocument|null'], default: '')]
public $ownerDocument;






#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $namespaceURI;






#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $prefix;






#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $localName;






#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $baseURI;






#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $textContent;













public function insertBefore(
DOMNode $node,
#[LanguageLevelTypeAware(['8.0' => 'DOMNode|null'], default: 'DOMNode')] $child = null
) {}














public function replaceChild(DOMNode $node, DOMNode $child) {}









public function removeChild(DOMNode $child) {}









public function appendChild(DOMNode $node) {}






#[TentativeType]
public function hasChildNodes(): bool {}










public function cloneNode(
#[PhpStormStubsElementAvailable(from: '5.3', to: '5.6')] $deep,
#[PhpStormStubsElementAvailable(from: '7.0')] #[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $deep = false
) {}






#[TentativeType]
public function normalize(): void {}














#[TentativeType]
public function isSupported(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $feature,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $version
): bool {}






#[TentativeType]
public function hasAttributes(): bool {}




public function compareDocumentPosition(DOMNode $other) {}









#[TentativeType]
public function isSameNode(DOMNode $otherNode): bool {}









#[TentativeType]
public function lookupPrefix(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $namespace): ?string {}










#[TentativeType]
public function isDefaultNamespace(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $namespace): bool {}









#[PhpStormStubsElementAvailable(from: '8.0')]
#[TentativeType]
public function lookupNamespaceURI(?string $prefix): ?string {}









#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')]
public function lookupNamespaceUri($prefix) {}





public function isEqualNode(DOMNode $arg) {}






public function getFeature($feature, $version) {}






public function setUserData($key, $data, $handler) {}





public function getUserData($key) {}






#[TentativeType]
public function getNodePath(): ?string {}






#[TentativeType]
public function getLineNo(): int {}









#[TentativeType]
public function C14N(
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $exclusive = false,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $withComments = false,
#[LanguageLevelTypeAware(['8.0' => 'array|null'], default: 'array')] $xpath = null,
#[LanguageLevelTypeAware(['8.0' => 'array|null'], default: 'array')] $nsPrefixes = null
): string|false {}











#[TentativeType]
public function C14NFile(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $uri,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $exclusive = false,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $withComments = false,
#[LanguageLevelTypeAware(['8.0' => 'array|null'], default: 'array')] $xpath = null,
#[LanguageLevelTypeAware(['8.0' => 'array|null'], default: 'array')] $nsPrefixes = null
): int|false {}
}






final class DOMException extends Exception
{




public $code;
}

class DOMStringList
{




public function item($index) {}
}

/**
@removed

*/
class DOMNameList
{




public function getName($index) {}





public function getNamespaceURI($index) {}
}

/**
@removed
*/
class DOMImplementationList
{




public function item($index) {}
}

/**
@removed
*/
class DOMImplementationSource
{




public function getDomimplementation($features) {}





public function getDomimplementations($features) {}
}







class DOMImplementation
{





#[TentativeType]
public function getFeature(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $feature,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $version
): never {}













public function hasFeature($feature, $version) {}

















public function createDocumentType(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $publicId,
#[PhpStormStubsElementAvailable(from: '8.0')] string $publicId = '',
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $systemId,
#[PhpStormStubsElementAvailable(from: '8.0')] string $systemId = ''
) {}





















public function createDocument(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $namespace,
#[PhpStormStubsElementAvailable(from: '8.0')] ?string $namespace = null,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $qualifiedName,
#[PhpStormStubsElementAvailable(from: '8.0')] string $qualifiedName = '',
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.3')] DOMDocumentType $doctype,
#[PhpStormStubsElementAvailable(from: '7.4')] #[LanguageLevelTypeAware(['8.0' => 'DOMDocumentType|null'], default: 'DOMDocumentType')] $doctype = null
) {}
}

class DOMNameSpaceNode
{
#[LanguageLevelTypeAware(['8.1' => 'DOMNode|null'], default: '')]
public $parentNode;

#[LanguageLevelTypeAware(['8.1' => 'DOMDocument|null'], default: '')]
public $ownerDocument;

#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $namespaceURI;

#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $localName;

#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $prefix;

#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $nodeType;

#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $nodeValue;

#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $nodeName;
}





class DOMDocumentFragment extends DOMNode implements DOMParentNode
{
#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $childElementCount;

#[LanguageLevelTypeAware(['8.1' => 'DOMElement|null'], default: '')]
public $lastElementChild;

#[LanguageLevelTypeAware(['8.1' => 'DOMElement|null'], default: '')]
public $firstElementChild;

public function __construct() {}









#[TentativeType]
public function appendXML(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data): bool {}




public function append(...$nodes): void {}




public function prepend(...$nodes): void {}
}






class DOMDocument extends DOMNode implements DOMParentNode
{




#[Deprecated("Actual encoding of the document, is a readonly equivalent to encoding.")]
#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $actualEncoding;






#[Deprecated("Configuration used when DOMDocument::normalizeDocument() is invoked.")]
#[LanguageLevelTypeAware(['8.1' => 'mixed'], default: '')]
public $config;






#[LanguageLevelTypeAware(['8.1' => 'DOMDocumentType|null'], default: '')]
public $doctype;







#[LanguageLevelTypeAware(['8.1' => 'DOMElement|null'], default: '')]
public $documentElement;






#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $documentURI;








#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $encoding;






#[LanguageLevelTypeAware(['8.1' => 'bool'], default: '')]
public $formatOutput;






#[LanguageLevelTypeAware(['8.1' => 'DOMImplementation'], default: '')]
public $implementation;






#[LanguageLevelTypeAware(['8.1' => 'bool'], default: '')]
public $preserveWhiteSpace = true;







#[LanguageLevelTypeAware(['8.1' => 'bool'], default: '')]
public $recover;







#[LanguageLevelTypeAware(['8.1' => 'bool'], default: '')]
public $resolveExternals;





#[Deprecated("Whether or not the document is standalone, as specified by the XML declaration, corresponds to xmlStandalone.")]
#[LanguageLevelTypeAware(['8.1' => 'bool'], default: '')]
public $standalone;






#[LanguageLevelTypeAware(['8.1' => 'bool'], default: '')]
public $strictErrorChecking = true;







#[LanguageLevelTypeAware(['8.1' => 'bool'], default: '')]
public $substituteEntities;






#[LanguageLevelTypeAware(['8.1' => 'bool'], default: '')]
public $validateOnParse = false;





#[Deprecated('Version of XML, corresponds to xmlVersion')]
#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $version;







#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $xmlEncoding;







#[LanguageLevelTypeAware(['8.1' => 'bool'], default: '')]
public $xmlStandalone;







#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $xmlVersion;

#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $childElementCount;

#[LanguageLevelTypeAware(['8.1' => 'DOMElement|null'], default: '')]
public $lastElementChild;

#[LanguageLevelTypeAware(['8.1' => 'DOMElement|null'], default: '')]
public $firstElementChild;















public function createElement(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $localName,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $value = ''
) {}






#[TentativeType]
public function createDocumentFragment(): DOMDocumentFragment {}









#[TentativeType]
public function createTextNode(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data): DOMText {}









#[TentativeType]
public function createComment(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data): DOMComment {}









public function createCDATASection(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data) {}












public function createProcessingInstruction(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $target,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.3')] $data,
#[PhpStormStubsElementAvailable(from: '7.4')] #[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data = null
) {}










public function createAttribute(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $localName) {}












public function createEntityReference(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name) {}











#[TentativeType]
public function getElementsByTagName(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName): DOMNodeList {}
















public function importNode(
DOMNode $node,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.3')] $deep,
#[PhpStormStubsElementAvailable(from: '7.4')] #[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $deep = false
) {}

















public function createElementNS(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespace,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $value = ''
) {}













public function createAttributeNS(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespace,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName
) {}















#[TentativeType]
public function getElementsByTagNameNS(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespace,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $localName
): DOMNodeList {}










#[TentativeType]
public function getElementById(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $elementId): ?DOMElement {}




public function adoptNode(DOMNode $node) {}




public function append(...$nodes): void {}




public function prepend(...$nodes): void {}






#[TentativeType]
public function normalizeDocument(): void {}






public function renameNode(DOMNode $node, $namespace, $qualifiedName) {}















public function load(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filename,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $options = null
) {}












public function save($filename, $options = null) {}















public function loadXML(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $source,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $options = null
) {}













#[TentativeType]
public function saveXML(
?DOMNode $node = null,
#[PhpStormStubsElementAvailable(from: '7.0')] #[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $options = null
): string|false {}







public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $version = '1.0',
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $encoding = ''
) {}







#[TentativeType]
public function validate(): bool {}










#[TentativeType]
public function xinclude(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $options = null): int|false {}















public function loadHTML(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $source,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $options = 0
) {}















public function loadHTMLFile(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filename,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $options = 0
) {}







public function saveHTML(DOMNode $node = null) {}









#[TentativeType]
public function saveHTMLFile(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filename): int|false {}













public function schemaValidate($filename, $options = null) {}











public function schemaValidateSource($source, $flags) {}









#[TentativeType]
public function relaxNGValidate(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filename): bool {}









#[TentativeType]
public function relaxNGValidateSource(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $source): bool {}















#[TentativeType]
public function registerNodeClass(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $baseClass,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $extendedClass
): bool {}
}





class DOMNodeList implements IteratorAggregate, Countable
{





#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
#[Immutable]
public $length;












public function item(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index) {}





#[TentativeType]
public function count(): int {}





public function getIterator(): Iterator {}
}

/**
@property-read


*/
class DOMNamedNodeMap implements IteratorAggregate, Countable
{









#[TentativeType]
public function getNamedItem(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName): ?DOMNode {}




public function setNamedItem(DOMNode $arg) {}




public function removeNamedItem($name) {}











#[TentativeType]
public function item(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.0')] $index = 0,
#[PhpStormStubsElementAvailable(from: '7.1')] #[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index
): ?DOMNode {}













#[TentativeType]
public function getNamedItemNS(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $namespaceURI = '',
#[PhpStormStubsElementAvailable(from: '8.0')] ?string $namespace,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $localName = '',
#[PhpStormStubsElementAvailable(from: '8.0')] string $localName
): ?DOMNode {}




public function setNamedItemNS(DOMNode $arg) {}





public function removeNamedItemNS($namespace, $localName) {}





#[TentativeType]
public function count(): int {}





public function getIterator(): Iterator {}
}






class DOMCharacterData extends DOMNode implements DOMChildNode
{





#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $data;






#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $length;

#[LanguageLevelTypeAware(['8.1' => 'DOMElement|null'], default: '')]
public $nextElementSibling;

#[LanguageLevelTypeAware(['8.1' => 'DOMElement|null'], default: '')]
public $previousElementSibling;














public function substringData(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $offset,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $count
) {}









#[TentativeType]
public function appendData(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data): bool {}












#[TentativeType]
public function insertData(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $offset,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data
): bool {}














#[TentativeType]
public function deleteData(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $offset,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $count
): bool {}

















#[TentativeType]
public function replaceData(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $offset,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $count,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data
): bool {}




public function remove(): void {}




public function before(...$nodes): void {}




public function after(...$nodes): void {}




public function replaceWith(...$nodes): void {}
}





class DOMAttr extends DOMNode
{






#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $name;







#[LanguageLevelTypeAware(['8.1' => 'DOMElement|null'], default: '')]
public $ownerElement;







#[LanguageLevelTypeAware(['8.1' => 'mixed'], default: '')]
public $schemaTypeInfo;







#[LanguageLevelTypeAware(['8.1' => 'bool'], default: '')]
public $specified;







#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $value;






#[TentativeType]
public function isId(): bool {}








public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $value = ''
) {}
}





class DOMElement extends DOMNode implements DOMParentNode, DOMChildNode
{





public $parentNode;






public $firstChild;






public $lastChild;






public $previousSibling;






public $nextSibling;






#[LanguageLevelTypeAware(['8.1' => 'mixed'], default: '')]
public $schemaTypeInfo;






#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $tagName;

#[LanguageLevelTypeAware(['8.1' => 'DOMElement|null'], default: '')]
public $firstElementChild;

#[LanguageLevelTypeAware(['8.1' => 'DOMElement|null'], default: '')]
public $lastElementChild;

#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $childElementCount;

#[LanguageLevelTypeAware(['8.1' => 'DOMElement|null'], default: '')]
public $previousElementSibling;

#[LanguageLevelTypeAware(['8.1' => 'DOMElement|null'], default: '')]
public $nextElementSibling;










#[TentativeType]
public function getAttribute(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName): string {}












public function setAttribute(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $value
) {}









#[TentativeType]
public function removeAttribute(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName): bool {}









public function getAttributeNode(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName) {}









public function setAttributeNode(DOMAttr $attr) {}









public function removeAttributeNode(DOMAttr $attr) {}











#[TentativeType]
public function getElementsByTagName(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName): DOMNodeList {}














#[TentativeType]
public function getAttributeNS(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespace,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $localName
): string {}















#[TentativeType]
public function setAttributeNS(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespace,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $value
): void {}












#[TentativeType]
public function removeAttributeNS(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespace,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $localName
): void {}












public function getAttributeNodeNS(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespace,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $localName
) {}







public function setAttributeNodeNS(DOMAttr $attr) {}















#[TentativeType]
public function getElementsByTagNameNS(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespace,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $localName
): DOMNodeList {}









#[TentativeType]
public function hasAttribute(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName): bool {}












#[TentativeType]
public function hasAttributeNS(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespace,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $localName
): bool {}













#[TentativeType]
public function setIdAttribute(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $isId
): void {}
















#[TentativeType]
public function setIdAttributeNS(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $namespace,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $isId
): void {}













#[TentativeType]
public function setIdAttributeNode(DOMAttr $attr, #[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $isId): void {}




public function remove(): void {}




public function before(...$nodes): void {}




public function after(...$nodes): void {}




public function replaceWith(...$nodes): void {}




public function append(...$nodes): void {}




public function prepend(...$nodes): void {}









public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $value = null,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $namespace = null
) {}
}






class DOMText extends DOMCharacterData
{




#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $wholeText;










public function splitText(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $offset) {}






#[TentativeType]
public function isWhitespaceInElementContent(): bool {}

#[TentativeType]
public function isElementContentWhitespace(): bool {}




public function replaceWholeText($content) {}






public function __construct(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data) {}
}






class DOMComment extends DOMCharacterData
{





public function __construct(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data) {}
}

/**
@removed
*/
class DOMTypeinfo {}

/**
@removed
*/
class DOMUserDataHandler
{
public function handle() {}
}

/**
@removed
*/
class DOMDomError {}

/**
@removed
*/
class DOMErrorHandler
{



public function handleError(DOMDomError $error) {}
}

/**
@removed
*/
class DOMLocator {}

/**
@removed
*/
class DOMConfiguration
{




public function setParameter($name, $value) {}




public function getParameter($name) {}





public function canSetParameter($name, $value) {}
}





class DOMCdataSection extends DOMText
{





public function __construct(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data) {}
}





class DOMDocumentType extends DOMNode
{





#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $publicId;






#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $systemId;






#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $name;






#[LanguageLevelTypeAware(['8.1' => 'DOMNamedNodeMap'], default: '')]
public $entities;






#[LanguageLevelTypeAware(['8.1' => 'DOMNamedNodeMap'], default: '')]
public $notations;






#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $internalSubset;
}





class DOMNotation extends DOMNode
{





#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $publicId;






#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $systemId;
}





class DOMEntity extends DOMNode
{





#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $publicId;







#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $systemId;






#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $notationName;







#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $actualEncoding;







#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $encoding;







#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $version;
}





class DOMEntityReference extends DOMNode
{





public function __construct(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name) {}
}





class DOMProcessingInstruction extends DOMNode
{



#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $target;




#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $data;







public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $value
) {}
}

class DOMStringExtend
{



public function findOffset16($offset32) {}




public function findOffset32($offset16) {}
}





class DOMXPath
{





#[LanguageLevelTypeAware(['8.1' => 'DOMDocument'], default: '')]
public $document;

#[LanguageLevelTypeAware(['8.1' => 'bool'], default: '')]
public $registerNodeNamespaces;







public function __construct(DOMDocument $document, #[PhpStormStubsElementAvailable(from: '8.0')] bool $registerNodeNS = true) {}












#[TentativeType]
public function registerNamespace(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $prefix,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $namespace
): bool {}



















#[TentativeType]
public function query(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] #[Language('XPath')] $expression,
#[LanguageLevelTypeAware(['8.0' => 'DOMNode|null'], default: '')] $contextNode = null,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $registerNodeNS = true
): mixed {}



















#[TentativeType]
public function evaluate(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] #[Language('XPath')] $expression,
#[LanguageLevelTypeAware(['8.0' => 'DOMNode|null'], default: '')] $contextNode = null,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $registerNodeNS = true
): mixed {}













public function registerPhpFunctions($restrict = null) {}
}

/**
@property-read
@property-read
@property-read


*/
interface DOMParentNode
{








public function append(...$nodes): void;









public function prepend(...$nodes): void;
}

/**
@property-read
@property-read


*/
interface DOMChildNode
{






public function remove(): void;








public function before(...$nodes): void;








public function after(...$nodes): void;









public function replaceWith(...$nodes): void;
}
