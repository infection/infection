<?php




function xdebug_info(string $category = '') {}





function config_get_hash(): array {}






function xdebug_get_stack_depth(): int {}





function xdebug_get_function_stack(): array {}







function xdebug_print_function_stack(string $message = 'user triggered', int $options = 0) {}





function xdebug_get_declared_vars(): array {}







function xdebug_call_file(int $depth = 2) {}







function xdebug_call_class(int $depth = 2) {}







function xdebug_call_function(int $depth = 2) {}







function xdebug_call_line(int $depth = 2) {}









function xdebug_start_function_monitor(array $listOfFunctionsToMonitor) {}






function xdebug_stop_function_monitor() {}





function xdebug_get_monitored_functions(): array {}







function xdebug_var_dump(mixed ...$variable) {}













function xdebug_debug_zval(string ...$varname) {}










function xdebug_debug_zval_stdout(string ...$varname) {}





function xdebug_enable() {}





function xdebug_disable() {}





function xdebug_is_enabled() {}













function xdebug_start_error_collection() {}






function xdebug_stop_error_collection() {}








function xdebug_get_collected_errors(bool $emptyList = false): array {}






function xdebug_break(): bool {}











function xdebug_start_trace(?string $traceFile = null, int $options = 0): ?string {}






function xdebug_stop_trace(): string {}






function xdebug_get_tracefile_name() {}






function xdebug_get_profiler_filename() {}





function xdebug_dump_aggr_profiling_data($prefix) {}




function xdebug_clear_aggr_profiling_data() {}








function xdebug_memory_usage(): int {}








function xdebug_peak_memory_usage(): int {}






function xdebug_time_index(): float {}











function xdebug_start_code_coverage(int $options = 0) {}








function xdebug_stop_code_coverage(bool $cleanUp = true) {}





function xdebug_code_coverage_started(): bool {}







function xdebug_get_code_coverage(): array {}






function xdebug_get_function_count(): int {}







function xdebug_dump_superglobals() {}







function xdebug_get_headers(): array {}

function xdebug_get_formatted_function_stack() {}








function xdebug_is_debugger_active(): bool {}





function xdebug_start_gcstats(?string $gcstatsFile = null) {}





function xdebug_stop_gcstats(): string {}






function xdebug_get_gcstats_filename() {}




function xdebug_get_gc_run_count(): int {}




function xdebug_get_gc_total_collected_roots(): int {}







function xdebug_set_filter(int $group, int $listType, array $configuration) {}

function xdebug_connect_to_client(): bool {}

function xdebug_notify(mixed $data): bool {}

define('XDEBUG_STACK_NO_DESC', 1);
define('XDEBUG_TRACE_APPEND', 1);
define('XDEBUG_TRACE_COMPUTERIZED', 2);
define('XDEBUG_TRACE_HTML', 4);
define('XDEBUG_TRACE_NAKED_FILENAME', 8);
define('XDEBUG_CC_UNUSED', 1);
define('XDEBUG_CC_DEAD_CODE', 2);
define('XDEBUG_CC_BRANCH_CHECK', 4);
define('XDEBUG_FILTER_TRACING', 768);
define('XDEBUG_FILTER_STACK', 512);
define('XDEBUG_FILTER_CODE_COVERAGE', 256);
define('XDEBUG_FILTER_NONE', 0);
define('XDEBUG_PATH_WHITELIST', 1);
define('XDEBUG_PATH_BLACKLIST', 2);
define('XDEBUG_NAMESPACE_WHITELIST', 17);
define('XDEBUG_NAMESPACE_BLACKLIST', 18);
define('XDEBUG_NAMESPACE_EXCLUDE', 18);
define('XDEBUG_NAMESPACE_INCLUDE', 17);
define('XDEBUG_PATH_EXCLUDE', 2);
define('XDEBUG_PATH_INCLUDE', 1);
