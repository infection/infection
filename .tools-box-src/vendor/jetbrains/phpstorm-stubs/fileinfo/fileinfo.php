<?php



use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;

class finfo
{




public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $magic_database
) {}





#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')]
public function finfo($options, $arg) {}











public function set_flags(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags) {}


















#[Pure]
#[TentativeType]
public function file(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filename,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = FILEINFO_NONE,
$context = null
): string|false {}
















#[Pure]
#[TentativeType]
public function buffer(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $string,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = FILEINFO_NONE,
$context = null
): string|false {}
}



















#[LanguageLevelTypeAware(['8.1' => 'finfo|false'], default: 'resource|false')]
function finfo_open(int $flags = 0, ?string $magic_database = null) {}










function finfo_close(#[LanguageLevelTypeAware(['8.1' => 'finfo'], default: 'resource')] $finfo): bool {}














function finfo_set_flags(#[LanguageLevelTypeAware(['8.1' => 'finfo'], default: 'resource')] $finfo, int $flags): bool {}





















function finfo_file(#[LanguageLevelTypeAware(['8.1' => 'finfo'], default: 'resource')] $finfo, string $filename, int $flags = 0, $context): string|false {}

















function finfo_buffer(#[LanguageLevelTypeAware(['8.1' => 'finfo'], default: 'resource')] $finfo, string $string, int $flags = FILEINFO_NONE, $context): string|false {}










function mime_content_type($filename): string|false {}





define('FILEINFO_NONE', 0);





define('FILEINFO_SYMLINK', 2);





define('FILEINFO_MIME', 1040);





define('FILEINFO_MIME_TYPE', 16);





define('FILEINFO_MIME_ENCODING', 1024);





define('FILEINFO_DEVICES', 8);





define('FILEINFO_CONTINUE', 32);





define('FILEINFO_PRESERVE_ATIME', 128);






define('FILEINFO_RAW', 256);







define('FILEINFO_EXTENSION', 2097152);




define('FILEINFO_APPLE', 2048);


