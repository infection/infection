<?php


use JetBrains\PhpStorm\Pure;












function posix_kill(int $process_id, int $signal): bool {}






#[Pure]
function posix_getpid(): int {}






#[Pure]
function posix_getppid(): int {}






#[Pure]
function posix_getuid(): int {}









function posix_setuid(int $user_id): bool {}






#[Pure]
function posix_geteuid(): int {}









function posix_seteuid(int $user_id): bool {}














function posix_setrlimit(int $resource, int $soft_limit, int $hard_limit): bool {}





#[Pure]
function posix_getgid(): int {}









function posix_setgid(int $group_id): bool {}






#[Pure]
function posix_getegid(): int {}









function posix_setegid(int $group_id): bool {}







#[Pure]
function posix_getgroups(): array|false {}






#[Pure]
function posix_getlogin(): string|false {}






#[Pure]
function posix_getpgrp(): int {}






function posix_setsid(): int {}












function posix_setpgid(int $process_id, int $process_group_id): bool {}









#[Pure]
function posix_getpgid(int $process_id): int|false {}












#[Pure]
function posix_getsid(int $process_id): int|false {}



















#[Pure]
function posix_uname(): array|false {}













#[Pure]
function posix_times(): array|false {}








#[Pure]
function posix_ctermid(): string|false {}










#[Pure]
function posix_ttyname($file_descriptor): string|false {}













#[Pure]
function posix_isatty($file_descriptor): bool {}








#[Pure(true)]
function posix_getcwd(): string|false {}
















function posix_mkfifo(string $filename, int $permissions): bool {}























function posix_mknod(string $filename, int $flags, int $major = 0, int $minor = 0): bool {}





















function posix_access(string $filename, int $flags = POSIX_F_OK): bool {}













































#[Pure]
function posix_getgrnam(string $name): array|false {}















































#[Pure]
function posix_getgrgid(int $group_id): array|false {}











































































#[Pure]
function posix_getpwnam(string $username): array|false {}










































































#[Pure]
function posix_getpwuid(int $user_id): array|false {}



















































































#[Pure]
function posix_getrlimit(): array|false {}







#[Pure(true)]
function posix_get_last_error(): int {}





#[Pure(true)]
function posix_errno(): int {}











#[Pure]
function posix_strerror(int $error_code): string {}












#[Pure]
function posix_initgroups(string $username, int $group_id): bool {}





define('POSIX_F_OK', 0);





define('POSIX_X_OK', 1);





define('POSIX_W_OK', 2);





define('POSIX_R_OK', 4);





define('POSIX_S_IFREG', 32768);





define('POSIX_S_IFCHR', 8192);





define('POSIX_S_IFBLK', 24576);





define('POSIX_S_IFIFO', 4096);





define('POSIX_S_IFSOCK', 49152);





define('POSIX_RLIMIT_AS', 5);




define('POSIX_RLIMIT_CORE', 4);








define('POSIX_RLIMIT_CPU', 0);







define('POSIX_RLIMIT_DATA', 2);





define('POSIX_RLIMIT_FSIZE', 1);






define('POSIX_RLIMIT_LOCKS', 10);







define('POSIX_RLIMIT_MSGQUEUE', 12);





define('POSIX_RLIMIT_NICE', 13);





define('POSIX_RLIMIT_RTPRIO', 14);






define('POSIX_RLIMIT_RTTIME', 15);





define('POSIX_RLIMIT_SIGPENDING', 11);





define('POSIX_RLIMIT_MEMLOCK', 6);





define('POSIX_RLIMIT_NOFILE', 8);






define('POSIX_RLIMIT_NPROC', 7);





define('POSIX_RLIMIT_RSS', 5);





define('POSIX_RLIMIT_STACK', 3);





define('POSIX_RLIMIT_INFINITY', 9223372036854775807);


