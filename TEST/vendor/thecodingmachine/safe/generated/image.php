<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\ImageException;
function getimagesize(string $filename, ?array &$image_info = null) : array
{
    \error_clear_last();
    $result = \getimagesize($filename, $image_info);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function image_type_to_extension(int $image_type, bool $include_dot = \true) : string
{
    \error_clear_last();
    $result = \image_type_to_extension($image_type, $include_dot);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function image2wbmp($image, ?string $filename = null, int $foreground = null) : void
{
    \error_clear_last();
    if ($foreground !== null) {
        $result = \image2wbmp($image, $filename, $foreground);
    } elseif ($filename !== null) {
        $result = \image2wbmp($image, $filename);
    } else {
        $result = \image2wbmp($image);
    }
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageaffine($image, array $affine, array $clip = null)
{
    \error_clear_last();
    if ($clip !== null) {
        $result = \imageaffine($image, $affine, $clip);
    } else {
        $result = \imageaffine($image, $affine);
    }
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imageaffinematrixconcat(array $matrix1, array $matrix2) : array
{
    \error_clear_last();
    $result = \imageaffinematrixconcat($matrix1, $matrix2);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imageaffinematrixget(int $type, $options) : array
{
    \error_clear_last();
    $result = \imageaffinematrixget($type, $options);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagealphablending($image, bool $enable) : void
{
    \error_clear_last();
    $result = \imagealphablending($image, $enable);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageantialias($image, bool $enable) : void
{
    \error_clear_last();
    $result = \imageantialias($image, $enable);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagearc($image, int $center_x, int $center_y, int $width, int $height, int $start_angle, int $end_angle, int $color) : void
{
    \error_clear_last();
    $result = \imagearc($image, $center_x, $center_y, $width, $height, $start_angle, $end_angle, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageavif(\GdImage $image, $file = null, int $quality = -1, int $speed = -1) : void
{
    \error_clear_last();
    if ($speed !== -1) {
        $result = \imageavif($image, $file, $quality, $speed);
    } elseif ($quality !== -1) {
        $result = \imageavif($image, $file, $quality);
    } elseif ($file !== null) {
        $result = \imageavif($image, $file);
    } else {
        $result = \imageavif($image);
    }
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagebmp($image, $file = null, bool $compressed = \true) : void
{
    \error_clear_last();
    if ($compressed !== \true) {
        $result = \imagebmp($image, $file, $compressed);
    } elseif ($file !== null) {
        $result = \imagebmp($image, $file);
    } else {
        $result = \imagebmp($image);
    }
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagechar($image, int $font, int $x, int $y, string $char, int $color) : void
{
    \error_clear_last();
    $result = \imagechar($image, $font, $x, $y, $char, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecharup($image, int $font, int $x, int $y, string $char, int $color) : void
{
    \error_clear_last();
    $result = \imagecharup($image, $font, $x, $y, $char, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecolorat($image, int $x, int $y) : int
{
    \error_clear_last();
    $result = \imagecolorat($image, $x, $y);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecolordeallocate($image, int $color) : void
{
    \error_clear_last();
    $result = \imagecolordeallocate($image, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecolormatch($image1, $image2) : void
{
    \error_clear_last();
    $result = \imagecolormatch($image1, $image2);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecolorset($image, int $color, int $red, int $green, int $blue, int $alpha = 0) : void
{
    \error_clear_last();
    $result = \imagecolorset($image, $color, $red, $green, $blue, $alpha);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageconvolution($image, array $matrix, float $divisor, float $offset) : void
{
    \error_clear_last();
    $result = \imageconvolution($image, $matrix, $divisor, $offset);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecopy($dst_image, $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_width, int $src_height) : void
{
    \error_clear_last();
    $result = \imagecopy($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $src_width, $src_height);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecopymerge($dst_image, $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_width, int $src_height, int $pct) : void
{
    \error_clear_last();
    $result = \imagecopymerge($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $src_width, $src_height, $pct);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecopymergegray($dst_image, $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_width, int $src_height, int $pct) : void
{
    \error_clear_last();
    $result = \imagecopymergegray($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $src_width, $src_height, $pct);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecopyresampled($dst_image, $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $dst_width, int $dst_height, int $src_width, int $src_height) : void
{
    \error_clear_last();
    $result = \imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_width, $dst_height, $src_width, $src_height);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecopyresized($dst_image, $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $dst_width, int $dst_height, int $src_width, int $src_height) : void
{
    \error_clear_last();
    $result = \imagecopyresized($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_width, $dst_height, $src_width, $src_height);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagecreate(int $width, int $height)
{
    \error_clear_last();
    $result = \imagecreate($width, $height);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecreatefromavif(string $filename)
{
    \error_clear_last();
    $result = \imagecreatefromavif($filename);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecreatefrombmp(string $filename)
{
    \error_clear_last();
    $result = \imagecreatefrombmp($filename);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecreatefromgd(string $filename)
{
    \error_clear_last();
    $result = \imagecreatefromgd($filename);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecreatefromgd2(string $filename)
{
    \error_clear_last();
    $result = \imagecreatefromgd2($filename);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecreatefromgd2part(string $filename, int $x, int $y, int $width, int $height)
{
    \error_clear_last();
    $result = \imagecreatefromgd2part($filename, $x, $y, $width, $height);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecreatefromgif(string $filename)
{
    \error_clear_last();
    $result = \imagecreatefromgif($filename);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecreatefromjpeg(string $filename)
{
    \error_clear_last();
    $result = \imagecreatefromjpeg($filename);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecreatefrompng(string $filename)
{
    \error_clear_last();
    $result = \imagecreatefrompng($filename);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecreatefromtga(string $filename)
{
    \error_clear_last();
    $result = \imagecreatefromtga($filename);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecreatefromwbmp(string $filename)
{
    \error_clear_last();
    $result = \imagecreatefromwbmp($filename);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecreatefromwebp(string $filename)
{
    \error_clear_last();
    $result = \imagecreatefromwebp($filename);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecreatefromxbm(string $filename)
{
    \error_clear_last();
    $result = \imagecreatefromxbm($filename);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecreatefromxpm(string $filename)
{
    \error_clear_last();
    $result = \imagecreatefromxpm($filename);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecreatetruecolor(int $width, int $height)
{
    \error_clear_last();
    $result = \imagecreatetruecolor($width, $height);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecrop($image, array $rectangle)
{
    \error_clear_last();
    $result = \imagecrop($image, $rectangle);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagecropauto($image, int $mode = \IMG_CROP_DEFAULT, float $threshold = 0.5, int $color = -1)
{
    \error_clear_last();
    $result = \imagecropauto($image, $mode, $threshold, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagedashedline($image, int $x1, int $y1, int $x2, int $y2, int $color) : void
{
    \error_clear_last();
    $result = \imagedashedline($image, $x1, $y1, $x2, $y2, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagedestroy($image) : void
{
    \error_clear_last();
    $result = \imagedestroy($image);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageellipse($image, int $center_x, int $center_y, int $width, int $height, int $color) : void
{
    \error_clear_last();
    $result = \imageellipse($image, $center_x, $center_y, $width, $height, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagefill($image, int $x, int $y, int $color) : void
{
    \error_clear_last();
    $result = \imagefill($image, $x, $y, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagefilledarc($image, int $center_x, int $center_y, int $width, int $height, int $start_angle, int $end_angle, int $color, int $style) : void
{
    \error_clear_last();
    $result = \imagefilledarc($image, $center_x, $center_y, $width, $height, $start_angle, $end_angle, $color, $style);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagefilledellipse($image, int $center_x, int $center_y, int $width, int $height, int $color) : void
{
    \error_clear_last();
    $result = \imagefilledellipse($image, $center_x, $center_y, $width, $height, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagefilledrectangle($image, int $x1, int $y1, int $x2, int $y2, int $color) : void
{
    \error_clear_last();
    $result = \imagefilledrectangle($image, $x1, $y1, $x2, $y2, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagefilltoborder($image, int $x, int $y, int $border_color, int $color) : void
{
    \error_clear_last();
    $result = \imagefilltoborder($image, $x, $y, $border_color, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagefilter($image, int $filter, int ...$args) : void
{
    \error_clear_last();
    if ($args !== []) {
        $result = \imagefilter($image, $filter, ...$args);
    } else {
        $result = \imagefilter($image, $filter);
    }
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageflip($image, int $mode) : void
{
    \error_clear_last();
    $result = \imageflip($image, $mode);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageftbbox(float $size, float $angle, string $font_filename, string $string, array $options = []) : array
{
    \error_clear_last();
    $result = \imageftbbox($size, $angle, $font_filename, $string, $options);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagefttext($image, float $size, float $angle, int $x, int $y, int $color, string $font_filename, string $text, array $options = []) : array
{
    \error_clear_last();
    $result = \imagefttext($image, $size, $angle, $x, $y, $color, $font_filename, $text, $options);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagegammacorrect($image, float $input_gamma, float $output_gamma) : void
{
    \error_clear_last();
    $result = \imagegammacorrect($image, $input_gamma, $output_gamma);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagegd($image, $file = null) : void
{
    \error_clear_last();
    if ($file !== null) {
        $result = \imagegd($image, $file);
    } else {
        $result = \imagegd($image);
    }
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagegd2($image, $file = null, int $chunk_size = 128, int $mode = \IMG_GD2_RAW) : void
{
    \error_clear_last();
    if ($mode !== \IMG_GD2_RAW) {
        $result = \imagegd2($image, $file, $chunk_size, $mode);
    } elseif ($chunk_size !== 128) {
        $result = \imagegd2($image, $file, $chunk_size);
    } elseif ($file !== null) {
        $result = \imagegd2($image, $file);
    } else {
        $result = \imagegd2($image);
    }
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagegif($image, $file = null) : void
{
    \error_clear_last();
    if ($file !== null) {
        $result = \imagegif($image, $file);
    } else {
        $result = \imagegif($image);
    }
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagegrabscreen()
{
    \error_clear_last();
    $result = \imagegrabscreen();
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagegrabwindow(int $handle, bool $client_area = \false) : \GdImage
{
    \error_clear_last();
    $result = \imagegrabwindow($handle, $client_area);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagejpeg($image, $file = null, int $quality = -1) : void
{
    \error_clear_last();
    if ($quality !== -1) {
        $result = \imagejpeg($image, $file, $quality);
    } elseif ($file !== null) {
        $result = \imagejpeg($image, $file);
    } else {
        $result = \imagejpeg($image);
    }
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagelayereffect($image, int $effect) : void
{
    \error_clear_last();
    $result = \imagelayereffect($image, $effect);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageline($image, int $x1, int $y1, int $x2, int $y2, int $color) : void
{
    \error_clear_last();
    $result = \imageline($image, $x1, $y1, $x2, $y2, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageloadfont(string $filename) : int
{
    \error_clear_last();
    $result = \imageloadfont($filename);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagepng($image, $file = null, int $quality = -1, int $filters = -1) : void
{
    \error_clear_last();
    if ($filters !== -1) {
        $result = \imagepng($image, $file, $quality, $filters);
    } elseif ($quality !== -1) {
        $result = \imagepng($image, $file, $quality);
    } elseif ($file !== null) {
        $result = \imagepng($image, $file);
    } else {
        $result = \imagepng($image);
    }
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagerectangle($image, int $x1, int $y1, int $x2, int $y2, int $color) : void
{
    \error_clear_last();
    $result = \imagerectangle($image, $x1, $y1, $x2, $y2, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imageresolution($image, int $resolution_x = null, int $resolution_y = null)
{
    \error_clear_last();
    if ($resolution_y !== null) {
        $result = \imageresolution($image, $resolution_x, $resolution_y);
    } elseif ($resolution_x !== null) {
        $result = \imageresolution($image, $resolution_x);
    } else {
        $result = \imageresolution($image);
    }
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagerotate($image, float $angle, int $background_color, bool $ignore_transparent = \false)
{
    \error_clear_last();
    $result = \imagerotate($image, $angle, $background_color, $ignore_transparent);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagesavealpha($image, bool $enable) : void
{
    \error_clear_last();
    $result = \imagesavealpha($image, $enable);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagescale($image, int $width, int $height = -1, int $mode = \IMG_BILINEAR_FIXED)
{
    \error_clear_last();
    $result = \imagescale($image, $width, $height, $mode);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagesetbrush($image, $brush) : void
{
    \error_clear_last();
    $result = \imagesetbrush($image, $brush);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagesetclip($image, int $x1, int $y1, int $x2, int $y2) : void
{
    \error_clear_last();
    $result = \imagesetclip($image, $x1, $y1, $x2, $y2);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagesetinterpolation($image, int $method = \IMG_BILINEAR_FIXED) : void
{
    \error_clear_last();
    $result = \imagesetinterpolation($image, $method);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagesetpixel($image, int $x, int $y, int $color) : void
{
    \error_clear_last();
    $result = \imagesetpixel($image, $x, $y, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagesetstyle($image, array $style) : void
{
    \error_clear_last();
    $result = \imagesetstyle($image, $style);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagesetthickness($image, int $thickness) : void
{
    \error_clear_last();
    $result = \imagesetthickness($image, $thickness);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagesettile($image, $tile) : void
{
    \error_clear_last();
    $result = \imagesettile($image, $tile);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagestring($image, int $font, int $x, int $y, string $string, int $color) : void
{
    \error_clear_last();
    $result = \imagestring($image, $font, $x, $y, $string, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagestringup($image, int $font, int $x, int $y, string $string, int $color) : void
{
    \error_clear_last();
    $result = \imagestringup($image, $font, $x, $y, $string, $color);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagesx($image) : int
{
    \error_clear_last();
    $result = \imagesx($image);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagesy($image) : int
{
    \error_clear_last();
    $result = \imagesy($image);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagetruecolortopalette($image, bool $dither, int $num_colors) : void
{
    \error_clear_last();
    $result = \imagetruecolortopalette($image, $dither, $num_colors);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagettfbbox(float $size, float $angle, string $font_filename, string $string, array $options = []) : array
{
    \error_clear_last();
    $result = \imagettfbbox($size, $angle, $font_filename, $string, $options);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagettftext($image, float $size, float $angle, int $x, int $y, int $color, string $font_filename, string $text, array $options = []) : array
{
    \error_clear_last();
    $result = \imagettftext($image, $size, $angle, $x, $y, $color, $font_filename, $text, $options);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function imagewbmp($image, $file = null, int $foreground_color = null) : void
{
    \error_clear_last();
    if ($foreground_color !== null) {
        $result = \imagewbmp($image, $file, $foreground_color);
    } elseif ($file !== null) {
        $result = \imagewbmp($image, $file);
    } else {
        $result = \imagewbmp($image);
    }
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagewebp($image, $file = null, int $quality = -1) : void
{
    \error_clear_last();
    if ($quality !== -1) {
        $result = \imagewebp($image, $file, $quality);
    } elseif ($file !== null) {
        $result = \imagewebp($image, $file);
    } else {
        $result = \imagewebp($image);
    }
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function imagexbm($image, $filename, int $foreground_color = null) : void
{
    \error_clear_last();
    if ($foreground_color !== null) {
        $result = \imagexbm($image, $filename, $foreground_color);
    } else {
        $result = \imagexbm($image, $filename);
    }
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function iptcembed(string $iptc_data, string $filename, int $spool = 0)
{
    \error_clear_last();
    $result = \iptcembed($iptc_data, $filename, $spool);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function iptcparse(string $iptc_block) : array
{
    \error_clear_last();
    $result = \iptcparse($iptc_block);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
    return $result;
}
function jpeg2wbmp(string $jpegname, string $wbmpname, int $dest_height, int $dest_width, int $threshold) : void
{
    \error_clear_last();
    $result = \jpeg2wbmp($jpegname, $wbmpname, $dest_height, $dest_width, $threshold);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
function png2wbmp(string $pngname, string $wbmpname, int $dest_height, int $dest_width, int $threshold) : void
{
    \error_clear_last();
    $result = \png2wbmp($pngname, $wbmpname, $dest_height, $dest_width, $threshold);
    if ($result === \false) {
        throw ImageException::createFromPhpError();
    }
}
