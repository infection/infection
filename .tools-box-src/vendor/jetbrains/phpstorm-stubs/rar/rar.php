<?php









final class RarArchive implements Traversable
{


















public static function open($filename, $password = null, ?callable $volume_callback = null) {}








public function close() {}








public function getComment() {}






public function getEntries() {}












public function getEntry($entryname) {}













public function isBroken() {}










public function isSolid() {}














public function setAllowBroken($allow_broken) {}














public function __toString() {}
}





final class RarEntry
{




public const HOST_MSDOS = 0;





public const HOST_OS2 = 1;





public const HOST_WIN32 = 2;





public const HOST_UNIX = 3;




public const HOST_MACOS = 4;





public const HOST_BEOS = 5;





public const ATTRIBUTE_WIN_READONLY = 1;





public const ATTRIBUTE_WIN_HIDDEN = 2;





public const ATTRIBUTE_WIN_SYSTEM = 4;






public const ATTRIBUTE_WIN_DIRECTORY = 16;





public const ATTRIBUTE_WIN_ARCHIVE = 32;





public const ATTRIBUTE_WIN_DEVICE = 64;






public const ATTRIBUTE_WIN_NORMAL = 128;





public const ATTRIBUTE_WIN_TEMPORARY = 256;





public const ATTRIBUTE_WIN_SPARSE_FILE = 512;






public const ATTRIBUTE_WIN_REPARSE_POINT = 1024;





public const ATTRIBUTE_WIN_COMPRESSED = 2048;





public const ATTRIBUTE_WIN_OFFLINE = 4096;





public const ATTRIBUTE_WIN_NOT_CONTENT_INDEXED = 8192;





public const ATTRIBUTE_WIN_ENCRYPTED = 16384;





public const ATTRIBUTE_WIN_VIRTUAL = 65536;





public const ATTRIBUTE_UNIX_WORLD_EXECUTE = 1;





public const ATTRIBUTE_UNIX_WORLD_WRITE = 2;





public const ATTRIBUTE_UNIX_WORLD_READ = 4;





public const ATTRIBUTE_UNIX_GROUP_EXECUTE = 8;





public const ATTRIBUTE_UNIX_GROUP_WRITE = 16;





public const ATTRIBUTE_UNIX_GROUP_READ = 32;





public const ATTRIBUTE_UNIX_OWNER_EXECUTE = 64;





public const ATTRIBUTE_UNIX_OWNER_WRITE = 128;





public const ATTRIBUTE_UNIX_OWNER_READ = 256;





public const ATTRIBUTE_UNIX_STICKY = 512;





public const ATTRIBUTE_UNIX_SETGID = 1024;





public const ATTRIBUTE_UNIX_SETUID = 2048;









public const ATTRIBUTE_UNIX_FINAL_QUARTET = 61440;





public const ATTRIBUTE_UNIX_FIFO = 4096;






public const ATTRIBUTE_UNIX_CHAR_DEV = 8192;









public const ATTRIBUTE_UNIX_DIRECTORY = 16384;






public const ATTRIBUTE_UNIX_BLOCK_DEV = 24576;







public const ATTRIBUTE_UNIX_REGULAR_FILE = 32768;






public const ATTRIBUTE_UNIX_SYM_LINK = 40960;






public const ATTRIBUTE_UNIX_SOCKET = 49152;

























public function extract($dir, $filepath = "", $password = null, $extended_data = false) {}










public function getAttr() {}










public function getCrc() {}








public function getFileTime() {}










public function getHostOs() {}










public function getMethod() {}










public function getName() {}








public function getPackedSize() {}


















public function getStream($password = '') {}






public function getUnpackedSize() {}











public function getVersion() {}








public function isDirectory() {}








public function isEncrypted() {}












public function __toString() {}
}























final class RarException extends Exception
{







public static function isUsingExceptions() {}








public static function setUsingExceptions($using_exceptions) {}
}
