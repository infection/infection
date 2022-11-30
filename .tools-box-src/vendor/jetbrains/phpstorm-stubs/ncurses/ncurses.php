<?php










function ncurses_addch($ch) {}








function ncurses_color_set($pair) {}








function ncurses_delwin($window) {}






function ncurses_end() {}






function ncurses_getch() {}






function ncurses_has_colors() {}






function ncurses_init() {}












function ncurses_init_pair($pair, $fg, $bg) {}














function ncurses_color_content($color, &$r, &$g, &$b) {}












function ncurses_pair_content($pair, &$f, &$b) {}










function ncurses_move($y, $x) {}


















function ncurses_newwin($rows, $cols, $y, $x) {}








function ncurses_refresh($ch) {}






function ncurses_start_color() {}






function ncurses_standout() {}






function ncurses_standend() {}






function ncurses_baudrate() {}






function ncurses_beep() {}







function ncurses_can_change_color() {}






function ncurses_cbreak() {}






function ncurses_clear() {}






function ncurses_clrtobot() {}






function ncurses_clrtoeol() {}






function ncurses_def_prog_mode() {}






function ncurses_reset_prog_mode() {}






function ncurses_def_shell_mode() {}






function ncurses_reset_shell_mode() {}






function ncurses_delch() {}






function ncurses_deleteln() {}






function ncurses_doupdate() {}






function ncurses_echo() {}






function ncurses_erase() {}








function ncurses_werase($window) {}






function ncurses_erasechar() {}






function ncurses_flash() {}






function ncurses_flushinp() {}







function ncurses_has_ic() {}







function ncurses_has_il() {}






function ncurses_inch() {}






function ncurses_insertln() {}








function ncurses_isendwin() {}






function ncurses_killchar() {}






function ncurses_nl() {}






function ncurses_nocbreak() {}






function ncurses_noecho() {}






function ncurses_nonl() {}






function ncurses_noraw() {}






function ncurses_raw() {}










function ncurses_meta($window, $bit8) {}






function ncurses_resetty() {}






function ncurses_savetty() {}






function ncurses_termattrs() {}






function ncurses_use_default_colors() {}






function ncurses_slk_attr() {}






function ncurses_slk_clear() {}






function ncurses_slk_noutrefresh() {}






function ncurses_slk_refresh() {}






function ncurses_slk_restore() {}






function ncurses_slk_touch() {}








function ncurses_attroff($attributes) {}








function ncurses_attron($attributes) {}








function ncurses_attrset($attributes) {}








function ncurses_bkgd($attrchar) {}








function ncurses_curs_set($visibility) {}








function ncurses_delay_output($milliseconds) {}








function ncurses_echochar($character) {}








function ncurses_halfdelay($tenth) {}








function ncurses_has_key($keycode) {}








function ncurses_insch($character) {}








function ncurses_insdelln($count) {}








function ncurses_mouseinterval($milliseconds) {}








function ncurses_napms($milliseconds) {}








function ncurses_scrl($count) {}








function ncurses_slk_attroff($intarg) {}








function ncurses_slk_attron($intarg) {}








function ncurses_slk_attrset($intarg) {}








function ncurses_slk_color($intarg) {}
















function ncurses_slk_init($format) {}












function ncurses_slk_set($labelnr, $label, $format) {}








function ncurses_typeahead($fd) {}








function ncurses_ungetch($keycode) {}








function ncurses_vidattr($intarg) {}








function ncurses_wrefresh($window) {}








function ncurses_use_extended_names($flag) {}








function ncurses_bkgdset($attrchar) {}






function ncurses_filter() {}






function ncurses_noqiflush() {}






function ncurses_qiflush() {}








function ncurses_timeout($millisec) {}








function ncurses_use_env($flag) {}








function ncurses_addstr($text) {}








function ncurses_putp($text) {}








function ncurses_scr_dump($filename) {}








function ncurses_scr_init($filename) {}








function ncurses_scr_restore($filename) {}








function ncurses_scr_set($filename) {}












function ncurses_mvaddch($y, $x, $c) {}














function ncurses_mvaddchnstr($y, $x, $s, $n) {}










function ncurses_addchnstr($s, $n) {}












function ncurses_mvaddchstr($y, $x, $s) {}








function ncurses_addchstr($s) {}














function ncurses_mvaddnstr($y, $x, $s, $n) {}










function ncurses_addnstr($s, $n) {}












function ncurses_mvaddstr($y, $x, $s) {}










function ncurses_mvdelch($y, $x) {}










function ncurses_mvgetch($y, $x) {}










function ncurses_mvinch($y, $x) {}














function ncurses_mvwaddstr($window, $y, $x, $text) {}








function ncurses_insstr($text) {}









function ncurses_instr(&$buffer) {}














function ncurses_mvhline($y, $x, $attrchar, $n) {}














function ncurses_mvcur($old_y, $old_x, $new_y, $new_x) {}














function ncurses_init_color($color, $r, $g, $b) {}


























function ncurses_border($left, $right, $top, $bottom, $tl_corner, $tr_corner, $bl_corner, $br_corner) {}










function ncurses_assume_default_colors($fg, $bg) {}










function ncurses_define_key($definition, $keycode) {}










function ncurses_hline($charattr, $n) {}










function ncurses_vline($charattr, $n) {}










function ncurses_keyok($keycode, $enable) {}







function ncurses_termname() {}







function ncurses_longname() {}















function ncurses_mousemask($newmask, &$oldmask) {}

















function ncurses_getmouse(array &$mevent) {}










function ncurses_ungetmouse(array $mevent) {}












function ncurses_mouse_trafo(&$y, &$x, $toscreen) {}














function ncurses_wmouse_trafo($window, &$y, &$x, $toscreen) {}












function ncurses_waddstr($window, $str, $n = null) {}








function ncurses_wnoutrefresh($window) {}








function ncurses_wclear($window) {}










function ncurses_wcolor_set($window, $color_pair) {}








function ncurses_wgetch($window) {}










function ncurses_keypad($window, $bf) {}












function ncurses_wmove($window, $y, $x) {}










function ncurses_newpad($rows, $cols) {}




















function ncurses_prefresh($pad, $pminrow, $pmincol, $sminrow, $smincol, $smaxrow, $smaxcol) {}




















function ncurses_pnoutrefresh($pad, $pminrow, $pmincol, $sminrow, $smincol, $smaxrow, $smaxcol) {}








function ncurses_wstandout($window) {}








function ncurses_wstandend($window) {}










function ncurses_wattrset($window, $attrs) {}










function ncurses_wattron($window, $attrs) {}










function ncurses_wattroff($window, $attrs) {}










function ncurses_waddch($window, $ch) {}





























function ncurses_wborder($window, $left, $right, $top, $bottom, $tl_corner, $tr_corner, $bl_corner, $br_corner) {}












function ncurses_whline($window, $charattr, $n) {}












function ncurses_wvline($window, $charattr, $n) {}












function ncurses_getyx($window, &$y, &$x) {}















function ncurses_getmaxyx($window, &$y, &$x) {}






function ncurses_update_panels() {}








function ncurses_panel_window($panel) {}








function ncurses_panel_below($panel) {}








function ncurses_panel_above($panel) {}










function ncurses_replace_panel($panel, $window) {}












function ncurses_move_panel($panel, $startx, $starty) {}








function ncurses_bottom_panel($panel) {}








function ncurses_top_panel($panel) {}








function ncurses_show_panel($panel) {}








function ncurses_hide_panel($panel) {}








function ncurses_del_panel($panel) {}








function ncurses_new_panel($window) {}

define('NCURSES_COLOR_BLACK', 0);
define('NCURSES_COLOR_RED', 1);
define('NCURSES_COLOR_GREEN', 2);
define('NCURSES_COLOR_YELLOW', 3);
define('NCURSES_COLOR_BLUE', 4);
define('NCURSES_COLOR_MAGENTA', 5);
define('NCURSES_COLOR_CYAN', 6);
define('NCURSES_COLOR_WHITE', 7);
define('NCURSES_KEY_DOWN', 258);
define('NCURSES_KEY_UP', 259);
define('NCURSES_KEY_LEFT', 260);
define('NCURSES_KEY_RIGHT', 261);
define('NCURSES_KEY_HOME', 262);
define('NCURSES_KEY_END', 360);
define('NCURSES_KEY_BACKSPACE', 263);
define('NCURSES_KEY_MOUSE', 409);
define('NCURSES_KEY_F0', 264);
define('NCURSES_KEY_F1', 265);
define('NCURSES_KEY_F2', 266);
define('NCURSES_KEY_F3', 267);
define('NCURSES_KEY_F4', 268);
define('NCURSES_KEY_F5', 269);
define('NCURSES_KEY_F6', 270);
define('NCURSES_KEY_F7', 271);
define('NCURSES_KEY_F8', 272);
define('NCURSES_KEY_F9', 273);
define('NCURSES_KEY_F10', 274);
define('NCURSES_KEY_F11', 275);
define('NCURSES_KEY_F12', 276);
define('NCURSES_KEY_DL', 328);
define('NCURSES_KEY_IL', 329);
define('NCURSES_KEY_DC', 330);
define('NCURSES_KEY_IC', 331);
define('NCURSES_KEY_EIC', 332);
define('NCURSES_KEY_CLEAR', 333);
define('NCURSES_KEY_EOS', 334);
define('NCURSES_KEY_EOL', 335);
define('NCURSES_KEY_SF', 336);
define('NCURSES_KEY_SR', 337);
define('NCURSES_KEY_NPAGE', 338);
define('NCURSES_KEY_PPAGE', 339);
define('NCURSES_KEY_STAB', 340);
define('NCURSES_KEY_CTAB', 341);
define('NCURSES_KEY_CATAB', 342);
define('NCURSES_KEY_ENTER', 343);
define('NCURSES_KEY_SRESET', 344);
define('NCURSES_KEY_RESET', 345);
define('NCURSES_KEY_PRINT', 346);
define('NCURSES_KEY_LL', 347);
define('NCURSES_KEY_A1', 348);
define('NCURSES_KEY_A3', 349);
define('NCURSES_KEY_B2', 350);
define('NCURSES_KEY_C1', 351);
define('NCURSES_KEY_C3', 352);
define('NCURSES_KEY_BTAB', 353);
define('NCURSES_KEY_BEG', 354);
define('NCURSES_KEY_CANCEL', 355);
define('NCURSES_KEY_CLOSE', 356);
define('NCURSES_KEY_COMMAND', 357);
define('NCURSES_KEY_COPY', 358);
define('NCURSES_KEY_CREATE', 359);
define('NCURSES_KEY_EXIT', 361);
define('NCURSES_KEY_FIND', 362);
define('NCURSES_KEY_HELP', 363);
define('NCURSES_KEY_MARK', 364);
define('NCURSES_KEY_MESSAGE', 365);
define('NCURSES_KEY_MOVE', 366);
define('NCURSES_KEY_NEXT', 367);
define('NCURSES_KEY_OPEN', 368);
define('NCURSES_KEY_OPTIONS', 369);
define('NCURSES_KEY_PREVIOUS', 370);
define('NCURSES_KEY_REDO', 371);
define('NCURSES_KEY_REFERENCE', 372);
define('NCURSES_KEY_REFRESH', 373);
define('NCURSES_KEY_REPLACE', 374);
define('NCURSES_KEY_RESTART', 375);
define('NCURSES_KEY_RESUME', 376);
define('NCURSES_KEY_SAVE', 377);
define('NCURSES_KEY_SBEG', 378);
define('NCURSES_KEY_SCANCEL', 379);
define('NCURSES_KEY_SCOMMAND', 380);
define('NCURSES_KEY_SCOPY', 381);
define('NCURSES_KEY_SCREATE', 382);
define('NCURSES_KEY_SDC', 383);
define('NCURSES_KEY_SDL', 384);
define('NCURSES_KEY_SELECT', 385);
define('NCURSES_KEY_SEND', 386);
define('NCURSES_KEY_SEOL', 387);
define('NCURSES_KEY_SEXIT', 388);
define('NCURSES_KEY_SFIND', 389);
define('NCURSES_KEY_SHELP', 390);
define('NCURSES_KEY_SHOME', 391);
define('NCURSES_KEY_SIC', 392);
define('NCURSES_KEY_SLEFT', 393);
define('NCURSES_KEY_SMESSAGE', 394);
define('NCURSES_KEY_SMOVE', 395);
define('NCURSES_KEY_SNEXT', 396);
define('NCURSES_KEY_SOPTIONS', 397);
define('NCURSES_KEY_SPREVIOUS', 398);
define('NCURSES_KEY_SPRINT', 399);
define('NCURSES_KEY_SREDO', 400);
define('NCURSES_KEY_SREPLACE', 401);
define('NCURSES_KEY_SRIGHT', 402);
define('NCURSES_KEY_SRSUME', 403);
define('NCURSES_KEY_SSAVE', 404);
define('NCURSES_KEY_SSUSPEND', 405);
define('NCURSES_KEY_SUNDO', 406);
define('NCURSES_KEY_SUSPEND', 407);
define('NCURSES_KEY_UNDO', 408);
define('NCURSES_KEY_RESIZE', 410);
define('NCURSES_A_NORMAL', 0);
define('NCURSES_A_STANDOUT', 65536);
define('NCURSES_A_UNDERLINE', 131072);
define('NCURSES_A_REVERSE', 262144);
define('NCURSES_A_BLINK', 524288);
define('NCURSES_A_DIM', 1048576);
define('NCURSES_A_BOLD', 2097152);
define('NCURSES_A_PROTECT', 16777216);
define('NCURSES_A_INVIS', 8388608);
define('NCURSES_A_ALTCHARSET', 4194304);
define('NCURSES_A_CHARTEXT', 255);
define('NCURSES_BUTTON1_PRESSED', 2);
define('NCURSES_BUTTON1_RELEASED', 1);
define('NCURSES_BUTTON1_CLICKED', 4);
define('NCURSES_BUTTON1_DOUBLE_CLICKED', 8);
define('NCURSES_BUTTON1_TRIPLE_CLICKED', 16);
define('NCURSES_BUTTON2_PRESSED', 128);
define('NCURSES_BUTTON2_RELEASED', 64);
define('NCURSES_BUTTON2_CLICKED', 256);
define('NCURSES_BUTTON2_DOUBLE_CLICKED', 512);
define('NCURSES_BUTTON2_TRIPLE_CLICKED', 1024);
define('NCURSES_BUTTON3_PRESSED', 8192);
define('NCURSES_BUTTON3_RELEASED', 4096);
define('NCURSES_BUTTON3_CLICKED', 16384);
define('NCURSES_BUTTON3_DOUBLE_CLICKED', 32768);
define('NCURSES_BUTTON3_TRIPLE_CLICKED', 65536);
define('NCURSES_BUTTON4_PRESSED', 524288);
define('NCURSES_BUTTON4_RELEASED', 262144);
define('NCURSES_BUTTON4_CLICKED', 1048576);
define('NCURSES_BUTTON4_DOUBLE_CLICKED', 2097152);
define('NCURSES_BUTTON4_TRIPLE_CLICKED', 4194304);
define('NCURSES_BUTTON_SHIFT', 33554432);
define('NCURSES_BUTTON_CTRL', 16777216);
define('NCURSES_BUTTON_ALT', 67108864);
define('NCURSES_ALL_MOUSE_EVENTS', 134217727);
define('NCURSES_REPORT_MOUSE_POSITION', 134217728);


