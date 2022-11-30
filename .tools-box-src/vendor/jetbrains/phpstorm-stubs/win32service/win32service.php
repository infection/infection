<?php





define("WIN32_SERVICE_WIN32_OWN_PROCESS", 0x00000010);



define("WIN32_SERVICE_INTERACTIVE_PROCESS", 0x00000100);




define("WIN32_SERVICE_WIN32_OWN_PROCESS_INTERACTIVE", 0x00000110);






define("WIN32_SERVICE_CONTINUE_PENDING", 0x00000005);



define("WIN32_SERVICE_PAUSE_PENDING", 0x00000006);



define("WIN32_SERVICE_PAUSED", 0x00000007);



define("WIN32_SERVICE_RUNNING", 0x00000004);



define("WIN32_SERVICE_START_PENDING", 0x00000002);



define("WIN32_SERVICE_STOP_PENDING", 0x00000003);



define("WIN32_SERVICE_STOPPED", 0x00000001);






define("WIN32_SERVICE_CONTROL_CONTINUE", 0x00000003);



define("WIN32_SERVICE_CONTROL_INTERROGATE", 0x00000004);



define("WIN32_SERVICE_CONTROL_PAUSE", 0x00000002);





define("WIN32_SERVICE_CONTROL_PRESHUTDOWN", 0x0000000F);





define("WIN32_SERVICE_CONTROL_SHUTDOWN", 0x00000005);



define("WIN32_SERVICE_CONTROL_STOP", 0x00000001);







define("WIN32_SERVICE_ACCEPT_PAUSE_CONTINUE", 0x00000002);





define("WIN32_SERVICE_ACCEPT_PRESHUTDOWN", 0x00000100);




define("WIN32_SERVICE_ACCEPT_SHUTDOWN", 0x00000004);




define("WIN32_SERVICE_ACCEPT_STOP", 0x00000001);






define("WIN32_SERVICE_AUTO_START", 0x00000002);



define("WIN32_SERVICE_DEMAND_START", 0x00000003);




define("WIN32_SERVICE_DISABLED", 0x00000004);






define("WIN32_SERVICE_ERROR_IGNORE", 0x00000000);



define("WIN32_SERVICE_ERROR_NORMAL", 0x00000001);






define("WIN32_SERVICE_RUNS_IN_SYSTEM_PROCESS", 0x00000001);






define("WIN32_ERROR_ACCESS_DENIED", 0x00000005);



define("WIN32_ERROR_CIRCULAR_DEPENDENCY", 0x00000423);



define("WIN32_ERROR_DATABASE_DOES_NOT_EXIST", 0x00000429);



define("WIN32_ERROR_DEPENDENT_SERVICES_RUNNING", 0x0000041B);




define("WIN32_ERROR_DUPLICATE_SERVICE_NAME", 0x00000436);





define("WIN32_ERROR_FAILED_SERVICE_CONTROLLER_CONNECT", 0x00000427);



define("WIN32_ERROR_INSUFFICIENT_BUFFER", 0x0000007A);



define("WIN32_ERROR_INVALID_DATA", 0x0000000D);



define("WIN32_ERROR_INVALID_HANDLE", 0x00000006);



define("WIN32_ERROR_INVALID_LEVEL", 0x0000007C);



define("WIN32_ERROR_INVALID_NAME", 0x0000007B);



define("WIN32_ERROR_INVALID_PARAMETER", 0x00000057);



define("WIN32_ERROR_INVALID_SERVICE_ACCOUNT", 0x00000421);



define("WIN32_ERROR_INVALID_SERVICE_CONTROL", 0x0000041C);



define("WIN32_ERROR_PATH_NOT_FOUND", 0x00000003);



define("WIN32_ERROR_SERVICE_ALREADY_RUNNING", 0x00000420);




define("WIN32_ERROR_SERVICE_CANNOT_ACCEPT_CTRL", 0x00000425);



define("WIN32_ERROR_SERVICE_DATABASE_LOCKED", 0x0000041F);



define("WIN32_ERROR_SERVICE_DEPENDENCY_DELETED", 0x00000433);



define("WIN32_ERROR_SERVICE_DEPENDENCY_FAIL", 0x0000042C);



define("WIN32_ERROR_SERVICE_DISABLED", 0x00000422);



define("WIN32_ERROR_SERVICE_DOES_NOT_EXIST", 0x00000424);



define("WIN32_ERROR_SERVICE_EXISTS", 0x00000431);




define("WIN32_ERROR_SERVICE_LOGON_FAILED", 0x0000042D);



define("WIN32_ERROR_SERVICE_MARKED_FOR_DELETE", 0x00000430);



define("WIN32_ERROR_SERVICE_NO_THREAD", 0x0000041E);



define("WIN32_ERROR_SERVICE_NOT_ACTIVE", 0x00000426);




define("WIN32_ERROR_SERVICE_REQUEST_TIMEOUT", 0x0000041D);



define("WIN32_ERROR_SHUTDOWN_IN_PROGRESS", 0x0000045B);



define("WIN32_NO_ERROR", 0x00000000);






define("WIN32_ABOVE_NORMAL_PRIORITY_CLASS", 0x00008000);



define("WIN32_BELOW_NORMAL_PRIORITY_CLASS", 0x00004000);






define("WIN32_HIGH_PRIORITY_CLASS", 0x00000080);





define("WIN32_IDLE_PRIORITY_CLASS", 0x00000040);



define("WIN32_NORMAL_PRIORITY_CLASS", 0x00000020);






define("WIN32_REALTIME_PRIORITY_CLASS", 0x00000100);











function win32_continue_service($serviceName, $machine = "") {}





































































































function win32_create_service($details, $machine = "") {}













function win32_delete_service($serviceName, $machine = "") {}













function win32_get_last_control_message() {}










function win32_pause_service($serviceName, $machine = "") {}





















































function win32_query_service_status($serviceName, $machine = "") {}























function win32_set_service_status($status, $checkpoint = 0) {}























function win32_start_service_ctrl_dispatcher($name) {}











function win32_start_service($serviceName, $machine = "") {}










function win32_stop_service($serviceName, $machine = "") {}
