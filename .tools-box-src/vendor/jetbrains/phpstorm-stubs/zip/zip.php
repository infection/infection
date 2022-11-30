<?php


use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;





class ZipArchive implements Countable
{





public const LIBZIP_VERSION = '1.7.3';





public const CREATE = 1;





public const EXCL = 2;





public const CHECKCONS = 4;






public const OVERWRITE = 8;





public const FL_NOCASE = 1;





public const FL_NODIR = 2;





public const FL_COMPRESSED = 4;





public const FL_UNCHANGED = 8;
public const FL_RECOMPRESS = 16;
public const FL_ENCRYPTED = 32;
public const FL_OVERWRITE = 8192;
public const FL_LOCAL = 256;
public const FL_CENTRAL = 512;
public const EM_TRAD_PKWARE = 1;
public const EM_UNKNOWN = 65535;





public const CM_DEFAULT = -1;





public const CM_STORE = 0;





public const CM_SHRINK = 1;





public const CM_REDUCE_1 = 2;





public const CM_REDUCE_2 = 3;





public const CM_REDUCE_3 = 4;





public const CM_REDUCE_4 = 5;





public const CM_IMPLODE = 6;





public const CM_DEFLATE = 8;





public const CM_DEFLATE64 = 9;





public const CM_PKWARE_IMPLODE = 10;





public const CM_BZIP2 = 12;
public const CM_LZMA = 14;
public const CM_TERSE = 18;
public const CM_LZ77 = 19;
public const CM_WAVPACK = 97;
public const CM_PPMD = 98;





public const ER_OK = 0;





public const ER_MULTIDISK = 1;





public const ER_RENAME = 2;





public const ER_CLOSE = 3;





public const ER_SEEK = 4;





public const ER_READ = 5;





public const ER_WRITE = 6;





public const ER_CRC = 7;





public const ER_ZIPCLOSED = 8;





public const ER_NOENT = 9;





public const ER_EXISTS = 10;





public const ER_OPEN = 11;





public const ER_TMPOPEN = 12;





public const ER_ZLIB = 13;





public const ER_MEMORY = 14;





public const ER_CHANGED = 15;





public const ER_COMPNOTSUPP = 16;





public const ER_EOF = 17;





public const ER_INVAL = 18;





public const ER_NOZIP = 19;





public const ER_INTERNAL = 20;





public const ER_INCONS = 21;





public const ER_REMOVE = 22;





public const ER_DELETED = 23;






public const EM_NONE = 0;






public const EM_AES_128 = 257;






public const EM_AES_192 = 258;






public const EM_AES_256 = 259;






public const RDONLY = 16;






public const FL_ENC_GUESS = 0;






public const FL_ENC_RAW = 64;






public const FL_ENC_STRICT = 128;






public const FL_ENC_UTF_8 = 2048;






public const FL_ENC_CP437 = 4096;






public const CM_LZMA2 = 33;






public const CM_XZ = 95;






public const ER_ENCRNOTSUPP = 24;






public const ER_RDONLY = 25;






public const ER_NOPASSWD = 26;






public const ER_WRONGPASSWD = 27;






public const ER_OPNOTSUPP = 28;






public const ER_INUSE = 29;






public const ER_TELL = 30;






public const ER_COMPRESSED_DATA = 31;






public const ER_CANCELLED = 32;





public const OPSYS_DOS = 0;





public const OPSYS_AMIGA = 1;





public const OPSYS_OPENVMS = 2;





public const OPSYS_UNIX = 3;





public const OPSYS_VM_CMS = 4;





public const OPSYS_ATARI_ST = 5;





public const OPSYS_OS_2 = 6;





public const OPSYS_MACINTOSH = 7;





public const OPSYS_Z_SYSTEM = 8;

/**
@removed


*/
public const OPSYS_Z_CPM = 9;





public const OPSYS_WINDOWS_NTFS = 10;





public const OPSYS_MVS = 11;





public const OPSYS_VSE = 12;





public const OPSYS_ACORN_RISC = 13;





public const OPSYS_VFAT = 14;





public const OPSYS_ALTERNATE_MVS = 15;





public const OPSYS_BEOS = 16;





public const OPSYS_TANDEM = 17;





public const OPSYS_OS_400 = 18;





public const OPSYS_OS_X = 19;




public const OPSYS_CPM = 9;





public const OPSYS_DEFAULT = 3;





#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $status;





#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $statusSys;





#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $numFiles;





#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $filename;





#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $comment;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $lastId;












































































public function open(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filename,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}







public function close() {}








public function count() {}







public function getStatusString() {}











public function addEmptyDir(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $dirname,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags
) {}















public function addFromString(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $content,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = 8192
) {}




















public function addFile(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filepath,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $entryname = null,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $start = 0,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $length = 0,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = 8192
) {}

























public function addGlob(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $pattern,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = 0,
array $options = []
) {}
















public function addPattern(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $pattern,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $path = '.',
array $options = []
) {}













public function renameIndex(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $new_name
) {}













public function renameName(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $new_name
) {}










public function setArchiveComment(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $comment) {}











public function getArchiveComment(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null) {}













public function setCommentIndex(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $comment
) {}













public function setCommentName(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $comment
) {}










public function setCompressionIndex(int $index, int $method, int $compflags = 0) {}










public function setCompressionName(string $name, int $method, int $compflags = 0) {}










public function setEncryptionIndex(int $index, int $method, ?string $password = null) {}










public function setEncryptionName(string $name, int $method, ?string $password = null) {}






public function setPassword(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $password) {}














public function getCommentIndex(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}














public function getCommentName(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}










public function deleteIndex(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index) {}










public function deleteName(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name) {}

















public function statName(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}















public function statIndex(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}















public function locateName(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}














public function getNameIndex(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}







public function unchangeArchive() {}







public function unchangeAll() {}










public function unchangeIndex(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index) {}










public function unchangeName(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name) {}














public function extractTo(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $pathto,
#[LanguageLevelTypeAware(['8.0' => 'array|string|null'], default: '')] $files = null
) {}



















public function getFromName(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $len = 0,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}





















public function getFromIndex(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $len = 0,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}










public function getStream(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name) {}










public function setExternalAttributesName(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $opsys,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $attr,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}










public function getExternalAttributesName(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] &$opsys,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] &$attr,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}










public function setExternalAttributesIndex(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $opsys,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $attr,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}










public function getExternalAttributesIndex(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] &$opsys,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] &$attr,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}

public static function isEncryptionMethodSupported(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $method,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $enc = true
) {}

public static function isCompressionMethodSupported(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $method,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $enc = true
) {}

public function registerCancelCallback(#[LanguageLevelTypeAware(['8.0' => 'callable'], default: '')] $callback) {}

public function registerProgressCallback(
#[LanguageLevelTypeAware(['8.0' => 'float'], default: '')] $rate,
#[LanguageLevelTypeAware(['8.0' => 'callable'], default: '')] $callback
) {}

public function setMtimeName(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $timestamp,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}

public function setMtimeIndex(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $index,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $timestamp,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}

public function replaceFile(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filepath,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $index,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $start = null,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $length = null,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = null
) {}
}














function zip_open(string $filename) {}











function zip_close($zip): void {}














function zip_read($zip) {}




























#[Deprecated(reason: 'This function is deprecated in favor of the Object API', since: "8.0")]
function zip_entry_open($zip_dp, $zip_entry, string $mode = 'rb'): bool {}











function zip_entry_close($zip_entry): bool {}

















function zip_entry_read($zip_entry, int $len = 1024): string|false {}











function zip_entry_filesize($zip_entry): int|false {}











function zip_entry_name($zip_entry): string|false {}











function zip_entry_compressedsize($zip_entry): int|false {}











function zip_entry_compressionmethod($zip_entry): string|false {}


