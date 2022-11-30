<?php
declare(strict_types=1);








const VIR_DOMAIN_METADATA_DESCRIPTION = 0;
const VIR_DOMAIN_METADATA_TITLE = 1;
const VIR_DOMAIN_METADATA_ELEMENT = 2;
const VIR_DOMAIN_AFFECT_CURRENT = VIR_DOMAIN_AFFECT_CURRENT;
const VIR_DOMAIN_AFFECT_LIVE = 1;
const VIR_DOMAIN_AFFECT_CONFIG = 2;

const VIR_DOMAIN_STATS_STATE = 1;
const VIR_DOMAIN_STATS_CPU_TOTAL = 2;
const VIR_DOMAIN_STATS_BALLOON = 4;
const VIR_DOMAIN_STATS_VCPU = 8;
const VIR_DOMAIN_STATS_INTERFACE = 16;
const VIR_DOMAIN_STATS_BLOCK = 32;


const VIR_DOMAIN_XML_SECURE = 1;
const VIR_DOMAIN_XML_INACTIVE = 2;
const VIR_DOMAIN_XML_UPDATE_CPU = 4;
const VIR_DOMAIN_XML_MIGRATABLE = 8;

const VIR_NODE_CPU_STATS_ALL_CPUS = -1;


const VIR_DOMAIN_NOSTATE = 0;
const VIR_DOMAIN_RUNNING = 1;
const VIR_DOMAIN_BLOCKED = 2;
const VIR_DOMAIN_PAUSED = 3;
const VIR_DOMAIN_SHUTDOWN = 4;
const VIR_DOMAIN_SHUTOFF = 5;
const VIR_DOMAIN_CRASHED = 6;
const VIR_DOMAIN_PMSUSPENDED = 7;


const VIR_STORAGE_VOL_RESIZE_ALLOCATE = 1;
const VIR_STORAGE_VOL_RESIZE_DELTA = 2;
const VIR_STORAGE_VOL_RESIZE_SHRINK = 4;
const VIR_STORAGE_VOL_CREATE_PREALLOC_METADATA = 1;
const VIR_STORAGE_VOL_CREATE_REFLINK = 2;


const VIR_DOMAIN_VCPU_CONFIG = VIR_DOMAIN_AFFECT_CONFIG;
const VIR_DOMAIN_VCPU_CURRENT = VIR_DOMAIN_AFFECT_CURRENT;
const VIR_DOMAIN_VCPU_LIVE = VIR_DOMAIN_AFFECT_LIVE;
const VIR_DOMAIN_VCPU_MAXIMUM = 4;
const VIR_DOMAIN_VCPU_GUEST = 8;


const VIR_SNAPSHOT_DELETE_CHILDREN = 1;
const VIR_SNAPSHOT_DELETE_METADATA_ONLY = 2;
const VIR_SNAPSHOT_DELETE_CHILDREN_ONLY = 4;
const VIR_SNAPSHOT_CREATE_REDEFINE = 1;
const VIR_SNAPSHOT_CREATE_CURRENT = 2;
const VIR_SNAPSHOT_CREATE_NO_METADATA = 4;
const VIR_SNAPSHOT_CREATE_HALT = 8;
const VIR_SNAPSHOT_CREATE_DISK_ONLY = 16;
const VIR_SNAPSHOT_CREATE_REUSE_EXT = 32;
const VIR_SNAPSHOT_CREATE_QUIESCE = 64;
const VIR_SNAPSHOT_CREATE_ATOMIC = 128;
const VIR_SNAPSHOT_CREATE_LIVE = 256;
const VIR_SNAPSHOT_LIST_DESCENDANTS = 1;
const VIR_SNAPSHOT_LIST_ROOTS = 1;
const VIR_SNAPSHOT_LIST_METADATA = 2;
const VIR_SNAPSHOT_LIST_LEAVES = 4;
const VIR_SNAPSHOT_LIST_NO_LEAVES = 8;
const VIR_SNAPSHOT_LIST_NO_METADATA = 16;
const VIR_SNAPSHOT_LIST_INACTIVE = 32;
const VIR_SNAPSHOT_LIST_ACTIVE = 64;
const VIR_SNAPSHOT_LIST_DISK_ONLY = 128;
const VIR_SNAPSHOT_LIST_INTERNAL = 256;
const VIR_SNAPSHOT_LIST_EXTERNAL = 512;
const VIR_SNAPSHOT_REVERT_RUNNING = 1;
const VIR_SNAPSHOT_REVERT_PAUSED = 2;
const VIR_SNAPSHOT_REVERT_FORCE = 4;


const VIR_DOMAIN_NONE = 0;
const VIR_DOMAIN_START_PAUSED = 1;
const VIR_DOMAIN_START_AUTODESTROY = 2;
const VIR_DOMAIN_START_BYPASS_CACHE = 4;
const VIR_DOMAIN_START_FORCE_BOOT = 8;
const VIR_DOMAIN_START_VALIDATE = 16;


const VIR_MEMORY_VIRTUAL = 1;
const VIR_MEMORY_PHYSICAL = 2;


const VIR_VERSION_BINDING = 1;
const VIR_VERSION_LIBVIRT = 2;


const VIR_NETWORKS_ACTIVE = 1;
const VIR_NETWORKS_INACTIVE = 2;
const VIR_NETWORKS_ALL = VIR_NETWORKS_ACTIVE|VIR_NETWORKS_INACTIVE;
const VIR_CONNECT_LIST_NETWORKS_INACTIVE = 1;
const VIR_CONNECT_LIST_NETWORKS_ACTIVE = 2;
const VIR_CONNECT_LIST_NETWORKS_PERSISTENT = 4;
const VIR_CONNECT_LIST_NETWORKS_TRANSIENT = 8;
const VIR_CONNECT_LIST_NETWORKS_AUTOSTART = 16;
const VIR_CONNECT_LIST_NETWORKS_NO_AUTOSTART = 32;


const VIR_CRED_USERNAME = 1;
const VIR_CRED_AUTHNAME = 2;

const VIR_CRED_LANGUAGE = 3;

const VIR_CRED_CNONCE = 4;

const VIR_CRED_PASSPHRASE = 5;

const VIR_CRED_ECHOPROMPT = 6;

const VIR_CRED_NOECHOPROMPT = 7;

const VIR_CRED_REALM = 8;

const VIR_CRED_EXTERNAL = 9;



const VIR_DOMAIN_MEMORY_STAT_SWAP_IN = 0;



const VIR_DOMAIN_MEMORY_STAT_SWAP_OUT = 1;
const VIR_DOMAIN_MEMORY_STAT_MAJOR_FAULT = 2;


const VIR_DOMAIN_MEMORY_STAT_MINOR_FAULT = 3;



const VIR_DOMAIN_MEMORY_STAT_UNUSED = 4;


const VIR_DOMAIN_MEMORY_STAT_AVAILABLE = 5;

const VIR_DOMAIN_MEMORY_STAT_ACTUAL_BALLOON = 6;

const VIR_DOMAIN_MEMORY_STAT_RSS = 7;


const VIR_DOMAIN_MEMORY_STAT_NR = 8;


const VIR_DOMAIN_JOB_NONE = 0;

const VIR_DOMAIN_JOB_BOUNDED = 1;

const VIR_DOMAIN_JOB_UNBOUNDED = 2;

const VIR_DOMAIN_JOB_COMPLETED = 3;

const VIR_DOMAIN_JOB_FAILED = 4;

const VIR_DOMAIN_JOB_CANCELLED = 5;

const VIR_DOMAIN_BLOCK_COMMIT_SHALLOW = 1;
const VIR_DOMAIN_BLOCK_COMMIT_DELETE = 2;
const VIR_DOMAIN_BLOCK_COMMIT_ACTIVE = 4;
const VIR_DOMAIN_BLOCK_COMMIT_RELATIVE = 8;
const VIR_DOMAIN_BLOCK_COMMIT_BANDWIDTH_BYTES = 16;

const VIR_DOMAIN_BLOCK_COPY_SHALLOW = 1;
const VIR_DOMAIN_BLOCK_COPY_REUSE_EXT = 2;

const VIR_DOMAIN_BLOCK_JOB_ABORT_ASYNC = 1;
const VIR_DOMAIN_BLOCK_JOB_ABORT_PIVOT = 2;
const VIR_DOMAIN_BLOCK_JOB_SPEED_BANDWIDTH_BYTES = 1;

const VIR_DOMAIN_BLOCK_JOB_INFO_BANDWIDTH_BYTES = 1;

const VIR_DOMAIN_BLOCK_JOB_TYPE_UNKNOWN = 0;
const VIR_DOMAIN_BLOCK_JOB_TYPE_PULL = 1;
const VIR_DOMAIN_BLOCK_JOB_TYPE_COPY = 2;
const VIR_DOMAIN_BLOCK_JOB_TYPE_COMMIT = 3;
const VIR_DOMAIN_BLOCK_JOB_TYPE_ACTIVE_COMMIT = 4;

const VIR_DOMAIN_BLOCK_PULL_BANDWIDTH_BYTES = 128;

const VIR_DOMAIN_BLOCK_REBASE_SHALLOW = 1;
const VIR_DOMAIN_BLOCK_REBASE_REUSE_EXT = 2;
const VIR_DOMAIN_BLOCK_REBASE_COPY_RAW = 4;
const VIR_DOMAIN_BLOCK_REBASE_COPY = 8;
const VIR_DOMAIN_BLOCK_REBASE_RELATIVE = 16;
const VIR_DOMAIN_BLOCK_REBASE_COPY_DEV = 32;
const VIR_DOMAIN_BLOCK_REBASE_BANDWIDTH_BYTES = 64;

const VIR_DOMAIN_BLOCK_RESIZE_BYTES = 1;


const VIR_MIGRATE_LIVE = 1;


const VIR_MIGRATE_PEER2PEER = 2;

const VIR_MIGRATE_TUNNELLED = 4;

const VIR_MIGRATE_PERSIST_DEST = 8;

const VIR_MIGRATE_UNDEFINE_SOURCE = 16;

const VIR_MIGRATE_PAUSED = 32;

const VIR_MIGRATE_NON_SHARED_DISK = 64;

const VIR_MIGRATE_NON_SHARED_INC = 128;


const VIR_MIGRATE_CHANGE_PROTECTION = 256;

const VIR_MIGRATE_UNSAFE = 512;

const VIR_MIGRATE_OFFLINE = 1024;

const VIR_MIGRATE_COMPRESSED = 2048;

const VIR_MIGRATE_ABORT_ON_ERROR = 4096;

const VIR_MIGRATE_AUTO_CONVERGE = 8192;


const VIR_DOMAIN_DEVICE_MODIFY_CURRENT = 0;

const VIR_DOMAIN_DEVICE_MODIFY_LIVE = 1;

const VIR_DOMAIN_DEVICE_MODIFY_CONFIG = 2;

const VIR_DOMAIN_DEVICE_MODIFY_FORCE = 4;


const VIR_STORAGE_POOL_BUILD_NEW = 0;

const VIR_STORAGE_POOL_BUILD_REPAIR = 1;

const VIR_STORAGE_POOL_BUILD_RESIZE = 2;


const VIR_DOMAIN_FLAG_FEATURE_ACPI = 1;
const VIR_DOMAIN_FLAG_FEATURE_APIC = 2;
const VIR_DOMAIN_FLAG_FEATURE_PAE = 4;
const VIR_DOMAIN_FLAG_CLOCK_LOCALTIME = 8;
const VIR_DOMAIN_FLAG_TEST_LOCAL_VNC = 16;
const VIR_DOMAIN_FLAG_SOUND_AC97 = 32;
const VIR_DOMAIN_DISK_FILE = 1;
const VIR_DOMAIN_DISK_BLOCK = 2;
const VIR_DOMAIN_DISK_ACCESS_ALL = 4;

const VIR_CONNECT_GET_ALL_DOMAINS_STATS_ACTIVE = 1;
const VIR_CONNECT_GET_ALL_DOMAINS_STATS_INACTIVE = 2;
const VIR_CONNECT_GET_ALL_DOMAINS_STATS_OTHER = 4;
const VIR_CONNECT_GET_ALL_DOMAINS_STATS_PAUSED = 8;
const VIR_CONNECT_GET_ALL_DOMAINS_STATS_PERSISTENT = 16;
const VIR_CONNECT_GET_ALL_DOMAINS_STATS_RUNNING = 32;
const VIR_CONNECT_GET_ALL_DOMAINS_STATS_SHUTOFF = 64;
const VIR_CONNECT_GET_ALL_DOMAINS_STATS_TRANSIENT = 128;
const VIR_CONNECT_GET_ALL_DOMAINS_STATS_ENFORCE_STATS = 2 ^ 31;

const VIR_DOMAIN_MEM_CONFIG = VIR_DOMAIN_AFFECT_CONFIG;
const VIR_DOMAIN_MEM_CURRENT = VIR_DOMAIN_AFFECT_CURRENT;
const VIR_DOMAIN_MEM_LIVE = VIR_DOMAIN_AFFECT_LIVE;
const VIR_DOMAIN_MEM_MAXIMUM = 4;

const VIR_DOMAIN_INTERFACE_ADDRESSES_SRC_LEASE = 0;
const VIR_DOMAIN_INTERFACE_ADDRESSES_SRC_AGENT = 1;
const VIR_DOMAIN_INTERFACE_ADDRESSES_SRC_ARP = VIR_DOMAIN_INTERFACE_ADDRESSES_SRC_LEASE;


const VIR_CONNECT_FLAG_SOUNDHW_GET_NAMES = 1;


const VIR_KEYCODE_SET_LINUX = 0;
const VIR_KEYCODE_SET_XT = 1;
const VIR_KEYCODE_SET_ATSET1 = 6;
const VIR_KEYCODE_SET_ATSET2 = 2;
const VIR_KEYCODE_SET_ATSET3 = 3;
const VIR_KEYCODE_SET_OSX = 4;
const VIR_KEYCODE_SET_XT_KBD = 5;
const VIR_KEYCODE_SET_USB = 6;
const VIR_KEYCODE_SET_WIN32 = 7;
const VIR_KEYCODE_SET_RFB = 8;


const VIR_DOMAIN_UNDEFINE_MANAGED_SAVE = 1;
const VIR_DOMAIN_UNDEFINE_SNAPSHOTS_METADATA = 2;
const VIR_DOMAIN_UNDEFINE_NVRAM = 4;
const VIR_DOMAIN_UNDEFINE_KEEP_NVRAM = 8;












function libvirt_connect(string $url, bool $readonly = true, array $credentials) {}









function libvirt_connect_get_all_domain_stats($conn, int $stats = 0, int $flags = 0): array|false {}








function libvirt_connect_get_capabilities($conn, ?string $xpath): string {}








function libvirt_connect_get_emulator($conn, ?string $arch): string {}







function libvirt_connect_get_encrypted($conn): int {}







function libvirt_connect_get_hostname($conn): string|false {}






function libvirt_connect_get_hypervisor($conn): array {}







function libvirt_connect_get_information($conn): array {}







function libvirt_connect_get_machine_types($conn): array {}







function libvirt_connect_get_maxvcpus($conn): int|false {}








function libvirt_connect_get_nic_models($conn, ?string $arch): array {}







function libvirt_connect_get_secure($conn): int {}









function libvirt_connect_get_soundhw_models($conn, ?string $arch, int $flags = 0): array {}







function libvirt_connect_get_sysinfo($conn): string|false {}








function libvirt_connect_get_uri($conn): string|false {}











function libvirt_domain_attach_device($res, string $xml, int $flags = 0): bool {}













function libvirt_domain_block_commit($res, string $disk, ?string $base, ?string $top, int $bandwidth = 0, int $flags = 0): bool {}









function libvirt_domain_block_job_abort($res, string $path, int $flags = 0): bool {}









function libvirt_domain_block_job_info($res, string $disk, int $flags = 0): array {}










function libvirt_domain_block_job_set_speed($res, string $path, int $bandwidth, int $flags = 0): bool {}










function libvirt_domain_block_resize($res, string $path, int $size, int $flags = 0): bool {}








function libvirt_domain_block_stats($res, string $path): array {}










function libvirt_domain_change_boot_devices($res, string $first, string $second, int $flags = 0) {}










function libvirt_domain_change_memory($res, int $allocMem, int $allocMax, int $flags = 0) {}










function libvirt_domain_change_vcpus($res, int $numCpus, int $flags = 0): bool {}








function libvirt_domain_core_dump($res, string $to): bool {}







function libvirt_domain_create($res): bool {}









function libvirt_domain_create_xml($conn, string $xml, int $flags = 0) {}








function libvirt_domain_define_xml($conn, string $xml) {}







function libvirt_domain_destroy($res): bool {}









function libvirt_domain_detach_device($res, string $xml, int $flags = VIR_DOMAIN_AFFECT_LIVE): bool {}













function libvirt_domain_disk_add($res, string $img, string $dev, string $typ, string $driver, int $flags = 0) {}










function libvirt_domain_disk_remove($res, string $dev, int $flags = 0) {}







function libvirt_domain_get_autostart($res): int {}









function libvirt_domain_get_block_info($res, string $dev): array {}







function libvirt_domain_get_connect($res) {}







function libvirt_domain_get_counts($conn): array {}







function libvirt_domain_get_disk_devices($res): array|false {}







function libvirt_domain_get_id($res): int {}







function libvirt_domain_get_info($res): array {}







function libvirt_domain_get_interface_devices($res): array|false {}







function libvirt_domain_get_job_info($res): array {}










function libvirt_domain_get_metadata($res, int $type, string $uri, int $flags = 0): string|null|false {}







function libvirt_domain_get_name($res): string {}








function libvirt_domain_get_network_info($res, string $mac): array {}








function libvirt_domain_get_next_dev_ids($res): array {}








function libvirt_domain_get_screen_dimensions($res, string $server): array|false {}









function libvirt_domain_get_screenshot($res, string $server, int $scancode = 10): string {}









function libvirt_domain_get_screenshot_api($res, int $screenID = 0): array {}







function libvirt_domain_get_uuid($res): string {}







function libvirt_domain_get_uuid_string($res): string {}









function libvirt_domain_get_xml_desc($res, ?string $xpath, int $flags = 0): string {}









function libvirt_domain_interface_addresses($res, int $source): array|false {}








function libvirt_domain_interface_stats($res, string $path): array {}







function libvirt_domain_is_active($res): bool {}







function libvirt_domain_is_persistent($res): bool {}








function libvirt_domain_lookup_by_id($conn, string $id) {}








function libvirt_domain_lookup_by_name($res, string $name) {}








function libvirt_domain_lookup_by_uuid($res, string $uuid) {}








function libvirt_domain_lookup_by_uuid_string($res, string $uuid) {}








function libvirt_domain_managedsave($res): bool {}










function libvirt_domain_memory_peek($res, int $start, int $size, int $flags = 0): int {}








function libvirt_domain_memory_stats($res, int $flags = 0): array {}











function libvirt_domain_migrate($res, string $dest_conn, int $flags, string $dname, int $bandwidth = 0) {}











function libvirt_domain_migrate_to_uri($res, string $dest_uri, int $flags, string $dname, int $bandwidth = 0): bool {}













function libvirt_domain_migrate_to_uri2($res, string $dconnuri, string $miguri, string $dxml, int $flags, string $dname, int $bandwidth = 0): bool {}






















function libvirt_domain_new($conn, string $name, string|null|false $arch, int $memMB, int $maxmemMB, int $vcpus, string $iso_image, array $disks, array $networks, int $flags = 0) {}






function libvirt_domain_new_get_vnc(): string|null {}












function libvirt_domain_nic_add($res, string $mac, string $network, string $model, int $flags = 0) {}










function libvirt_domain_nic_remove($res, string $dev, int $flags = 0) {}










function libvirt_domain_qemu_agent_command($res, string $cmd, $timeout = -1, int $flags = 0): string|false {}








function libvirt_domain_reboot($res, int $flags = 0): bool {}








function libvirt_domain_reset($res, int $flags = 0): bool {}







function libvirt_domain_resume($res) {}











function libvirt_domain_send_key_api($res, int $codeset, int $holdtime, array $keycodes, int $flags = 0): bool {}









function libvirt_domain_send_keys($res, string $server, int $scancode): bool {}













function libvirt_domain_send_pointer_event($res, string $server, int $pos_x, int $pos_y, int $clicked, bool $release = true): bool {}








function libvirt_domain_set_autostart($res, bool $flags): bool {}








function libvirt_domain_set_max_memory($res, int $memory): bool {}








function libvirt_domain_set_memory($res, int $memory): bool {}









function libvirt_domain_set_memory_flags($res, int $memory = 0, int $flags = 0): bool {}












function libvirt_domain_set_metadata($res, int $type, string $metadata, string $key, string $uri, int $flags = 0): int {}







function libvirt_domain_shutdown($res): bool {}







function libvirt_domain_suspend($res): bool {}







function libvirt_domain_undefine($res): bool {}








function libvirt_domain_undefine_flags($res, int $flags = 0): bool {}










function libvirt_domain_update_device($res, string $xml, int $flags): bool {}









function libvirt_domain_xml_from_native($conn, string $format, string $config_data): string|false {}









function libvirt_domain_xml_to_native($conn, string $format, string $xml_data): string|false {}









function libvirt_domain_xml_xpath($res, string $xpath, int $flags = 0): array {}







function libvirt_list_active_domain_ids($res): array {}







function libvirt_list_active_domains($res): array {}







function libvirt_list_domain_resources($res): array {}







function libvirt_list_domains($res): array {}







function libvirt_list_inactive_domains($res): array {}











function libvirt_list_all_networks($conn, int $flags = VIR_CONNECT_LIST_NETWORKS_ACTIVE|VIR_CONNECT_LIST_NETWORKS_INACTIVE): array {}









function libvirt_list_networks($res, int $flags = 0): array {}








function libvirt_network_define_xml($res, string $xml) {}








function libvirt_network_get($res, string $name) {}







function libvirt_network_get_active($res): int|false {}







function libvirt_network_get_autostart($res): int {}







function libvirt_network_get_bridge($res): string {}







function libvirt_network_get_information($res): array {}







function libvirt_network_get_name($res): string|false {}







function libvirt_network_get_uuid($res): string|false {}







function libvirt_network_get_uuid_string($res): string|false {}








function libvirt_network_get_xml_desc($res, ?string $xpath): string|false {}








function libvirt_network_set_active($res, int $flags): bool {}








function libvirt_network_set_autostart($res, int $flags): bool {}







function libvirt_network_undefine($res): bool {}












function libvirt_node_get_cpu_stats($conn, int $cpunr = VIR_NODE_CPU_STATS_ALL_CPUS): array|false {}









function libvirt_node_get_cpu_stats_for_each_cpu($conn, int $time = 0): array|false {}







function libvirt_node_get_free_memory($conn): string|false {}








function libvirt_node_get_info($conn): array|false {}







function libvirt_node_get_mem_stats($conn): array {}










function libvirt_list_nodedevs($res, ?string $cap): array {}







function libvirt_nodedev_capabilities($res): array {}








function libvirt_nodedev_get($res, string $name) {}







function libvirt_nodedev_get_information($res): array {}








function libvirt_nodedev_get_xml_desc($res, ?string $xpath): string {}









function libvirt_list_all_nwfilters($res): array {}







function libvirt_list_nwfilters($conn): array {}








function libvirt_nwfilter_define_xml($conn, string $xml) {}







function libvirt_nwfilter_get_name($res): string|false {}







function libvirt_nwfilter_get_uuid($res): string|false {}







function libvirt_nwfilter_get_uuid_string($res): string|false {}








function libvirt_nwfilter_get_xml_desc($res, ?string $xpath): string {}








function libvirt_nwfilter_lookup_by_name($conn, string $name) {}








function libvirt_nwfilter_lookup_by_uuid_string($conn, string $uuid) {}







function libvirt_nwfilter_undefine($res): bool {}
















function libvirt_check_version(int $major, int $minor, int $micro, int $type): bool {}






function libvirt_get_iso_images(string $path): array|false {}





function libvirt_get_last_error(): string {}






function libvirt_get_last_error_code(): int {}






function libvirt_get_last_error_domain(): int {}







function libvirt_has_feature(string $name): bool {}











function libvirt_image_create($conn, string $name, int $size, string $format): string|false {}









function libvirt_image_remove($conn, string $image): string|false {}








function libvirt_logfile_set(?string $filename, int $maxsize): bool {}







function libvirt_print_binding_resources() {}








function libvirt_version(string $type): array {}











function libvirt_domain_has_current_snapshot($res, int $flags = 0): bool {}








function libvirt_domain_snapshot_create($res, int $flags = 0) {}








function libvirt_domain_snapshot_current($res, int $flags = 0) {}









function libvirt_domain_snapshot_delete($res, int $flags = 0): bool {}








function libvirt_domain_snapshot_get_xml($res, int $flags = 0): string {}









function libvirt_domain_snapshot_lookup_by_name($res, string $name, int $flags = 0) {}








function libvirt_domain_snapshot_revert($res, int $flags = 0): bool {}








function libvirt_list_domain_snapshots($res, int $flags = 0): array {}









function libvirt_list_active_storagepools($res): array {}







function libvirt_list_inactive_storagepools($res): array {}







function libvirt_list_storagepools($res): array {}







function libvirt_storagepool_build($res): bool {}







function libvirt_storagepool_create($res): bool {}









function libvirt_storagepool_define_xml($res, string $xml, int $flags = 0) {}







function libvirt_storagepool_delete($res): bool {}







function libvirt_storagepool_destroy($res): bool {}







function libvirt_storagepool_get_autostart($res): bool {}







function libvirt_storagepool_get_info($res): array {}







function libvirt_storagepool_get_name($res): string {}







function libvirt_storagepool_get_uuid_string($res): string {}







function libvirt_storagepool_get_volume_count($res): int {}








function libvirt_storagepool_get_xml_desc($res, ?string $xpath): string {}







function libvirt_storagepool_is_active($res): bool {}







function libvirt_storagepool_list_volumes($res): array {}








function libvirt_storagepool_lookup_by_name($res, string $name) {}








function libvirt_storagepool_lookup_by_uuid_string($res, string $uuid) {}







function libvirt_storagepool_lookup_by_volume($res) {}








function libvirt_storagepool_refresh($res, int $flags = 0): bool {}







function libvirt_storagepool_set_autostart($res, bool $flags): bool {}







function libvirt_storagepool_undefine($res): bool {}









function libvirt_storagevolume_create_xml($res, string $xml, int $flags = 0) {}









function libvirt_storagevolume_create_xml_from($pool, string $xml, $original_volume) {}








function libvirt_storagevolume_delete($res, int $flags = 0): bool {}











function libvirt_storagevolume_download($res, $stream, int $offset = 0, int $length = 0, int $flags = 0): int {}







function libvirt_storagevolume_get_info($res): array {}







function libvirt_storagevolume_get_name($res): string {}







function libvirt_storagevolume_get_path($res): string {}









function libvirt_storagevolume_get_xml_desc($res, ?string $xpath, int $flags = 0): string {}








function libvirt_storagevolume_lookup_by_name($res, string $name) {}








function libvirt_storagevolume_lookup_by_path($res, string $path) {}









function libvirt_storagevolume_resize($res, int $capacity, int $flags = 0): int {}











function libvirt_storagevolume_upload($res, $stream, int $offset = 0, int $length = 0, int $flags = 0): int {}









function libvirt_stream_abort($res): int {}







function libvirt_stream_close($res): int {}







function libvirt_stream_create($res) {}







function libvirt_stream_finish($res): int {}









function libvirt_stream_recv($res, string $data, int $len = 0): int {}









function libvirt_stream_send($res, string $data, int $length = 0): int {}
