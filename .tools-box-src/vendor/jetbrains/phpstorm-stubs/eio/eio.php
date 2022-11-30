<?php

const EIO_DEBUG = 0;
const EIO_SEEK_SET = 0;
const EIO_SEEK_CUR = 0;
const EIO_SEEK_END = 0;
const EIO_PRI_MIN = 0;
const EIO_PRI_DEFAULT = 0;
const EIO_PRI_MAX = 0;
const EIO_READDIR_DENTS = 0;
const EIO_READDIR_DIRS_FIRST = 0;
const EIO_READDIR_STAT_ORDER = 0;
const EIO_READDIR_FOUND_UNKNOWN = 0;
const EIO_DT_UNKNOWN = 0;
const EIO_DT_FIFO = 0;
const EIO_DT_CHR = 0;
const EIO_DT_MPC = 0;
const EIO_DT_DIR = 0;
const EIO_DT_NAM = 0;
const EIO_DT_BLK = 0;
const EIO_DT_MPB = 0;
const EIO_DT_REG = 0;
const EIO_DT_NWK = 0;
const EIO_DT_CMP = 0;
const EIO_DT_LNK = 0;
const EIO_DT_SOCK = 0;
const EIO_DT_DOOR = 0;
const EIO_DT_WHT = 0;
const EIO_DT_MAX = 0;
const EIO_O_RDONLY = 0;
const EIO_O_WRONLY = 0;
const EIO_O_RDWR = 0;
const EIO_O_NONBLOCK = 0;
const EIO_O_APPEND = 0;
const EIO_O_CREAT = 0;
const EIO_O_TRUNC = 0;
const EIO_O_EXCL = 0;
const EIO_O_FSYNC = 0;
const EIO_S_IRUSR = 0;
const EIO_S_IWUSR = 0;
const EIO_S_IXUSR = 0;
const EIO_S_IRGRP = 0;
const EIO_S_IWGRP = 0;
const EIO_S_IXGRP = 0;
const EIO_S_IROTH = 0;
const EIO_S_IWOTH = 0;
const EIO_S_IXOTH = 0;
const EIO_S_IFREG = 0;
const EIO_S_IFCHR = 0;
const EIO_S_IFBLK = 0;
const EIO_S_IFIFO = 0;
const EIO_S_IFSOCK = 0;
const EIO_SYNC_FILE_RANGE_WAIT_BEFORE = 0;
const EIO_SYNC_FILE_RANGE_WRITE = 0;
const EIO_SYNC_FILE_RANGE_WAIT_AFTER = 0;
const EIO_FALLOC_FL_KEEP_SIZE = 0;






function eio_event_loop(): bool {}






function eio_poll(): int {}












function eio_open(string $path, int $flags, int $mode, int $pri, mixed $callback, mixed $data = null) {}











function eio_truncate(string $path, int $offset = 0, int $pri = 0, mixed $callback = null, mixed $data = null) {}











function eio_mkdir(string $path, int $mode, int $pri = 0, mixed $callback = null, mixed $data = null) {}










function eio_rmdir(string $path, int $pri = 0, mixed $callback = null, mixed $data = null) {}










function eio_unlink(string $path, int $pri = 0, mixed $callback = null, mixed $data = null) {}












function eio_utime(string $path, float $atime, float $mtime, int $pri = 0, mixed $callback = null, mixed $data = null) {}












function eio_mknod(string $path, int $mode, int $dev, int $pri = 0, mixed $callback = null, mixed $data = null) {}











function eio_link(string $path, string $new_path, int $pri = 0, mixed $callback = null, mixed $data = null) {}











function eio_symlink(string $path, string $new_path, int $pri = 0, mixed $callback = null, mixed $data = null) {}











function eio_rename(string $path, string $new_path, int $pri = 0, mixed $callback = null, mixed $data = null) {}










function eio_close(mixed $fd, int $pri = 0, mixed $callback = null, mixed $data = null) {}









function eio_sync(int $pri = 0, mixed $callback = null, mixed $data = null) {}










function eio_fsync(mixed $fd, int $pri = 0, mixed $callback = null, mixed $data = null) {}










function eio_fdatasync(mixed $fd, int $pri = 0, mixed $callback = null, mixed $data = null) {}












function eio_futime(mixed $fd, float $atime, float $mtime, int $pri = 0, mixed $callback = null, mixed $data = null) {}











function eio_ftruncate(mixed $fd, int $offset = 0, int $pri = 0, mixed $callback = null, mixed $data = null) {}











function eio_chmod(string $path, int $mode, int $pri = 0, mixed $callback = null, mixed $data = null) {}











function eio_fchmod(mixed $fd, int $mode, int $pri = 0, mixed $callback = null, mixed $data = null) {}












function eio_chown(string $path, int $uid, int $gid = -1, int $pri = 0, mixed $callback = null, ?mixed $data = null) {}












function eio_fchown(mixed $fd, int $uid, int $gid = -1, int $pri = 0, mixed $callback = null, mixed $data = null) {}











function eio_dup2(mixed $fd, mixed $fd2, int $pri = 0, mixed $callback = null, mixed $data = null) {}












function eio_read(mixed $fd, int $length, int $offset, int $pri, mixed $callback, mixed $data = null) {}













function eio_write(mixed $fd, mixed $str, int $length = 0, int $offset = 0, int $pri = 0, mixed $callback = null, mixed $data = null) {}










function eio_readlink(string $path, int $pri, mixed $callback, mixed $data = null) {}










function eio_realpath(string $path, int $pri, mixed $callback, mixed $data = null) {}










function eio_stat(string $path, int $pri, mixed $callback, mixed $data = null) {}










function eio_lstat(string $path, int $pri, mixed $callback, mixed $data = null) {}










function eio_fstat(mixed $fd, int $pri, mixed $callback, mixed $data = null) {}










function eio_statvfs(string $path, int $pri, mixed $callback, mixed $data = null) {}










function eio_fstatvfs(mixed $fd, int $pri, mixed $callback, mixed $data = null) {}











function eio_readdir(string $path, int $flags, int $pri, mixed $callback, mixed $data = null) {}













function eio_sendfile(mixed $out_fd, mixed $in_fd, int $offset, int $length, int $pri, mixed $callback, mixed $data = null) {}












function eio_readahead(mixed $fd, int $offset, int $length, int $pri = EIO_PRI_DEFAULT, mixed $callback = null, mixed $data = null) {}












function eio_seek(mixed $fd, int $offset, int $length, int $pri = EIO_PRI_DEFAULT, mixed $callback = null, mixed $data = null) {}










function eio_syncfs(mixed $fd, int $pri = EIO_PRI_DEFAULT, mixed $callback = null, mixed $data = null) {}













function eio_sync_file_range(mixed $fd, int $offset, int $nbytes, int $flags, int $pri = EIO_PRI_DEFAULT, mixed $callback = null, mixed $data = null) {}













function eio_fallocate(mixed $fd, int $mode, int $offset, int $length, int $pri = EIO_PRI_DEFAULT, mixed $callback = null, mixed $data = null) {}










function eio_custom(mixed $execute, int $pri, mixed $callback, mixed $data = null) {}










function eio_busy(int $delay, int $pri = EIO_PRI_DEFAULT, mixed $callback = null, mixed $data = null) {}









function eio_nop(int $pri = EIO_PRI_DEFAULT, mixed $callback = null, mixed $data = null) {}






function eio_cancel($req): void {}








function eio_grp(mixed $callback, mixed $data = null) {}







function eio_grp_add($grp, $req): void {}







function eio_grp_limit($grp, int $limit): void {}






function eio_grp_cancel($grp): void {}






function eio_set_max_poll_time(float $nseconds): void {}






function eio_set_max_poll_reqs(int $value): void {}






function eio_set_min_parallel(int $value): void {}






function eio_set_max_parallel(int $value): void {}






function eio_set_max_idle(int $value): void {}






function eio_nthreads(): int {}






function eio_nreqs(): int {}






function eio_nready(): int {}






function eio_npending(): int {}






function eio_get_event_stream() {}







function eio_get_last_error($req): string {}
