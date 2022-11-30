<?php


use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;





class SimpleXMLElement implements Traversable, ArrayAccess, Countable, Iterator, Stringable, RecursiveIterator
{












#[Pure]
public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $options = 0,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $dataIsURL = false,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $namespaceOrPrefix = "",
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $isPrefix = false
) {}







private function __get($name) {}














#[TentativeType]
public function asXML(#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $filename = null): string|bool {}














#[TentativeType]
public function saveXML(#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $filename = null): string|bool {}










#[TentativeType]
public function xpath(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $expression): array|false|null {}















#[TentativeType]
public function registerXPathNamespace(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $prefix,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $namespace
): bool {}


















#[TentativeType]
public function attributes(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespaceOrPrefix = null,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $isPrefix = false
): ?static {}

















#[Pure]
#[TentativeType]
public function children(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespaceOrPrefix = null,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $isPrefix = false
): ?static {}












#[Pure]
#[TentativeType]
public function getNamespaces(#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $recursive = false): array {}
















#[Pure]
#[TentativeType]
public function getDocNamespaces(
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $recursive = false,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $fromRoot = true
): array|false {}








#[Pure]
#[TentativeType]
public function getName(): string {}

















#[TentativeType]
public function addChild(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $value = null,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespace = null
): ?static {}
















#[TentativeType]
public function addAttribute(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $value = null,
#[PhpStormStubsElementAvailable(from: '8.0')] string $value,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespace = null
): void {}







#[TentativeType]
public function __toString(): string {}






#[Pure]
#[TentativeType]
public function count(): int {}







#[Pure]
public function offsetExists($offset) {}







#[Pure]
public function offsetGet($offset) {}








public function offsetSet($offset, $value) {}







public function offsetUnset($offset) {}






#[TentativeType]
public function rewind(): void {}






#[Pure]
#[TentativeType]
public function valid(): bool {}






#[Pure]
#[TentativeType]
public function current(): ?static {}






#[TentativeType]
#[LanguageLevelTypeAware(['8.0' => 'string'], default: 'string|false')]
public function key() {}






#[TentativeType]
public function next(): void {}





#[Pure]
#[TentativeType]
public function hasChildren(): bool {}




#[Pure]
#[TentativeType]
public function getChildren(): ?SimpleXMLElement {}
}





class SimpleXMLIterator extends SimpleXMLElement implements RecursiveIterator, Countable, Stringable
{





public function rewind() {}






#[Pure]
public function valid() {}






#[Pure]
public function current() {}






public function key() {}






public function next() {}






#[Pure]
public function hasChildren() {}







#[Pure]
public function getChildren() {}







public function __toString() {}






#[Pure]
public function count() {}
}



































function simplexml_load_file(string $filename, ?string $class_name = "SimpleXMLElement", int $options = 0, string $namespace_or_prefix = "", bool $is_prefix = false): SimpleXMLElement|false {}



























function simplexml_load_string(string $data, ?string $class_name = "SimpleXMLElement", int $options = 0, string $namespace_or_prefix = "", bool $is_prefix = false): SimpleXMLElement|false {}















function simplexml_import_dom(SimpleXMLElement|DOMNode $node, ?string $class_name = "SimpleXMLElement"): ?SimpleXMLElement {}


