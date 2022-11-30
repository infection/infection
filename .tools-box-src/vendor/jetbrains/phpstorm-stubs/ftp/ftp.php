<?php


use JetBrains\PhpStorm\ExpectedValues as EV;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;










function ftp_append(
#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp,
string $remote_filename,
string $local_filename,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.2')]
int $mode,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '7.3')]
int $mode = FTP_BINARY
): bool {}








function ftp_mlsd(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, string $directory): array|false {}




















#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection|false'], default: 'resource|false')]
function ftp_connect(string $hostname, int $port = 21, int $timeout = 90) {}




















#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection|false'], default: 'resource|false')]
function ftp_ssl_connect(string $hostname, int $port = 21, int $timeout = 90) {}
















function ftp_login(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, string $username, string $password): bool {}









function ftp_pwd(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp): string|false {}









function ftp_cdup(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp): bool {}













function ftp_chdir(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, string $directory): bool {}













function ftp_exec(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, string $command): bool {}














#[LanguageLevelTypeAware(['8.0' => 'array|null'], default: 'array')]
function ftp_raw(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, string $command) {}












function ftp_mkdir(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, string $directory): string|false {}













function ftp_rmdir(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, string $directory): bool {}















function ftp_chmod(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, int $permissions, string $filename): int|false {}
















function ftp_alloc(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, int $size, &$response): bool {}
















function ftp_nlist(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, string $directory): array|false {}





















function ftp_rawlist(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, string $directory, bool $recursive = false): array|false {}









function ftp_systype(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp): string|false {}












function ftp_pasv(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, bool $enable): bool {}





















function ftp_get(
#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp,
string $local_filename,
string $remote_filename,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.2')]
int $mode,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '7.3')]
int $mode = FTP_BINARY,
int $offset = 0
): bool {}





















function ftp_fget(
#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp,
$stream,
string $remote_filename,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.2')]
int $mode,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '7.3')]
int $mode = FTP_BINARY,
int $offset = 0
): bool {}



















function ftp_put(
#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp,
string $remote_filename,
string $local_filename,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.2')]
int $mode,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '7.3')]
int $mode = FTP_BINARY,
int $offset = 0
): bool {}



















function ftp_fput(
#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp,
string $remote_filename,
$stream,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.2')]
int $mode,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '7.3')]
int $mode = FTP_BINARY,
int $offset = 0
): bool {}












function ftp_size(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, string $filename): int {}













function ftp_mdtm(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, string $filename): int {}















function ftp_rename(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, string $from, string $to): bool {}












function ftp_delete(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, string $filename): bool {}













function ftp_site(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, string $command): bool {}









function ftp_close(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp): bool {}







































function ftp_set_option(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, #[EV(flags: [FTP_TIMEOUT_SEC, FTP_AUTOSEEK, FTP_USEPASVADDRESS])] int $option, $value): bool {}





























function ftp_get_option(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp, #[EV(flags: [FTP_TIMEOUT_SEC, FTP_AUTOSEEK])] int $option): int|bool {}




















#[EV([FTP_FAILED, FTP_FINISHED, FTP_MOREDATA])]
function ftp_nb_fget(
#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp,
$stream,
string $remote_filename,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.2')]
int $mode,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '7.3')]
int $mode = FTP_BINARY,
int $offset = 0
): int {}




















#[EV([FTP_FAILED, FTP_FINISHED, FTP_MOREDATA])]
function ftp_nb_get(
#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp,
string $local_filename,
string $remote_filename,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.2')]
int $mode,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '7.3')]
int $mode = FTP_BINARY,
int $offset = 0
): int {}










#[EV([FTP_FAILED, FTP_FINISHED, FTP_MOREDATA])]
function ftp_nb_continue(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp): int {}




















#[EV([FTP_FAILED, FTP_FINISHED, FTP_MOREDATA])]
function ftp_nb_put(
#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp,
string $remote_filename,
string $local_filename,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.2')]
int $mode,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '7.3')]
int $mode = FTP_BINARY,
int $offset = 0
): int|false {}




















#[EV([FTP_FAILED, FTP_FINISHED, FTP_MOREDATA])]
function ftp_nb_fput(
#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp,
string $remote_filename,
$stream,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.2')]
int $mode,
#[EV([FTP_ASCII, FTP_BINARY])]
#[PhpStormStubsElementAvailable(from: '7.3')]
int $mode = FTP_BINARY,
int $offset = 0
): int {}







function ftp_quit(#[LanguageLevelTypeAware(['8.1' => 'FTP\Connection'], default: 'resource')] $ftp): bool {}





define('FTP_ASCII', 1);





define('FTP_TEXT', 1);





define('FTP_BINARY', 2);





define('FTP_IMAGE', 2);








define('FTP_AUTORESUME', -1);







define('FTP_TIMEOUT_SEC', 0);







define('FTP_AUTOSEEK', 1);

define('FTP_USEPASVADDRESS', 2);







define('FTP_FAILED', 0);







define('FTP_FINISHED', 1);







define('FTP_MOREDATA', 2);


