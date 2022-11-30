<?php















function inotify_add_watch($inotify_instance, $pathname, $mask) {}








function inotify_init() {}













function inotify_queue_len($inotify_instance) {}




















function inotify_read($inotify_instance) {}












function inotify_rm_watch($inotify_instance, $mask) {}




const IN_ACCESS = 1;



const IN_MODIFY = 2;



const IN_ATTRIB = 4;



const IN_CLOSE_WRITE = 8;



const IN_CLOSE_NOWRITE = 16;



const IN_OPEN = 32;



const IN_MOVED_FROM = 64;



const IN_MOVED_TO = 128;



const IN_CREATE = 256;



const IN_DELETE = 512;



const IN_DELETE_SELF = 1024;



const IN_MOVE_SELF = 2048;



const IN_UNMOUNT = 8192;



const IN_Q_OVERFLOW = 16384;



const IN_IGNORED = 32768;



const IN_CLOSE = 24;



const IN_MOVE = 192;



const IN_ALL_EVENTS = 4095;



const IN_ONLYDIR = 16777216;



const IN_DONT_FOLLOW = 33554432;



const IN_MASK_ADD = 536870912;



const IN_ISDIR = 1073741824;



const IN_ONESHOT = 2147483648;


