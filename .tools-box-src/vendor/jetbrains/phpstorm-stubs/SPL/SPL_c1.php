<?php


use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;






class SplFileInfo implements Stringable
{






public function __construct(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filename) {}







#[TentativeType]
public function getPath(): string {}







#[TentativeType]
public function getFilename(): string {}








#[TentativeType]
public function getExtension(): string {}










#[TentativeType]
public function getBasename(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $suffix = null): string {}







#[TentativeType]
public function getPathname(): string {}







#[TentativeType]
public function getPerms(): int|false {}







#[TentativeType]
public function getInode(): int|false {}







#[TentativeType]
public function getSize(): int|false {}







#[TentativeType]
public function getOwner(): int|false {}







#[TentativeType]
public function getGroup(): int|false {}







#[TentativeType]
public function getATime(): int|false {}







#[TentativeType]
public function getMTime(): int|false {}







#[TentativeType]
public function getCTime(): int|false {}









#[TentativeType]
public function getType(): string|false {}







#[TentativeType]
public function isWritable(): bool {}







#[TentativeType]
public function isReadable(): bool {}







#[TentativeType]
public function isExecutable(): bool {}







#[TentativeType]
public function isFile(): bool {}







#[TentativeType]
public function isDir(): bool {}







#[TentativeType]
public function isLink(): bool {}







#[TentativeType]
public function getLinkTarget(): string|false {}







#[TentativeType]
public function getRealPath(): string|false {}










#[TentativeType]
public function getFileInfo(#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $class = null): SplFileInfo {}










#[TentativeType]
public function getPathInfo(#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $class = null): ?SplFileInfo {}
















#[TentativeType]
public function openFile(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $mode = 'r',
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $useIncludePath = false,
$context = null
): SplFileObject {}










#[TentativeType]
public function setFileClass(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $class = SplFileObject::class): void {}










#[TentativeType]
public function setInfoClass(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $class = SplFileInfo::class): void {}







#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')]
public function __toString() {}

#[TentativeType]
final public function _bad_state_ex(): void {}

public function __wakeup() {}





#[TentativeType]
public function __debugInfo(): array {}
}






class DirectoryIterator extends SplFileInfo implements SeekableIterator
{







public function __construct(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $directory) {}







#[TentativeType]
public function isDot(): bool {}






#[TentativeType]
public function rewind(): void {}






#[TentativeType]
public function valid(): bool {}






#[TentativeType]
public function key(): mixed {}






#[TentativeType]
public function current(): mixed {}






#[TentativeType]
public function next(): void {}









#[TentativeType]
public function seek(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $offset): void {}
}





class FilesystemIterator extends DirectoryIterator
{
public const CURRENT_MODE_MASK = 240;
public const CURRENT_AS_PATHNAME = 32;
public const CURRENT_AS_FILEINFO = 0;
public const CURRENT_AS_SELF = 16;
public const KEY_MODE_MASK = 3840;
public const KEY_AS_PATHNAME = 0;
public const FOLLOW_SYMLINKS = 16384;
public const KEY_AS_FILENAME = 256;
public const NEW_CURRENT_AND_KEY = 256;
public const SKIP_DOTS = 4096;
public const UNIX_PATHS = 8192;
public const OTHER_MODE_MASK = 28672;








public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $directory,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = FilesystemIterator::KEY_AS_PATHNAME|FilesystemIterator::CURRENT_AS_FILEINFO|FilesystemIterator::SKIP_DOTS
) {}






#[TentativeType]
public function rewind(): void {}






public function next() {}







#[TentativeType]
public function key(): string {}







#[TentativeType]
public function current(): SplFileInfo|FilesystemIterator|string {}






#[TentativeType]
public function getFlags(): int {}










#[TentativeType]
public function setFlags(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $flags = null,
#[PhpStormStubsElementAvailable(from: '8.0')] int $flags
): void {}
}






class RecursiveDirectoryIterator extends FilesystemIterator implements RecursiveIterator
{








public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $directory,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = FilesystemIterator::KEY_AS_PATHNAME|FilesystemIterator::CURRENT_AS_FILEINFO
) {}








#[TentativeType]
public function hasChildren(#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $allowLinks = false): bool {}






#[TentativeType]
public function getChildren(): RecursiveDirectoryIterator {}






#[TentativeType]
public function getSubPath(): string {}






#[TentativeType]
public function getSubPathname(): string {}






public function rewind() {}






public function next() {}







public function key() {}







public function current() {}
}






class GlobIterator extends FilesystemIterator implements Countable
{






public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $pattern,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = FilesystemIterator::KEY_AS_PATHNAME|FilesystemIterator::CURRENT_AS_FILEINFO
) {}







#[TentativeType]
public function count(): int {}
}





class SplFileObject extends SplFileInfo implements RecursiveIterator, SeekableIterator
{



public const DROP_NEW_LINE = 1;




public const READ_AHEAD = 2;




public const SKIP_EMPTY = 4;




public const READ_CSV = 8;














public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filename,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $mode = 'r',
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $useIncludePath = false,
$context = null
) {}






#[TentativeType]
public function rewind(): void {}






#[TentativeType]
public function eof(): bool {}






#[TentativeType]
public function valid(): bool {}






#[TentativeType]
public function fgets(): string {}










#[TentativeType]
public function fread(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $length): string|false {}




















#[TentativeType]
#[LanguageLevelTypeAware(['8.1' => 'array|false'], default: 'array|false|null')]
public function fgetcsv(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $separator = ",",
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $enclosure = "\"",
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $escape = "\\"
) {}















#[TentativeType]
public function fputcsv(
array $fields,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $separator = ',',
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $enclosure = '"',
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $escape = "\\",
#[PhpStormStubsElementAvailable('8.1')] string $eol = PHP_EOL
): int|false {}















#[TentativeType]
public function setCsvControl(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $separator = ",",
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $enclosure = "\"",
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $escape = "\\"
): void {}






#[TentativeType]
public function getCsvControl(): array {}













#[TentativeType]
public function flock(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $operation, &$wouldBlock = null): bool {}






#[TentativeType]
public function fflush(): bool {}






#[TentativeType]
public function ftell(): int|false {}




















#[TentativeType]
public function fseek(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $offset,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $whence = SEEK_SET
): int {}






#[TentativeType]
public function fgetc(): string|false {}







#[TentativeType]
public function fpassthru(): int {}

/**
@removed








*/
#[Deprecated(since: '7.3')]
public function fgetss($allowable_tags = null) {}















#[TentativeType]
public function fscanf(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $format,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] &...$vars
): array|int|null {}















#[LanguageLevelTypeAware(['7.4' => 'int|false'], default: 'int')]
#[TentativeType]
public function fwrite(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $length = null
): int|false {}







#[TentativeType]
public function fstat(): array {}















#[TentativeType]
public function ftruncate(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $size): bool {}






#[TentativeType]
public function current(): string|array|false {}






#[TentativeType]
public function key(): int {}






#[TentativeType]
public function next(): void {}











#[TentativeType]
public function setFlags(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags): void {}






#[TentativeType]
public function getFlags(): int {}









#[TentativeType]
public function setMaxLineLen(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $maxLength): void {}







#[TentativeType]
public function getMaxLineLen(): int {}







#[TentativeType]
#[LanguageLevelTypeAware(['8.2' => 'false'], default: 'bool')]
public function hasChildren() {}






#[TentativeType]
#[LanguageLevelTypeAware(['8.2' => 'null|null'], default: 'null|RecursiveIterator')]
public function getChildren() {}









#[TentativeType]
public function seek(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $line): void {}







#[TentativeType]
public function getCurrentLine(): string {}





#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')]
public function __toString() {}
}





class SplTempFileObject extends SplFileObject
{







public function __construct(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $maxMemory = 2097152) {}
}

/**
@template
@template-implements
@template-implements


*/
class SplDoublyLinkedList implements Iterator, Countable, ArrayAccess, Serializable
{
public const IT_MODE_LIFO = 2;
public const IT_MODE_FIFO = 0;
public const IT_MODE_DELETE = 1;
public const IT_MODE_KEEP = 0;









#[TentativeType]
public function add(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value
): void {}






#[TentativeType]
public function pop(): mixed {}






#[TentativeType]
public function shift(): mixed {}









#[TentativeType]
public function push(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value): void {}









#[TentativeType]
public function unshift(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value): void {}






#[TentativeType]
public function top(): mixed {}






#[TentativeType]
public function bottom(): mixed {}






#[TentativeType]
public function count(): int {}






#[TentativeType]
public function isEmpty(): bool {}











#[TentativeType]
public function setIteratorMode(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $mode): int {}






#[TentativeType]
public function getIteratorMode(): int {}









#[TentativeType]
public function offsetExists($index): bool {}









#[TentativeType]
public function offsetGet($index): mixed {}












#[TentativeType]
public function offsetSet($index, #[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value): void {}









#[TentativeType]
public function offsetUnset($index): void {}






#[TentativeType]
public function rewind(): void {}






#[TentativeType]
public function current(): mixed {}






#[TentativeType]
public function key(): int {}






#[TentativeType]
public function next(): void {}






#[TentativeType]
public function prev(): void {}






#[TentativeType]
public function valid(): bool {}








#[TentativeType]
public function unserialize(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data): void {}







#[TentativeType]
public function serialize(): string {}





#[TentativeType]
public function __debugInfo(): array {}





#[TentativeType]
public function __serialize(): array {}





#[TentativeType]
public function __unserialize(array $data): void {}
}

/**
@template


*/
class SplQueue extends SplDoublyLinkedList
{








#[TentativeType]
public function enqueue(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value): void {}






#[TentativeType]
public function dequeue(): mixed {}











public function setIteratorMode($mode) {}
}

/**
@template
@template-extends


*/
class SplStack extends SplDoublyLinkedList
{










public function setIteratorMode($mode) {}
}

/**
@template
@template-implements


*/
abstract class SplHeap implements Iterator, Countable
{





#[TentativeType]
public function extract(): mixed {}









#[TentativeType]
public function insert(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value): bool {}






#[TentativeType]
public function top(): mixed {}






#[TentativeType]
public function count(): int {}






#[TentativeType]
public function isEmpty(): bool {}






#[TentativeType]
public function rewind(): void {}






#[TentativeType]
public function current(): mixed {}






#[TentativeType]
public function key(): int {}






#[TentativeType]
public function next(): void {}






#[TentativeType]
public function valid(): bool {}






#[TentativeType]
public function recoverFromCorruption(): bool {}















abstract protected function compare($value1, $value2);




#[TentativeType]
public function isCorrupted(): bool {}





#[TentativeType]
public function __debugInfo(): array {}
}

/**
@template
@template-extends


*/
class SplMinHeap extends SplHeap
{














#[TentativeType]
protected function compare(
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value1,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value2
): int {}






public function extract() {}









public function insert($value) {}






public function top() {}






public function count() {}






public function isEmpty() {}






public function rewind() {}






public function current() {}






public function key() {}






public function next() {}






public function valid() {}






public function recoverFromCorruption() {}
}

/**
@template
@template-extends


*/
class SplMaxHeap extends SplHeap
{














#[TentativeType]
protected function compare(
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value1,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value2
): int {}
}

/**
@template
@template
@template-implements



*/
class SplPriorityQueue implements Iterator, Countable
{
public const EXTR_BOTH = 3;
public const EXTR_PRIORITY = 2;
public const EXTR_DATA = 1;















#[TentativeType]
public function compare(
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $priority1,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $priority2
): int {}












public function insert(
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $priority
) {}












#[TentativeType]
public function setExtractFlags(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags): int {}






#[TentativeType]
public function top(): mixed {}






#[TentativeType]
public function extract(): mixed {}






#[TentativeType]
public function count(): int {}






#[TentativeType]
public function isEmpty(): bool {}






#[TentativeType]
public function rewind(): void {}






#[TentativeType]
public function current(): mixed {}






#[TentativeType]
public function key(): int {}






#[TentativeType]
public function next(): void {}






#[TentativeType]
public function valid(): bool {}






public function recoverFromCorruption() {}




#[TentativeType]
public function isCorrupted(): bool {}




#[TentativeType]
public function getExtractFlags(): int {}





#[TentativeType]
public function __debugInfo(): array {}
}

/**
@template
@template-implements
@template-implements
@template-implements






*/
class SplFixedArray implements Iterator, ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{





public function __construct(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $size = 0) {}






#[TentativeType]
public function count(): int {}






#[TentativeType]
public function toArray(): array {}













#[TentativeType]
public static function fromArray(
#[LanguageLevelTypeAware(['8.0' => 'array'], default: '')] $array,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $preserveKeys = true
): SplFixedArray {}






#[TentativeType]
public function getSize(): int {}









public function setSize(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $size) {}









#[TentativeType]
public function offsetExists($index): bool {}









#[TentativeType]
public function offsetGet($index): mixed {}












#[TentativeType]
public function offsetSet($index, #[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value): void {}









#[TentativeType]
public function offsetUnset($index): void {}






public function rewind() {}






public function current() {}






public function key() {}






public function next() {}






#[TentativeType]
public function valid(): bool {}

#[TentativeType]
public function __wakeup(): void {}




public function getIterator(): Iterator {}

public function jsonSerialize(): array {}
}






interface SplObserver
{








#[TentativeType]
public function update(SplSubject $subject): void;
}






interface SplSubject
{








#[TentativeType]
public function attach(SplObserver $observer): void;









#[TentativeType]
public function detach(SplObserver $observer): void;






#[TentativeType]
public function notify(): void;
}

/**
@template
@template
@template-implements
@template-implements




*/
class SplObjectStorage implements Countable, Iterator, Serializable, ArrayAccess
{











#[TentativeType]
public function attach(
#[LanguageLevelTypeAware(['8.0' => 'object'], default: '')] $object,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $info = null
): void {}









#[TentativeType]
public function detach(#[LanguageLevelTypeAware(['8.0' => 'object'], default: '')] $object): void {}









#[TentativeType]
public function contains(#[LanguageLevelTypeAware(['8.0' => 'object'], default: '')] $object): bool {}









#[TentativeType]
public function addAll(#[LanguageLevelTypeAware(['8.0' => 'SplObjectStorage'], default: '')] $storage): int {}









#[TentativeType]
public function removeAll(#[LanguageLevelTypeAware(['8.0' => 'SplObjectStorage'], default: '')] $storage): int {}










#[TentativeType]
public function removeAllExcept(#[LanguageLevelTypeAware(['8.0' => 'SplObjectStorage'], default: '')] $storage): int {}






#[TentativeType]
public function getInfo(): mixed {}









#[TentativeType]
public function setInfo(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $info): void {}







#[TentativeType]
public function count(#[PhpStormStubsElementAvailable(from: '8.0')] int $mode = COUNT_NORMAL): int {}






#[TentativeType]
public function rewind(): void {}






#[TentativeType]
public function valid(): bool {}






#[TentativeType]
public function key(): int {}






#[TentativeType]
public function current(): object {}






#[TentativeType]
public function next(): void {}










#[TentativeType]
public function unserialize(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data): void {}







#[TentativeType]
public function serialize(): string {}










#[TentativeType]
public function offsetExists($object): bool {}












#[TentativeType]
public function offsetSet(
#[LanguageLevelTypeAware(['8.1' => 'mixed'], default: '')] $object,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $info = null
): void {}









#[TentativeType]
public function offsetUnset($object): void {}









#[TentativeType]
public function offsetGet($object): mixed {}










#[TentativeType]
public function getHash(#[LanguageLevelTypeAware(['8.0' => 'object'], default: '')] $object): string {}





#[TentativeType]
public function __serialize(): array {}





#[TentativeType]
public function __unserialize(array $data): void {}





#[TentativeType]
public function __debugInfo(): array {}
}





class MultipleIterator implements Iterator
{
public const MIT_NEED_ANY = 0;
public const MIT_NEED_ALL = 1;
public const MIT_KEYS_NUMERIC = 0;
public const MIT_KEYS_ASSOC = 2;






public function __construct(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $flags,
#[PhpStormStubsElementAvailable(from: '8.0')] int $flags = MultipleIterator::MIT_NEED_ALL|MultipleIterator::MIT_KEYS_NUMERIC
) {}






#[TentativeType]
public function getFlags(): int {}










#[TentativeType]
public function setFlags(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags): void {}













#[TentativeType]
public function attachIterator(Iterator $iterator, #[LanguageLevelTypeAware(['8.0' => 'int|string|null'], default: '')] $info = null): void {}









#[TentativeType]
public function detachIterator(Iterator $iterator): void {}









#[TentativeType]
public function containsIterator(Iterator $iterator): bool {}






#[TentativeType]
public function countIterators(): int {}






#[TentativeType]
public function rewind(): void {}







#[TentativeType]
public function valid(): bool {}







#[TentativeType]
public function key(): array {}









#[TentativeType]
public function current(): array {}






#[TentativeType]
public function next(): void {}





#[TentativeType]
public function __debugInfo(): array {}
}
