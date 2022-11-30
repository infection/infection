<?php


use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;

/**
@property-read
@property-read
@property-read
@property-read
@property-read
@property-read
@property-read
@property-read
@property-read
@property-read
@property-read
@property-read
@property-read
@property-read





*/
class XMLReader
{



public const NONE = 0;




public const ELEMENT = 1;




public const ATTRIBUTE = 2;




public const TEXT = 3;




public const CDATA = 4;




public const ENTITY_REF = 5;




public const ENTITY = 6;




public const PI = 7;




public const COMMENT = 8;




public const DOC = 9;




public const DOC_TYPE = 10;




public const DOC_FRAGMENT = 11;




public const NOTATION = 12;




public const WHITESPACE = 13;




public const SIGNIFICANT_WHITESPACE = 14;




public const END_ELEMENT = 15;




public const END_ENTITY = 16;




public const XML_DECLARATION = 17;




public const LOADDTD = 1;




public const DEFAULTATTRS = 2;




public const VALIDATE = 3;




public const SUBST_ENTITIES = 4;







public function close() {}











#[TentativeType]
public function getAttribute(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name): ?string {}











#[TentativeType]
public function getAttributeNo(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index): ?string {}















#[TentativeType]
public function getAttributeNs(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $namespace
): ?string {}











#[TentativeType]
public function getParserProperty(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $property): bool {}







#[TentativeType]
public function isValid(): bool {}










#[TentativeType]
public function lookupNamespace(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $prefix): ?string {}










#[TentativeType]
public function moveToAttributeNo(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index): bool {}










#[TentativeType]
public function moveToAttribute(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name): bool {}













#[TentativeType]
public function moveToAttributeNs(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $namespace
): bool {}








#[TentativeType]
public function moveToElement(): bool {}







#[TentativeType]
public function moveToFirstAttribute(): bool {}







#[TentativeType]
public function moveToNextAttribute(): bool {}


















public static function open(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $uri,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $encoding = null,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = 0
) {}







#[TentativeType]
public function read(): bool {}










#[TentativeType]
public function next(#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $name = null): bool {}






#[TentativeType]
public function readInnerXml(): string {}






#[TentativeType]
public function readOuterXml(): string {}







#[TentativeType]
public function readString(): string {}









#[TentativeType]
public function setSchema(#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $filename): bool {}















#[TentativeType]
public function setParserProperty(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $property,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $value
): bool {}









#[TentativeType]
public function setRelaxNGSchema(#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $filename): bool {}










#[TentativeType]
public function setRelaxNGSchemaSource(#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $source): bool {}


















public static function XML(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $source,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $encoding = null,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = 0
) {}








#[TentativeType]
public function expand(
#[PhpStormStubsElementAvailable(from: '7.0')] #[LanguageLevelTypeAware(['8.0' => 'DOMNode|null'], default: '')] $baseNode = null
): DOMNode|false {}
}

