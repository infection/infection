<?php



class Svn
{
public const NON_RECURSIVE = 1;
public const DISCOVER_CHANGED_PATHS = 2;
public const OMIT_MESSAGES = 4;
public const STOP_ON_COPY = 8;
public const ALL = 16;
public const SHOW_UPDATES = 32;
public const NO_IGNORE = 64;
public const IGNORE_EXTERNALS = 128;
public const INITIAL = 1;
public const HEAD = -1;
public const BASE = -2;
public const COMMITTED = -3;
public const PREV = -4;
public const UNSPECIFIED = -5;

public static function checkout() {}

public static function cat() {}

public static function ls() {}

public static function log() {}

public static function auth_set_parameter() {}

public static function auth_get_parameter() {}

public static function client_version() {}

public static function config_ensure() {}

public static function diff() {}

public static function cleanup() {}

public static function revert() {}

public static function resolved() {}

public static function commit() {}

public static function lock() {}

public static function unlock() {}

public static function add() {}

public static function status() {}

public static function update() {}

public static function update2() {}

public static function import() {}

public static function info() {}

public static function export() {}

public static function copy() {}

public static function switch() {}

public static function blame() {}

public static function delete() {}

public static function mkdir() {}

public static function move() {}

public static function proplist() {}

public static function propget() {}

public static function propset() {}

public static function prop_delete() {}

public static function revprop_get() {}

public static function revprop_set() {}

public static function revprop_delete() {}

public static function repos_create() {}

public static function repos_recover() {}

public static function repos_hotcopy() {}

public static function repos_open() {}

public static function repos_fs() {}

public static function repos_fs_begin_txn_for_commit() {}

public static function repos_fs_commit_txn() {}
}

class SvnWc
{
public const NONE = 1;
public const UNVERSIONED = 2;
public const NORMAL = 3;
public const ADDED = 4;
public const MISSING = 5;
public const DELETED = 6;
public const REPLACED = 7;
public const MODIFIED = 8;
public const MERGED = 9;
public const CONFLICTED = 10;
public const IGNORED = 11;
public const OBSTRUCTED = 12;
public const EXTERNAL = 13;
public const INCOMPLETE = 14;
}

class SvnWcSchedule
{
public const NORMAL = 0;
public const ADD = 1;
public const DELETE = 2;
public const REPLACE = 3;
}

class SvnNode
{
public const NONE = 0;
public const FILE = 1;
public const DIR = 2;
public const UNKNOWN = 3;
}






















function svn_checkout($repos, $targetpath, $revision = SVN_REVISION_HEAD, $flags = 0) {}















function svn_cat($repos_url, $revision_no = SVN_REVISION_HEAD) {}




























function svn_ls($repos_url, $revision_no = SVN_REVISION_HEAD, $recurse = false, $peg = false) {}



















































































function svn_log($repos_url, $start_revision = null, $end_revision = null, $limit = 0, $flags = SVN_DISCOVER_CHANGED_PATHS|SVN_STOP_ON_COPY) {}















function svn_auth_set_parameter($key, $value) {}












function svn_auth_get_parameter($key) {}







function svn_client_version() {}

function svn_config_ensure() {}





































function svn_diff($path1, $rev1, $path2, $rev2) {}











function svn_cleanup($workingdir) {}













function svn_revert($path, $recursive = false) {}

function svn_resolved() {}































function svn_commit($log, array $targets, $recursive = true) {}

function svn_lock() {}

function svn_unlock() {}




















function svn_add($path, $recursive = true, $force = false) {}































































































function svn_status($path, $flags = 0) {}

















function svn_update($path, $revno = SVN_REVISION_HEAD, $recurse = true) {}

















function svn_import($path, $url, $nonrecursive) {}

function svn_info() {}

















function svn_export($frompath, $topath, $working_copy = true, $revision_no = -1) {}

function svn_copy() {}

function svn_switch() {}















function svn_blame($repository_url, $revision_no = SVN_REVISION_HEAD) {}
















function svn_delete($path, $force = false) {}











function svn_mkdir($path, $log_message = null) {}








function svn_move($src_path, $dst_path, $force = false) {}








function svn_proplist($path, $recurse = false, $revision) {}








function svn_propget($path, $property_name, $recurse = false, $revision) {}
















function svn_repos_create($path, ?array $config = null, ?array $fsconfig = null) {}










function svn_repos_recover($path) {}
















function svn_repos_hotcopy($repospath, $destpath, $cleanlogs) {}










function svn_repos_open($path) {}










function svn_repos_fs($repos) {}



















function svn_repos_fs_begin_txn_for_commit($repos, $rev, $author, $log_msg) {}










function svn_repos_fs_commit_txn($txn) {}













function svn_fs_revision_root($fs, $revnum) {}













function svn_fs_check_path($fsroot, $path) {}
















function svn_fs_revision_prop($fs, $revnum, $propname) {}













function svn_fs_dir_entries($fsroot, $path) {}













function svn_fs_node_created_rev($fsroot, $path) {}










function svn_fs_youngest_rev($fs) {}













function svn_fs_file_contents($fsroot, $path) {}













function svn_fs_file_length($fsroot, $path) {}










function svn_fs_txn_root($txn) {}













function svn_fs_make_file($root, $path) {}













function svn_fs_make_dir($root, $path) {}













function svn_fs_apply_text($root, $path) {}



















function svn_fs_copy($from_root, $from_path, $to_root, $to_path) {}













function svn_fs_delete($root, $path) {}













function svn_fs_begin_txn2($repos, $rev) {}













function svn_fs_is_dir($root, $path) {}













function svn_fs_is_file($root, $path) {}
















function svn_fs_node_prop($fsroot, $path, $propname) {}



















function svn_fs_change_node_prop($root, $path, $name, $value) {}



















function svn_fs_contents_changed($root1, $path1, $root2, $path2) {}



















function svn_fs_props_changed($root1, $path1, $root2, $path2) {}










function svn_fs_abort_txn($txn) {}





define('SVN_AUTH_PARAM_DEFAULT_USERNAME', "svn:auth:username");





define('SVN_AUTH_PARAM_DEFAULT_PASSWORD', "svn:auth:password");
define('SVN_AUTH_PARAM_NON_INTERACTIVE', "svn:auth:non-interactive");
define('SVN_AUTH_PARAM_DONT_STORE_PASSWORDS', "svn:auth:dont-store-passwords");
define('SVN_AUTH_PARAM_NO_AUTH_CACHE', "svn:auth:no-auth-cache");
define('SVN_AUTH_PARAM_SSL_SERVER_FAILURES', "svn:auth:ssl:failures");
define('SVN_AUTH_PARAM_SSL_SERVER_CERT_INFO', "svn:auth:ssl:cert-info");
define('SVN_AUTH_PARAM_CONFIG', "svn:auth:config-category-servers");
define('SVN_AUTH_PARAM_SERVER_GROUP', "svn:auth:server-group");
define('SVN_AUTH_PARAM_CONFIG_DIR', "svn:auth:config-dir");





define('PHP_SVN_AUTH_PARAM_IGNORE_SSL_VERIFY_ERRORS', "php:svn:auth:ignore-ssl-verify-errors");





define('SVN_FS_CONFIG_FS_TYPE', "fs-type");





define('SVN_FS_TYPE_BDB', "bdb");





define('SVN_FS_TYPE_FSFS', "fsfs");





define('SVN_PROP_REVISION_DATE', "svn:date");





define('SVN_PROP_REVISION_ORIG_DATE', "svn:original-date");





define('SVN_PROP_REVISION_AUTHOR', "svn:author");





define('SVN_PROP_REVISION_LOG', "svn:log");
define('SVN_REVISION_INITIAL', 1);





define('SVN_REVISION_HEAD', -1);
define('SVN_REVISION_BASE', -2);
define('SVN_REVISION_COMMITTED', -3);
define('SVN_REVISION_PREV', -4);
define('SVN_REVISION_UNSPECIFIED', -5);
define('SVN_NON_RECURSIVE', 1);
define('SVN_DISCOVER_CHANGED_PATHS', 2);
define('SVN_OMIT_MESSAGES', 4);
define('SVN_STOP_ON_COPY', 8);
define('SVN_ALL', 16);
define('SVN_SHOW_UPDATES', 32);
define('SVN_NO_IGNORE', 64);





define('SVN_WC_STATUS_NONE', 1);





define('SVN_WC_STATUS_UNVERSIONED', 2);





define('SVN_WC_STATUS_NORMAL', 3);





define('SVN_WC_STATUS_ADDED', 4);





define('SVN_WC_STATUS_MISSING', 5);





define('SVN_WC_STATUS_DELETED', 6);





define('SVN_WC_STATUS_REPLACED', 7);





define('SVN_WC_STATUS_MODIFIED', 8);





define('SVN_WC_STATUS_MERGED', 9);





define('SVN_WC_STATUS_CONFLICTED', 10);





define('SVN_WC_STATUS_IGNORED', 11);





define('SVN_WC_STATUS_OBSTRUCTED', 12);





define('SVN_WC_STATUS_EXTERNAL', 13);





define('SVN_WC_STATUS_INCOMPLETE', 14);





define('SVN_NODE_NONE', 0);





define('SVN_NODE_FILE', 1);





define('SVN_NODE_DIR', 2);





define('SVN_NODE_UNKNOWN', 3);
define('SVN_WC_SCHEDULE_NORMAL', 0);
define('SVN_WC_SCHEDULE_ADD', 1);
define('SVN_WC_SCHEDULE_DELETE', 2);
define('SVN_WC_SCHEDULE_REPLACE', 3);
