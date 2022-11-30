<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\ImageException;
function getimagesize(string $filename, ?array &$image_info = null) : array
{
    \error_clear_last();
    $safeResult = \getimagesize($filename, $image_info);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function image_type_to_extension(int $image_type, bool $include_dot = \true) : string
{
    \error_clear_last();
    $safeResult = \image_type_to_extension($image_type, $include_dot);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function image2wbmp($image, ?string $filename = null, int $foreground = null) : void
{
    \error_clear_last();
    if ($foreground !== null) {
        $safeResult = \image2wbmp($image, $filename, $foreground);
    } elseif ($filename !== null) {
        $safeResult = \image2wbmp($image, $filename);
    } else {
        $safeResult = \image2wbmp($image);
    }
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageaffine($image, array $affine, array $clip = null)
{
    \error_clear_last();
    if ($clip !== null) {
        $safeResult = \imageaffine($image, $affine, $clip);
    } else {
        $safeResult = \imageaffine($image, $affine);
    }
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imageaffinematrixconcat(array $matrix1, array $matrix2) : array
{
    \error_clear_last();
    $safeResult = \imageaffinematrixconcat($matrix1, $matrix2);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imageaffinematrixget(int $type, $options) : array
{
    \error_clear_last();
    $safeResult = \imageaffinematrixget($type, $options);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagealphablending($image, bool $enable) : void
{
    \error_clear_last();
    $safeResult = \imagealphablending($image, $enable);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageantialias($image, bool $enable) : void
{
    \error_clear_last();
    $safeResult = \imageantialias($image, $enable);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagearc($image, int $center_x, int $center_y, int $width, int $height, int $start_angle, int $end_angle, int $color) : void
{
    \error_clear_last();
    $safeResult = \imagearc($image, $center_x, $center_y, $width, $height, $start_angle, $end_angle, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageavif(\GdImage $image, $file = null, int $quality = -1, int $speed = -1) : void
{
    \error_clear_last();
    if ($speed !== -1) {
        $safeResult = \imageavif($image, $file, $quality, $speed);
    } elseif ($quality !== -1) {
        $safeResult = \imageavif($image, $file, $quality);
    } elseif ($file !== null) {
        $safeResult = \imageavif($image, $file);
    } else {
        $safeResult = \imageavif($image);
    }
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagebmp($image, $file = null, bool $compressed = \true) : void
{
    \error_clear_last();
    if ($compressed !== \true) {
        $safeResult = \imagebmp($image, $file, $compressed);
    } elseif ($file !== null) {
        $safeResult = \imagebmp($image, $file);
    } else {
        $safeResult = \imagebmp($image);
    }
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagechar($image, int $font, int $x, int $y, string $char, int $color) : void
{
    \error_clear_last();
    $safeResult = \imagechar($image, $font, $x, $y, $char, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecharup($image, int $font, int $x, int $y, string $char, int $color) : void
{
    \error_clear_last();
    $safeResult = \imagecharup($image, $font, $x, $y, $char, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecolorat($image, int $x, int $y) : int
{
    \error_clear_last();
    $safeResult = \imagecolorat($image, $x, $y);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecolordeallocate($image, int $color) : void
{
    \error_clear_last();
    $safeResult = \imagecolordeallocate($image, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecolormatch($image1, $image2) : void
{
    \error_clear_last();
    $safeResult = \imagecolormatch($image1, $image2);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecolorset($image, int $color, int $red, int $green, int $blue, int $alpha = 0) : void
{
    \error_clear_last();
    $safeResult = \imagecolorset($image, $color, $red, $green, $blue, $alpha);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageconvolution($image, array $matrix, float $divisor, float $offset) : void
{
    \error_clear_last();
    $safeResult = \imageconvolution($image, $matrix, $divisor, $offset);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecopy($dst_image, $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_width, int $src_height) : void
{
    \error_clear_last();
    $safeResult = \imagecopy($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $src_width, $src_height);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecopymerge($dst_image, $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_width, int $src_height, int $pct) : void
{
    \error_clear_last();
    $safeResult = \imagecopymerge($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $src_width, $src_height, $pct);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecopymergegray($dst_image, $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_width, int $src_height, int $pct) : void
{
    \error_clear_last();
    $safeResult = \imagecopymergegray($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $src_width, $src_height, $pct);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecopyresampled($dst_image, $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $dst_width, int $dst_height, int $src_width, int $src_height) : void
{
    \error_clear_last();
    $safeResult = \imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_width, $dst_height, $src_width, $src_height);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecopyresized($dst_image, $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $dst_width, int $dst_height, int $src_width, int $src_height) : void
{
    \error_clear_last();
    $safeResult = \imagecopyresized($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_width, $dst_height, $src_width, $src_height);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecreate(int $width, int $height)
{
    \error_clear_last();
    $safeResult = \imagecreate($width, $height);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecreatefromavif(string $filename)
{
    \error_clear_last();
    $safeResult = \imagecreatefromavif($filename);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecreatefrombmp(string $filename)
{
    \error_clear_last();
    $safeResult = \imagecreatefrombmp($filename);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecreatefromgd(string $filename)
{
    \error_clear_last();
    $safeResult = \imagecreatefromgd($filename);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecreatefromgd2(string $filename)
{
    \error_clear_last();
    $safeResult = \imagecreatefromgd2($filename);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecreatefromgd2part(string $filename, int $x, int $y, int $width, int $height)
{
    \error_clear_last();
    $safeResult = \imagecreatefromgd2part($filename, $x, $y, $width, $height);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecreatefromgif(string $filename)
{
    \error_clear_last();
    $safeResult = \imagecreatefromgif($filename);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecreatefromjpeg(string $filename)
{
    \error_clear_last();
    $safeResult = \imagecreatefromjpeg($filename);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecreatefrompng(string $filename)
{
    \error_clear_last();
    $safeResult = \imagecreatefrompng($filename);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecreatefromstring(string $data)
{
    \error_clear_last();
    $safeResult = \imagecreatefromstring($data);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecreatefromtga(string $filename)
{
    \error_clear_last();
    $safeResult = \imagecreatefromtga($filename);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecreatefromwbmp(string $filename)
{
    \error_clear_last();
    $safeResult = \imagecreatefromwbmp($filename);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecreatefromwebp(string $filename)
{
    \error_clear_last();
    $safeResult = \imagecreatefromwebp($filename);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecreatefromxbm(string $filename)
{
    \error_clear_last();
    $safeResult = \imagecreatefromxbm($filename);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecreatefromxpm(string $filename)
{
    \error_clear_last();
    $safeResult = \imagecreatefromxpm($filename);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecreatetruecolor(int $width, int $height)
{
    \error_clear_last();
    $safeResult = \imagecreatetruecolor($width, $height);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecrop($image, array $rectangle)
{
    \error_clear_last();
    $safeResult = \imagecrop($image, $rectangle);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagecropauto($image, int $mode = \IMG_CROP_DEFAULT, float $threshold = 0.5, int $color = -1)
{
    \error_clear_last();
    $safeResult = \imagecropauto($image, $mode, $threshold, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagedashedline($image, int $x1, int $y1, int $x2, int $y2, int $color) : void
{
    \error_clear_last();
    $safeResult = \imagedashedline($image, $x1, $y1, $x2, $y2, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagedestroy($image) : void
{
    \error_clear_last();
    $safeResult = \imagedestroy($image);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageellipse($image, int $center_x, int $center_y, int $width, int $height, int $color) : void
{
    \error_clear_last();
    $safeResult = \imageellipse($image, $center_x, $center_y, $width, $height, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagefill($image, int $x, int $y, int $color) : void
{
    \error_clear_last();
    $safeResult = \imagefill($image, $x, $y, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagefilledarc($image, int $center_x, int $center_y, int $width, int $height, int $start_angle, int $end_angle, int $color, int $style) : void
{
    \error_clear_last();
    $safeResult = \imagefilledarc($image, $center_x, $center_y, $width, $height, $start_angle, $end_angle, $color, $style);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagefilledellipse($image, int $center_x, int $center_y, int $width, int $height, int $color) : void
{
    \error_clear_last();
    $safeResult = \imagefilledellipse($image, $center_x, $center_y, $width, $height, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagefilledrectangle($image, int $x1, int $y1, int $x2, int $y2, int $color) : void
{
    \error_clear_last();
    $safeResult = \imagefilledrectangle($image, $x1, $y1, $x2, $y2, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagefilltoborder($image, int $x, int $y, int $border_color, int $color) : void
{
    \error_clear_last();
    $safeResult = \imagefilltoborder($image, $x, $y, $border_color, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagefilter($image, int $filter, int ...$args) : void
{
    \error_clear_last();
    if ($args !== []) {
        $safeResult = \imagefilter($image, $filter, ...$args);
    } else {
        $safeResult = \imagefilter($image, $filter);
    }
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageflip($image, int $mode) : void
{
    \error_clear_last();
    $safeResult = \imageflip($image, $mode);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageftbbox(float $size, float $angle, string $font_filename, string $string, array $options = []) : array
{
    \error_clear_last();
    $safeResult = \imageftbbox($size, $angle, $font_filename, $string, $options);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagefttext($image, float $size, float $angle, int $x, int $y, int $color, string $font_filename, string $text, array $options = []) : array
{
    \error_clear_last();
    $safeResult = \imagefttext($image, $size, $angle, $x, $y, $color, $font_filename, $text, $options);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagegammacorrect($image, float $input_gamma, float $output_gamma) : void
{
    \error_clear_last();
    $safeResult = \imagegammacorrect($image, $input_gamma, $output_gamma);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagegd($image, $file = null) : void
{
    \error_clear_last();
    if ($file !== null) {
        $safeResult = \imagegd($image, $file);
    } else {
        $safeResult = \imagegd($image);
    }
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagegd2($image, $file = null, int $chunk_size = 128, int $mode = \IMG_GD2_RAW) : void
{
    \error_clear_last();
    if ($mode !== \IMG_GD2_RAW) {
        $safeResult = \imagegd2($image, $file, $chunk_size, $mode);
    } elseif ($chunk_size !== 128) {
        $safeResult = \imagegd2($image, $file, $chunk_size);
    } elseif ($file !== null) {
        $safeResult = \imagegd2($image, $file);
    } else {
        $safeResult = \imagegd2($image);
    }
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagegif($image, $file = null) : void
{
    \error_clear_last();
    if ($file !== null) {
        $safeResult = \imagegif($image, $file);
    } else {
        $safeResult = \imagegif($image);
    }
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagegrabscreen()
{
    \error_clear_last();
    $safeResult = \imagegrabscreen();
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagegrabwindow(int $handle, bool $client_area = \false) : \GdImage
{
    \error_clear_last();
    $safeResult = \imagegrabwindow($handle, $client_area);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagejpeg($image, $file = null, int $quality = -1) : void
{
    \error_clear_last();
    if ($quality !== -1) {
        $safeResult = \imagejpeg($image, $file, $quality);
    } elseif ($file !== null) {
        $safeResult = \imagejpeg($image, $file);
    } else {
        $safeResult = \imagejpeg($image);
    }
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagelayereffect($image, int $effect) : void
{
    \error_clear_last();
    $safeResult = \imagelayereffect($image, $effect);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageline($image, int $x1, int $y1, int $x2, int $y2, int $color) : void
{
    \error_clear_last();
    $safeResult = \imageline($image, $x1, $y1, $x2, $y2, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageloadfont(string $filename) : int
{
    \error_clear_last();
    $safeResult = \imageloadfont($filename);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagepng($image, $file = null, int $quality = -1, int $filters = -1) : void
{
    \error_clear_last();
    if ($filters !== -1) {
        $safeResult = \imagepng($image, $file, $quality, $filters);
    } elseif ($quality !== -1) {
        $safeResult = \imagepng($image, $file, $quality);
    } elseif ($file !== null) {
        $safeResult = \imagepng($image, $file);
    } else {
        $safeResult = \imagepng($image);
    }
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagerectangle($image, int $x1, int $y1, int $x2, int $y2, int $color) : void
{
    \error_clear_last();
    $safeResult = \imagerectangle($image, $x1, $y1, $x2, $y2, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageresolution($image, int $resolution_x = null, int $resolution_y = null)
{
    \error_clear_last();
    if ($resolution_y !== null) {
        $safeResult = \imageresolution($image, $resolution_x, $resolution_y);
    } elseif ($resolution_x !== null) {
        $safeResult = \imageresolution($image, $resolution_x);
    } else {
        $safeResult = \imageresolution($image);
    }
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagerotate($image, float $angle, int $background_color, bool $ignore_transparent = \false)
{
    \error_clear_last();
    $safeResult = \imagerotate($image, $angle, $background_color, $ignore_transparent);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagesavealpha($image, bool $enable) : void
{
    \error_clear_last();
    $safeResult = \imagesavealpha($image, $enable);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagescale($image, int $width, int $height = -1, int $mode = \IMG_BILINEAR_FIXED)
{
    \error_clear_last();
    $safeResult = \imagescale($image, $width, $height, $mode);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagesetbrush($image, $brush) : void
{
    \error_clear_last();
    $safeResult = \imagesetbrush($image, $brush);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagesetclip($image, int $x1, int $y1, int $x2, int $y2) : void
{
    \error_clear_last();
    $safeResult = \imagesetclip($image, $x1, $y1, $x2, $y2);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagesetinterpolation($image, int $method = \IMG_BILINEAR_FIXED) : void
{
    \error_clear_last();
    $safeResult = \imagesetinterpolation($image, $method);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagesetpixel($image, int $x, int $y, int $color) : void
{
    \error_clear_last();
    $safeResult = \imagesetpixel($image, $x, $y, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagesetstyle($image, array $style) : void
{
    \error_clear_last();
    $safeResult = \imagesetstyle($image, $style);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagesetthickness($image, int $thickness) : void
{
    \error_clear_last();
    $safeResult = \imagesetthickness($image, $thickness);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagesettile($image, $tile) : void
{
    \error_clear_last();
    $safeResult = \imagesettile($image, $tile);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagestring($image, int $font, int $x, int $y, string $string, int $color) : void
{
    \error_clear_last();
    $safeResult = \imagestring($image, $font, $x, $y, $string, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagestringup($image, int $font, int $x, int $y, string $string, int $color) : void
{
    \error_clear_last();
    $safeResult = \imagestringup($image, $font, $x, $y, $string, $color);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagesx($image) : int
{
    \error_clear_last();
    $safeResult = \imagesx($image);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagesy($image) : int
{
    \error_clear_last();
    $safeResult = \imagesy($image);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagetruecolortopalette($image, bool $dither, int $num_colors) : void
{
    \error_clear_last();
    $safeResult = \imagetruecolortopalette($image, $dither, $num_colors);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagettfbbox(float $size, float $angle, string $font_filename, string $string, array $options = []) : array
{
    \error_clear_last();
    $safeResult = \imagettfbbox($size, $angle, $font_filename, $string, $options);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagettftext($image, float $size, float $angle, int $x, int $y, int $color, string $font_filename, string $text, array $options = []) : array
{
    \error_clear_last();
    $safeResult = \imagettftext($image, $size, $angle, $x, $y, $color, $font_filename, $text, $options);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function imagewbmp($image, $file = null, int $foreground_color = null) : void
{
    \error_clear_last();
    if ($foreground_color !== null) {
        $safeResult = \imagewbmp($image, $file, $foreground_color);
    } elseif ($file !== null) {
        $safeResult = \imagewbmp($image, $file);
    } else {
        $safeResult = \imagewbmp($image);
    }
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagewebp($image, $file = null, int $quality = -1) : void
{
    \error_clear_last();
    if ($quality !== -1) {
        $safeResult = \imagewebp($image, $file, $quality);
    } elseif ($file !== null) {
        $safeResult = \imagewebp($image, $file);
    } else {
        $safeResult = \imagewebp($image);
    }
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagexbm($image, $filename, int $foreground_color = null) : void
{
    \error_clear_last();
    if ($foreground_color !== null) {
        $safeResult = \imagexbm($image, $filename, $foreground_color);
    } else {
        $safeResult = \imagexbm($image, $filename);
    }
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function iptcembed(string $iptc_data, string $filename, int $spool = 0)
{
    \error_clear_last();
    $safeResult = \iptcembed($iptc_data, $filename, $spool);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function iptcparse(string $iptc_block) : array
{
    \error_clear_last();
    $safeResult = \iptcparse($iptc_block);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
    return $safeResult;
}
function jpeg2wbmp(string $jpegname, string $wbmpname, int $dest_height, int $dest_width, int $threshold) : void
{
    \error_clear_last();
    $safeResult = \jpeg2wbmp($jpegname, $wbmpname, $dest_height, $dest_width, $threshold);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
function png2wbmp(string $pngname, string $wbmpname, int $dest_height, int $dest_width, int $threshold) : void
{
    \error_clear_last();
    $safeResult = \png2wbmp($pngname, $wbmpname, $dest_height, $dest_width, $threshold);
    if ($safeResult === \false) {
        throw ImageException::createFromPhpError();
    }
}
