<?php



use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;

class XMLWriter
{













#[TentativeType]
public function openUri(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $uri): bool {}











#[TentativeType]
public function openMemory(): bool {}










#[TentativeType]
public function setIndent(#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $enable): bool {}










#[TentativeType]
public function setIndentString(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $indentation): bool {}







#[TentativeType]
public function startComment(): bool {}







#[TentativeType]
public function endComment(): bool {}










#[TentativeType]
public function startAttribute(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name): bool {}







#[TentativeType]
public function endAttribute(): bool {}













#[TentativeType]
public function writeAttribute(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $value
): bool {}
















#[TentativeType]
public function startAttributeNs(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $prefix,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespace
): bool {}



















#[TentativeType]
public function writeAttributeNs(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $prefix,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespace,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $value
): bool {}










#[TentativeType]
public function startElement(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name): bool {}







#[TentativeType]
public function endElement(): bool {}







#[TentativeType]
public function fullEndElement(): bool {}
















#[TentativeType]
public function startElementNs(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $prefix,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespace
): bool {}













#[TentativeType]
public function writeElement(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $content = null
): bool {}



















#[TentativeType]
public function writeElementNs(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $prefix,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $namespace,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $content = null
): bool {}










#[TentativeType]
public function startPi(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $target): bool {}







#[TentativeType]
public function endPi(): bool {}













#[TentativeType]
public function writePi(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $target,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $content
): bool {}







#[TentativeType]
public function startCdata(): bool {}







#[TentativeType]
public function endCdata(): bool {}










#[TentativeType]
public function writeCdata(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $content): bool {}










#[TentativeType]
public function text(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $content): bool {}










#[TentativeType]
public function writeRaw(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $content): bool {}
















#[TentativeType]
public function startDocument(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $version = '1.0',
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $encoding = null,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $standalone = null
): bool {}







#[TentativeType]
public function endDocument(): bool {}










#[TentativeType]
public function writeComment(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $content): bool {}
















#[TentativeType]
public function startDtd(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $publicId = null,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $systemId = null
): bool {}







#[TentativeType]
public function endDtd(): bool {}



















#[TentativeType]
public function writeDtd(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $publicId = null,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $systemId = null,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $content = null
): bool {}










#[TentativeType]
public function startDtdElement(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $qualifiedName): bool {}







#[TentativeType]
public function endDtdElement(): bool {}













#[TentativeType]
public function writeDtdElement(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $content
): bool {}










#[TentativeType]
public function startDtdAttlist(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name): bool {}







#[TentativeType]
public function endDtdAttlist(): bool {}













#[TentativeType]
public function writeDtdAttlist(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $content
): bool {}











#[TentativeType]
public function startDtdEntity(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $isParam
): bool {}







#[TentativeType]
public function endDtdEntity(): bool {}

















public function writeDtdEntity(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $content,
$pe,
$pubid,
$sysid,
$ndataid
) {}










#[TentativeType]
public function outputMemory(#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $flush = true): string {}












#[TentativeType]
public function flush(#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $empty = true): string|int {}
}














#[LanguageLevelTypeAware(["8.0" => "XMLWriter|false"], default: "resource|false")]
function xmlwriter_open_uri(string $uri) {}











#[LanguageLevelTypeAware(["8.0" => "XMLWriter|false"], default: "resource|false")]
function xmlwriter_open_memory() {}















function xmlwriter_set_indent(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, bool $enable): bool {}















function xmlwriter_set_indent_string(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $indentation): bool {}












function xmlwriter_start_comment(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer): bool {}












function xmlwriter_end_comment(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer): bool {}















function xmlwriter_start_attribute(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $name): bool {}











function xmlwriter_end_attribute(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer): bool {}


















function xmlwriter_write_attribute(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $name, string $value): bool {}





















function xmlwriter_start_attribute_ns(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, ?string $prefix, string $name, ?string $namespace): bool {}
























function xmlwriter_write_attribute_ns(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, ?string $prefix, string $name, ?string $namespace, string $value): bool {}















function xmlwriter_start_element(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $name): bool {}











function xmlwriter_end_element(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer): bool {}











function xmlwriter_full_end_element(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer): bool {}





















function xmlwriter_start_element_ns(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, ?string $prefix, string $name, ?string $namespace): bool {}


















function xmlwriter_write_element(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $name, ?string $content): bool {}
























function xmlwriter_write_element_ns(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, ?string $prefix, string $name, ?string $namespace, ?string $content): bool {}















function xmlwriter_start_pi(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $target): bool {}











function xmlwriter_end_pi(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer): bool {}



















function xmlwriter_write_pi(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $target, string $content): bool {}












function xmlwriter_start_cdata(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer): bool {}












function xmlwriter_end_cdata(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer): bool {}















function xmlwriter_write_cdata(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $content): bool {}















function xmlwriter_text(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $content): bool {}















function xmlwriter_write_raw(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $content): bool {}





















function xmlwriter_start_document(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, ?string $version = '1.0', ?string $encoding, ?string $standalone): bool {}












function xmlwriter_end_document(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer): bool {}















function xmlwriter_write_comment(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $content): bool {}





















function xmlwriter_start_dtd(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $qualifiedName, ?string $publicId, ?string $systemId): bool {}











function xmlwriter_end_dtd(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer): bool {}
























function xmlwriter_write_dtd(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $name, ?string $publicId, ?string $systemId, ?string $content): bool {}















function xmlwriter_start_dtd_element(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $qualifiedName): bool {}








function xmlwriter_end_dtd_element(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer): bool {}


















function xmlwriter_write_dtd_element(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $name, string $content): bool {}















function xmlwriter_start_dtd_attlist(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $name): bool {}











function xmlwriter_end_dtd_attlist(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer): bool {}


















function xmlwriter_write_dtd_attlist(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $name, string $content): bool {}
















function xmlwriter_start_dtd_entity(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, string $name, bool $isParam): bool {}











function xmlwriter_end_dtd_entity(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer): bool {}






















function xmlwriter_write_dtd_entity(
#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer,
string $name,
string $content,
#[PhpStormStubsElementAvailable(from: '8.0')] bool $isParam = false,
#[PhpStormStubsElementAvailable(from: '8.0')] ?string $publicId = null,
#[PhpStormStubsElementAvailable(from: '8.0')] ?string $systemId = null,
#[PhpStormStubsElementAvailable(from: '8.0')] ?string $notationData = null
): bool {}















function xmlwriter_output_memory(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, bool $flush = true): string {}

















function xmlwriter_flush(#[LanguageLevelTypeAware(["8.0" => "XMLWriter"], default: "resource")] $writer, bool $empty = true): string|int {}
