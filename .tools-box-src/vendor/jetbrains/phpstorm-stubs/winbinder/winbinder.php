<?php








define('AppWindow', 1); 
define('ModalDialog', 2); 
define('ModelessDialog', 3); 
define('NakedWindow', 4); 
define('PopupWindow', 5); 
define('ResizableWindow', 6); 
define('ToolDialog', 7); 
define('Accel', 8);
define('Calendar', 9);
define('CheckBox', 10);
define('ComboBox', 11);
define('EditBox', 12);
define('Frame', 13);
define('Gauge', 14);
define('HTMLControl', 15);
define('HyperLink', 16);
define('ImageButton', 17);
define('InvisibleArea', 18);
define('Label', 19);
define('ListBox', 20);
define('ListView', 21);
define('Menu', 22);
define('PushButton', 23);
define('RTFEditBox', 24);
define('RadioButton', 25);
define('ScrollBar', 26);
define('Slider', 27);
define('Spinner', 28);
define('StatusBar', 29);
define('TabControl', 30);
define('ToolBar', 31);
define('TreeView', 32);
define('Timer', Timer); 

define('WBC_VERSION', '2010.10.14');
define('WBC_BORDER', 8);
define('WBC_BOTTOM', 8192);
define('WBC_CENTER', 2048);
define('WBC_CHECKBOXES', 65536);
define('WBC_CUSTOMDRAW', 268435456);
define('WBC_DEFAULTPOS', -2147483648);
define('WBC_DISABLED', 2);
define('WBC_ELLIPSIS', 131072);
define('WBC_ENABLED', 0);
define('WBC_GROUP', 524288);
define('WBC_IMAGE', 4);
define('WBC_INVISIBLE', 1);
define('WBC_LEFT', 0);
define('WBC_LINES', 128);
define('WBC_MASKED', 256);
define('WBC_MIDDLE', 0);
define('WBC_MULTILINE', 128);
define('WBC_NOTIFY', 16);
define('WBC_NUMBER', 1024);
define('WBC_READONLY', 64);
define('WBC_RIGHT', 32);
define('WBC_SINGLE', 1048576);
define('WBC_SORT', 262144);
define('WBC_TASKBAR', 512);
define('WBC_AUTOREPEAT', 512);
define('WBC_TOP', 4096);
define('WBC_VISIBLE', 0);
define('WBC_TRANSPARENT', 536870912);
define('WBC_DEFAULT', 8);
define('WBC_MULTISELECT', 1073741824);
define('WBC_NOHEADER', 268435456);
define('WBC_DBLCLICK', 64);
define('WBC_MOUSEMOVE', 128);
define('WBC_MOUSEDOWN', 256);
define('WBC_MOUSEUP', 512);
define('WBC_KEYDOWN', 1024);
define('WBC_KEYUP', 2048);
define('WBC_GETFOCUS', 4096);
define('WBC_RESIZE', 8192);
define('WBC_REDRAW', 16384);
define('WBC_HEADERSEL', 32768);
define('WBC_ALT', 32);
define('WBC_CONTROL', 8);
define('WBC_SHIFT', 4);
define('WBC_LBUTTON', 1);
define('WBC_MBUTTON', 16);
define('WBC_RBUTTON', 2);
define('WBC_BEEP', -1);
define('WBC_INFO', 64);
define('WBC_OK', 0);
define('WBC_OKCANCEL', 33);
define('WBC_QUESTION', 32);
define('WBC_STOP', 16);
define('WBC_WARNING', 48);
define('WBC_YESNO', 36);
define('WBC_YESNOCANCEL', 35);
define('WBC_MAXIMIZED', 2);
define('WBC_MINIMIZED', 1);
define('WBC_NORMAL', 0);
define('WBC_MINSIZE', 2);
define('WBC_MAXSIZE', 3);
define('WBC_TITLE', 1);

define('WBC_RTF_TEXT', 1);

define('IDABORT', 3);
define('IDCANCEL', 2);
define('IDCLOSE', 8);
define('IDDEFAULT', 0);
define('IDHELP', 9);
define('IDIGNORE', 5);
define('IDNO', 7);
define('IDOK', 1);
define('IDRETRY', 4);
define('IDYES', 6);
define('FTA_BOLD', 1);
define('FTA_ITALIC', 2);
define('FTA_NORMAL', 0);
define('FTA_REGULAR', 0);
define('FTA_STRIKEOUT', 8);
define('FTA_UNDERLINE', 4);
define('BLACK', 0);
define('BLUE', 16711680);
define('CYAN', 16776960);
define('DARKBLUE', 8388608);
define('DARKCYAN', 8421376);
define('DARKGRAY', 8421504);
define('DARKGREEN', 32768);
define('DARKMAGENTA', 8388736);
define('DARKRED', 128);
define('DARKYELLOW', 32896);
define('GREEN', 65280);
define('LIGHTGRAY', 12632256);
define('MAGENTA', 16711935);
define('RED', 255);
define('WHITE', 16777215);
define('YELLOW', 65535);
define('NOCOLOR', -1);
define('bgrBLACK', 0);
define('bgrBLUE', 255);
define('bgrCYAN', 65535);
define('bgrDARKBLUE', 128);
define('bgrDARKCYAN', 32896);
define('bgrDARKGRAY', 8421504);
define('bgrDARKGREEN', 32768);
define('bgrDARKMAGENTA', 8388736);
define('bgrDARKRED', 8388608);
define('bgrDARKYELLOW', 8421376);
define('bgrGREEN', 65280);
define('bgrLIGHTGRAY', 12632256);
define('bgrMAGENTA', 16711935);
define('bgrRED', 16711680);
define('bgrWHITE', 16777215);
define('bgrYELLOW', 16776960);
define('bgrNOCOLOR', -1);


define('WBC_LV_NONE', 0);
define('WBC_LV_FORE', 1);
define('WBC_LV_BACK', 2);
define('WBC_LV_DEFAULT', 0);
define('WBC_LV_DRAW', 1);
define('WBC_LV_COLUMNS', 2);












function wb_main_loop() {}










function wb_find_file($filename) {}





























function wb_message_box($parent, $message, $title = null, $style = null) {}






































function wb_play_sound($source, $command = null) {}
















function wb_stop_sound($command = null) {}


















function wb_exec($command, $param = null) {}



































function wb_get_system_info($info) {}
















function wb_get_registry_key($key, $subkey, $entry = null) {}

















function wb_set_registry_key($key, $subkey, $entry = null, $value = null) {}






















function wb_create_timer($window, $id, $interval) {}



















function wb_wait($window = null, $pause = null, $flags = null) {}










function wb_destroy_timer($window, $id) {}
















function wb_load_image($filename, $index = null, $param = null) {}












function wb_save_image($image, $filename) {}













function wb_create_image($width = 0, $height = 0, $dibbmi = null, $dibbits = null) {}












function wb_create_mask($bitmap, $transparent_color) {}








function wb_destroy_image($image) {}











function wb_get_image_data($image, $compress4to3) {}












function wb_get_pixel($source, $xpos, $ypos) {}














function wb_draw_point($source, $xpos, $ypos, $color) {}



























function wb_draw_line($target, $x0, $y0, $x1, $y1, $color, $linewidth = null, $linestyle = null) {}
























function wb_draw_rect($target, $xpos, $ypos, $width, $height, $color, $filled = null, $linewidth = null, $linestyle = null) {}
























function wb_draw_ellipse($target, $xpos, $ypos, $width, $height, $color, $filled = null, $linewidth = null, $linestyle = null) {}
























function wb_draw_text($target, $text, $xpos, $ypos, $width = null, $height = null, $font = null, $flags = null) {}

























function wb_draw_image($target, $bitmap, $xpos = 0, $ypos = 0, $width = null, $height = null, $transparentcolor = null, $xoffset = null, $yoffset = null) {}













function wb_destroy_control($control) {}










function wb_get_value($wbobject, $item = -1, $subitem = -1) {}


















function wb_refresh($wbobject, $now = null, $xpos = null, $ypos = null, $width = null, $height = null) {}











function wb_set_enabled($control, $enabled) {}























function wb_set_image($wbobject, $source, $transparentcolor = null, $index = null, $param = null) {}














function wb_set_item_image($wbobject, $index, $item = null, $subitem = null) {}


















function wb_delete_items($ctrl, $items = null) {}









function wb_get_class($wbobject) {}










function wb_get_control($wbobject, $id) {}








function wb_get_enabled($wbobject) {}






function wb_get_focus() {}








function wb_get_id($wbobject) {}












function wb_get_item_count($wbobject) {}









function wb_get_parent($wbobject, $item = null) {}


















function wb_get_selected($wbobject) {}













function wb_get_state($wbobject, $item = null) {}








function wb_get_visible($wbobject) {}













function wb_set_cursor($wbobject, $source) {}








function wb_set_focus($wbobject) {}
















function wb_set_handler($window, $fn_handler) {}


















function wb_set_location($wbobject, $location) {}












function wb_set_range($control, $vmin, $vmax) {}














function wb_set_state($wbobject, $item, $state) {}





















function wb_set_style($wbobject, $style, $set) {}











function wb_set_visible($wbobject, $visible) {}





















function wb_sort($control, $ascending = null, $subitem = null) {}












function wb_get_level($wbobject, $item) {}


























function wb_create_font($name, $height, $color = null, $flags = null) {}








function wb_destroy_font($nfont) {}

















function wb_set_font($control, $font = null, $redraw = null) {}










function wb_get_address($var) {}



















function wb_send_message($wbobject, $message, $wparam = 0, $lparam = 0) {}











function wb_peek($address, $length = 0) {}










function wb_poke($address, $contents, $length = null) {}





























function wb_load_library($libname) {}










function wb_release_library($idlib) {}
































function wb_get_function_address($fname, $idlib) {}













function wb_call_function($address, $args = []) {}







function wb_get_midi_callback() {}






function wb_get_enum_callback() {}





function wb_get_hook_callback() {}










function wb_destroy_window($window) {}































function wb_get_size($object, $param = null) {}


















function wb_set_size($wbobject, $width, $height = null) {}













function wb_set_position($wbobject, $xpos = null, $ypos = null) {}













function wb_get_position($wbobject, $clientarea = null) {}



























function wb_create_window($parent, $wclass, $caption = null, $xpos = null, $ypos = null, $width = null, $height = null, $style = null, $param = null) {}





















function wb_get_instance($caption, $bringtofront = null) {}








function wb_get_item_list($wbobject) {}

























function wb_set_area($window, $type, $x = null, $y = null, $width = null, $height = null) {}
















function wb_sys_dlg_path($parent, $title = null, $path = null) {}
















function wb_sys_dlg_color($parent, $title = null, $color = null) {}







function wbtemp_set_accel_table($parent, $accels) {}
















function wbtemp_create_control($parent, $class, $caption, $xpos, $ypos, $width, $height, $id, $style, $lparam, $ntab) {}







function wbtemp_create_item($ctrl, $str) {}









function wbtemp_create_statusbar_items($ctrl, $items, $clear, $param) {}







function wbtemp_get_text($ctrl, $item = null) {}








function wbtemp_set_text($ctrl, $text, $item) {}







function wbtemp_select_tab($ctrl, $selitems) {}








function wbtemp_set_value($ctrl, $value, $item = null) {}









function wbtemp_create_listview_item($ctrl, $item, $image, $value) {}








function wbtemp_set_listview_item_checked($ctrl, $index, $value) {}







function wbtemp_get_listview_item_checked($ctrl, $item) {}









function wbtemp_set_listview_item_text($ctrl, $item, $subitem, $text) {}







function wbtemp_get_listview_text($ctrl, $item) {}








function wbtemp_get_listview_columns($ctrl) {}










function wbtemp_create_listview_column($ctrl, $i, $text, $width, $align) {}






function wbtemp_clear_listview_columns($ctrl) {}








function wbtemp_select_listview_item($ctrl, $item, $selected) {}







function wbtemp_select_all_listview_items($ctrl, $bool) {}







function wbtemp_create_menu($parent, $caption) {}







function wbtemp_get_menu_item_checked($ctrl, $item) {}








function wbtemp_set_menu_item_checked($ctrl, $selitems, $selected) {}








function wbtemp_set_menu_item_selected($ctrl, $item, $selected) {}








function wbtemp_set_menu_item_image($ctrl, $item, $imageHandle) {}










function wbtemp_create_toolbar($parent, $caption, $width, $height, $lparam) {}












function wbtemp_create_treeview_item($ctrl, $name, $value, $where = 0, $image_index = 0, $selected_image = 0, $selected_image_index = 0) {}







function wbtemp_set_treeview_item_selected($ctrl, $selitems) {}








function wbtemp_set_treeview_item_text($ctrl, $item, $text) {}








function wbtemp_set_treeview_item_value($ctrl, $item, $value) {}







function wbtemp_get_treeview_item_text($ctrl, $item) {}










function wbtemp_sys_dlg_open($parent, $title = null, $filter = null, $path = null, $flags = null) {}











function wbtemp_sys_dlg_save($wbObj, $title = '', $filter = '', $path = '', $filename = '', $defext = '') {}
