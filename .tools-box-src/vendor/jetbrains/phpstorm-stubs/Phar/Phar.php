<?php


use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;






class PharException extends Exception {}






class Phar extends RecursiveDirectoryIterator implements RecursiveIterator, SeekableIterator, Countable, ArrayAccess
{
public const BZ2 = 8192;
public const GZ = 4096;
public const NONE = 0;
public const PHAR = 1;
public const TAR = 2;
public const ZIP = 3;
public const COMPRESSED = 61440;
public const PHP = 0;
public const PHPS = 1;
public const MD5 = 1;
public const OPENSSL = 16;
public const SHA1 = 2;
public const SHA256 = 3;
public const SHA512 = 4;
public const OPENSSL_SHA256 = 5;
public const OPENSSL_SHA512 = 6;



















public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filename,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = FilesystemIterator::KEY_AS_PATHNAME|FilesystemIterator::CURRENT_AS_FILEINFO,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $alias = null,
#[PhpStormStubsElementAvailable(from: '5.3', to: '5.6')] $fileformat = null
) {}

public function __destruct() {}










#[TentativeType]
public function addEmptyDir(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $directory = '',
#[PhpStormStubsElementAvailable(from: '8.0')] string $directory
): void {}














#[TentativeType]
public function addFile(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filename,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $localName = null
): void {}













#[TentativeType]
public function addFromString(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $localName,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $contents = '',
#[PhpStormStubsElementAvailable(from: '8.0')] string $contents
): void {}


















#[TentativeType]
public function buildFromDirectory(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $directory,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $pattern = null
): array {}

















#[TentativeType]
public function buildFromIterator(
Traversable $iterator,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $baseDirectory = null
): array {}












#[TentativeType]
public function compressFiles(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $compression): void {}







public function decompressFiles() {}



















#[TentativeType]
public function compress(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $compression,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $extension = null
): ?Phar {}














#[TentativeType]
public function decompress(#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $extension = null): ?Phar {}
































#[TentativeType]
public function convertToExecutable(
#[LanguageLevelTypeAware(['8.0' => 'int|null'], default: '')] $format = 9021976,
#[LanguageLevelTypeAware(['8.0' => 'int|null'], default: '')] $compression = 9021976,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $extension = null
): ?Phar {}






























#[TentativeType]
public function convertToData(
#[LanguageLevelTypeAware(['8.0' => 'int|null'], default: '')] $format = 9021976,
#[LanguageLevelTypeAware(['8.0' => 'int|null'], default: '')] $compression = 9021976,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $extension = null
): ?PharData {}










public function copy(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $to,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $from
) {}









#[TentativeType]
public function count(#[PhpStormStubsElementAvailable(from: '8.0')] int $mode = COUNT_NORMAL): int {}











public function delete(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $localName) {}








public function delMetadata() {}

















#[TentativeType]
public function extractTo(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $directory,
#[LanguageLevelTypeAware(['8.0' => 'array|string|null'], default: '')] $files = null,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $overwrite = false
): bool {}





#[TentativeType]
public function getAlias(): ?string {}










#[TentativeType]
public function getMetadata(#[PhpStormStubsElementAvailable(from: '8.0')] array $unserializeOptions = []): mixed {}







#[TentativeType]
public function getModified(): bool {}














#[ArrayShape(["hash" => "string", "hash_type" => "string"])]
#[TentativeType]
public function getSignature(): array|false {}








#[TentativeType]
public function getStub(): string {}











#[TentativeType]
public function getVersion(): string {}







#[TentativeType]
public function hasMetadata(): bool {}







#[TentativeType]
public function isBuffering(): bool {}







#[TentativeType]
public function isCompressed(): int|false {}











#[TentativeType]
public function isFileFormat(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $format): bool {}







#[TentativeType]
public function isWritable(): bool {}










#[TentativeType]
public function offsetExists($localName): bool {}











#[TentativeType]
public function offsetGet($localName): SplFileInfo {}













#[TentativeType]
public function offsetSet($localName, $value): void {}










#[TentativeType]
public function offsetUnset($localName): void {}











#[TentativeType]
public function setAlias(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $alias): bool {}













#[TentativeType]
public function setDefaultStub(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $index = null,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $webIndex = null
): bool {}










#[TentativeType]
public function setMetadata(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $metadata): void {}
























#[TentativeType]
public function setSignatureAlgorithm(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $algo,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $privateKey = null
): void {}













public function setStub(
$stub,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $length = -1
) {}







#[TentativeType]
public function startBuffering(): void {}







#[TentativeType]
public function stopBuffering(): void {}







final public static function apiVersion(): string {}












final public static function canCompress(int $compression = 0): bool {}







final public static function canWrite(): bool {}











final public static function createDefaultStub(?string $index = null, ?string $webIndex = null): string {}










final public static function getSupportedCompression(): array {}








final public static function getSupportedSignatures(): array {}







final public static function interceptFileFuncs(): void {}














final public static function isValidPharFilename(string $filename, bool $executable = true): bool {}
















final public static function loadPhar(string $filename, ?string $alias = null): bool {}














final public static function mapPhar(?string $alias = null, int $offset = 0): bool {}











final public static function running(
#[PhpStormStubsElementAvailable(from: '5.3', to: '5.6')] $returnPhar,
#[PhpStormStubsElementAvailable(from: '7.0')] bool $returnPhar = true
): string {}














final public static function mount(string $pharPath, string $externalPath): void {}














final public static function mungServer(array $variables): void {}











final public static function unlinkArchive(string $filename): bool {}















































































final public static function webPhar(
?string $alias = null,
?string $index = "index.php",
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: 'string')] $fileNotFoundScript = null,
array $mimeTypes = null,
?callable $rewrite = null
): void {}








public function hasChildren($allow_links = false) {}








public function getChildren() {}






public function rewind() {}






public function next() {}







public function key() {}







public function current() {}






public function valid() {}









public function seek($position) {}

public function _bad_state_ex() {}
}









class PharData extends Phar
{





















public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filename,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = FilesystemIterator::KEY_AS_PATHNAME|FilesystemIterator::CURRENT_AS_FILEINFO,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $alias = null,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $format = Phar::TAR
) {}





#[TentativeType]
public function offsetExists($localName): bool {}





#[TentativeType]
public function offsetGet($localName): SplFileInfo {}













#[TentativeType]
public function offsetSet($localName, $value): void {}










#[TentativeType]
public function offsetUnset($localName): void {}








public function hasChildren($allow_links = false) {}








public function getChildren() {}






public function rewind() {}






public function next() {}







public function key() {}







public function current() {}






public function valid() {}









public function seek($position) {}
}






class PharFileInfo extends SplFileInfo
{










public function __construct(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filename) {}

public function __destruct() {}










#[TentativeType]
public function chmod(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $perms): void {}








public function compress(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $compression) {}







public function decompress() {}












public function delMetadata() {}







#[TentativeType]
public function getCompressedSize(): int {}







#[TentativeType]
public function getCRC32(): int {}

#[TentativeType]
public function getContent(): string {}










#[TentativeType]
public function getMetadata(#[PhpStormStubsElementAvailable(from: '8.0')] array $unserializeOptions = []): mixed {}







#[TentativeType]
public function getPharFlags(): int {}







#[TentativeType]
public function hasMetadata(): bool {}











#[TentativeType]
public function isCompressed(#[LanguageLevelTypeAware(['8.0' => 'int|null'], default: '')] $compression = 9021976): bool {}







#[TentativeType]
public function isCRCChecked(): bool {}










#[TentativeType]
public function setMetadata(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $metadata): void {}
}

