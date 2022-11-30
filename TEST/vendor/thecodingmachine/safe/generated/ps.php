<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\PsException;
function ps_add_launchlink($psdoc, float $llx, float $lly, float $urx, float $ury, string $filename) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_add_launchlink($psdoc, $llx, $lly, $urx, $ury, $filename);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_add_locallink($psdoc, float $llx, float $lly, float $urx, float $ury, int $page, string $dest) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_add_locallink($psdoc, $llx, $lly, $urx, $ury, $page, $dest);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_add_note($psdoc, float $llx, float $lly, float $urx, float $ury, string $contents, string $title, string $icon, int $open) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_add_note($psdoc, $llx, $lly, $urx, $ury, $contents, $title, $icon, $open);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_add_pdflink($psdoc, float $llx, float $lly, float $urx, float $ury, string $filename, int $page, string $dest) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_add_pdflink($psdoc, $llx, $lly, $urx, $ury, $filename, $page, $dest);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_add_weblink($psdoc, float $llx, float $lly, float $urx, float $ury, string $url) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_add_weblink($psdoc, $llx, $lly, $urx, $ury, $url);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_arc($psdoc, float $x, float $y, float $radius, float $alpha, float $beta) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_arc($psdoc, $x, $y, $radius, $alpha, $beta);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_arcn($psdoc, float $x, float $y, float $radius, float $alpha, float $beta) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_arcn($psdoc, $x, $y, $radius, $alpha, $beta);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_begin_page($psdoc, float $width, float $height) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_begin_page($psdoc, $width, $height);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_begin_pattern($psdoc, float $width, float $height, float $xstep, float $ystep, int $painttype) : int
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_begin_pattern($psdoc, $width, $height, $xstep, $ystep, $painttype);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
    return $result;
}
function ps_begin_template($psdoc, float $width, float $height) : int
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_begin_template($psdoc, $width, $height);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
    return $result;
}
function ps_circle($psdoc, float $x, float $y, float $radius) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_circle($psdoc, $x, $y, $radius);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_clip($psdoc) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_clip($psdoc);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_close_image($psdoc, int $imageid) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_close_image($psdoc, $imageid);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_close($psdoc) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_close($psdoc);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_closepath_stroke($psdoc) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_closepath_stroke($psdoc);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_closepath($psdoc) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_closepath($psdoc);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_continue_text($psdoc, string $text) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_continue_text($psdoc, $text);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_curveto($psdoc, float $x1, float $y1, float $x2, float $y2, float $x3, float $y3) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_curveto($psdoc, $x1, $y1, $x2, $y2, $x3, $y3);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_delete($psdoc) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_delete($psdoc);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_end_page($psdoc) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_end_page($psdoc);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_end_pattern($psdoc) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_end_pattern($psdoc);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_end_template($psdoc) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_end_template($psdoc);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_fill_stroke($psdoc) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_fill_stroke($psdoc);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_fill($psdoc) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_fill($psdoc);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_get_parameter($psdoc, string $name, float $modifier = null) : string
{
    \error_clear_last();
    if ($modifier !== null) {
        $result = \_HumbugBox9658796bb9f0\ps_get_parameter($psdoc, $name, $modifier);
    } else {
        $result = \_HumbugBox9658796bb9f0\ps_get_parameter($psdoc, $name);
    }
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
    return $result;
}
function ps_hyphenate($psdoc, string $text) : array
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_hyphenate($psdoc, $text);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
    return $result;
}
function ps_include_file($psdoc, string $file) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_include_file($psdoc, $file);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_lineto($psdoc, float $x, float $y) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_lineto($psdoc, $x, $y);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_moveto($psdoc, float $x, float $y) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_moveto($psdoc, $x, $y);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_new()
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_new();
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
    return $result;
}
function ps_open_file($psdoc, string $filename = null) : void
{
    \error_clear_last();
    if ($filename !== null) {
        $result = \_HumbugBox9658796bb9f0\ps_open_file($psdoc, $filename);
    } else {
        $result = \_HumbugBox9658796bb9f0\ps_open_file($psdoc);
    }
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_place_image($psdoc, int $imageid, float $x, float $y, float $scale) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_place_image($psdoc, $imageid, $x, $y, $scale);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_rect($psdoc, float $x, float $y, float $width, float $height) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_rect($psdoc, $x, $y, $width, $height);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_restore($psdoc) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_restore($psdoc);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_rotate($psdoc, float $rot) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_rotate($psdoc, $rot);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_save($psdoc) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_save($psdoc);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_scale($psdoc, float $x, float $y) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_scale($psdoc, $x, $y);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_set_border_color($psdoc, float $red, float $green, float $blue) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_set_border_color($psdoc, $red, $green, $blue);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_set_border_dash($psdoc, float $black, float $white) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_set_border_dash($psdoc, $black, $white);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_set_border_style($psdoc, string $style, float $width) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_set_border_style($psdoc, $style, $width);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_set_info($p, string $key, string $val) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_set_info($p, $key, $val);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_set_parameter($psdoc, string $name, string $value) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_set_parameter($psdoc, $name, $value);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_set_text_pos($psdoc, float $x, float $y) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_set_text_pos($psdoc, $x, $y);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_set_value($psdoc, string $name, float $value) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_set_value($psdoc, $name, $value);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_setcolor($psdoc, string $type, string $colorspace, float $c1, float $c2, float $c3, float $c4) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_setcolor($psdoc, $type, $colorspace, $c1, $c2, $c3, $c4);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_setdash($psdoc, float $on, float $off) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_setdash($psdoc, $on, $off);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_setflat($psdoc, float $value) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_setflat($psdoc, $value);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_setfont($psdoc, int $fontid, float $size) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_setfont($psdoc, $fontid, $size);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_setgray($psdoc, float $gray) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_setgray($psdoc, $gray);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_setlinecap($psdoc, int $type) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_setlinecap($psdoc, $type);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_setlinejoin($psdoc, int $type) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_setlinejoin($psdoc, $type);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_setlinewidth($psdoc, float $width) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_setlinewidth($psdoc, $width);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_setmiterlimit($psdoc, float $value) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_setmiterlimit($psdoc, $value);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_setoverprintmode($psdoc, int $mode) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_setoverprintmode($psdoc, $mode);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_setpolydash($psdoc, float $arr) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_setpolydash($psdoc, $arr);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_shading_pattern($psdoc, int $shadingid, string $optlist) : int
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_shading_pattern($psdoc, $shadingid, $optlist);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
    return $result;
}
function ps_shading($psdoc, string $type, float $x0, float $y0, float $x1, float $y1, float $c1, float $c2, float $c3, float $c4, string $optlist) : int
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_shading($psdoc, $type, $x0, $y0, $x1, $y1, $c1, $c2, $c3, $c4, $optlist);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
    return $result;
}
function ps_shfill($psdoc, int $shadingid) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_shfill($psdoc, $shadingid);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_show_xy($psdoc, string $text, float $x, float $y) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_show_xy($psdoc, $text, $x, $y);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_show_xy2($psdoc, string $text, int $len, float $xcoor, float $ycoor) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_show_xy2($psdoc, $text, $len, $xcoor, $ycoor);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_show($psdoc, string $text) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_show($psdoc, $text);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_show2($psdoc, string $text, int $len) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_show2($psdoc, $text, $len);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_stroke($psdoc) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_stroke($psdoc);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_symbol($psdoc, int $ord) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_symbol($psdoc, $ord);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
function ps_translate($psdoc, float $x, float $y) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ps_translate($psdoc, $x, $y);
    if ($result === \false) {
        throw PsException::createFromPhpError();
    }
}
