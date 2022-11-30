<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Pure;















































































#[Pure]
function gd_info(): array {}































function imagearc(GdImage $image, int $center_x, int $center_y, int $width, int $height, int $start_angle, int $end_angle, int $color): bool {}























function imageellipse(GdImage $image, int $center_x, int $center_y, int $width, int $height, int $color): bool {}





















function imagechar(
GdImage $image,
#[LanguageLevelTypeAware(['8.1' => 'GdFont|int'], default: 'int')] $font,
int $x,
int $y,
string $char,
int $color
): bool {}





















function imagecharup(
GdImage $image,
#[LanguageLevelTypeAware(['8.1' => 'GdFont|int'], default: 'int')] $font,
int $x,
int $y,
string $char,
int $color
): bool {}













#[Pure]
function imagecolorat(GdImage $image, int $x, int $y): int|false {}










function imagecolorallocate(GdImage $image, int $red, int $green, int $blue): int|false {}












function imagepalettecopy(GdImage $dst, GdImage $src): void {}











#[Pure]
function imagecreatefromstring(string $data): GdImage|false {}











#[Pure]
function imagecolorclosest(GdImage $image, int $red, int $green, int $blue): int {}











#[Pure]
function imagecolorclosesthwb(GdImage $image, int $red, int $green, int $blue): int {}










function imagecolordeallocate(GdImage $image, int $color): bool {}










#[Pure]
function imagecolorresolve(GdImage $image, int $red, int $green, int $blue): int {}











#[Pure]
function imagecolorexact(GdImage $image, int $red, int $green, int $blue): int {}
















#[LanguageLevelTypeAware(['8.2' => 'null|false'], default: 'null|bool')]
function imagecolorset(GdImage $image, int $color, int $red, int $green, int $blue, int $alpha = 0): ?bool {}














function imagecolortransparent(GdImage $image, ?int $color = null): int {}











#[Pure]
function imagecolorstotal(GdImage $image): int {}











#[Pure]
#[LanguageLevelTypeAware(['8.0' => 'array'], default: 'array|false')]
function imagecolorsforindex(GdImage $image, int $color) {}






























function imagecopy(GdImage $dst_image, GdImage $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_width, int $src_height): bool {}





































function imagecopymerge(GdImage $dst_image, GdImage $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_width, int $src_height, int $pct): bool {}





































function imagecopymergegray(GdImage $dst_image, GdImage $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_width, int $src_height, int $pct): bool {}
































function imagecopyresized(GdImage $dst_image, GdImage $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $dst_width, int $dst_height, int $src_width, int $src_height): bool {}












#[Pure]
function imagecreate(int $width, int $height): GdImage|false {}












#[Pure]
function imagecreatetruecolor(int $width, int $height): GdImage|false {}








#[Pure]
function imageistruecolor(GdImage $image): bool {}















function imagetruecolortopalette(GdImage $image, bool $dither, int $num_colors): bool {}










function imagesetthickness(GdImage $image, int $thickness): bool {}


































function imagefilledarc(GdImage $image, int $center_x, int $center_y, int $width, int $height, int $start_angle, int $end_angle, int $color, int $style): bool {}























function imagefilledellipse(GdImage $image, int $center_x, int $center_y, int $width, int $height, int $color): bool {}











function imagealphablending(GdImage $image, bool $enable): bool {}










function imagesavealpha(GdImage $image, bool $enable): bool {}





















function imagecolorallocatealpha(GdImage $image, int $red, int $green, int $blue, int $alpha): int|false {}





















#[Pure]
function imagecolorresolvealpha(GdImage $image, int $red, int $green, int $blue, int $alpha): int {}






















#[Pure]
function imagecolorclosestalpha(GdImage $image, int $red, int $green, int $blue, int $alpha): int {}























#[Pure]
#[LanguageLevelTypeAware(['8.0' => 'int'], default: 'int|false')]
function imagecolorexactalpha(GdImage $image, int $red, int $green, int $blue, int $alpha) {}
































function imagecopyresampled(GdImage $dst_image, GdImage $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $dst_width, int $dst_height, int $src_width, int $src_height): bool {}
















function imagerotate(GdImage $image, float $angle, int $background_color, bool $ignore_transparent = false): GdImage|false {}











function imageantialias(GdImage $image, bool $enable): bool {}










function imagesettile(GdImage $image, GdImage $tile): bool {}










function imagesetbrush(GdImage $image, GdImage $brush): bool {}












function imagesetstyle(GdImage $image, array $style): bool {}









function imagecreatefrompng(string $filename): GdImage|false {}








function imagecreatefromavif(string $filename): GdImage|false {}









function imagecreatefromgif(string $filename): GdImage|false {}









function imagecreatefromjpeg(string $filename): GdImage|false {}









function imagecreatefromwbmp(string $filename): GdImage|false {}










function imagecreatefromwebp(string $filename): GdImage|false {}









function imagecreatefromxbm(string $filename): GdImage|false {}









function imagecreatefromxpm(string $filename): GdImage|false {}









function imagecreatefromgd(string $filename): GdImage|false {}









function imagecreatefromgd2(string $filename): GdImage|false {}





















function imagecreatefromgd2part(string $filename, int $x, int $y, int $width, int $height): GdImage|false {}

























function imagepng(GdImage $image, $file = null, int $quality = -1, int $filters = -1): bool {}















function imagewebp($image, $to = null, $quality = 80): bool {}











function imagegif(GdImage $image, $file = null): bool {}




















function imagejpeg($image, $filename = null, $quality = null): bool {}
















function imagewbmp(GdImage $image, $file = null, ?int $foreground_color = null): bool {}












function imagegd(GdImage $image, ?string $file = null): bool {}



















function imagegd2(GdImage $image, ?string $file = null, int $chunk_size = null, int $mode = null): bool {}







function imagedestroy(GdImage $image): bool {}













function imagegammacorrect(GdImage $image, float $input_gamma, float $output_gamma): bool {}

















function imagefill(GdImage $image, int $x, int $y, int $color): bool {}


















function imagefilledpolygon(
GdImage $image,
array $points,
#[Deprecated(since: "8.1")] int $num_points_or_color,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] ?int $color,
#[PhpStormStubsElementAvailable(from: '8.0')] ?int $color = null
): bool {}























function imagefilledrectangle(GdImage $image, int $x1, int $y1, int $x2, int $y2, int $color): bool {}





















function imagefilltoborder(GdImage $image, int $x, int $y, int $border_color, int $color): bool {}







#[Pure]
function imagefontwidth(#[LanguageLevelTypeAware(['8.1' => 'GdFont|int'], default: 'int')] $font): int {}







#[Pure]
function imagefontheight(#[LanguageLevelTypeAware(['8.1' => 'GdFont|int'], default: 'int')] $font): int {}












function imageinterlace(GdImage $image, ?bool $enable = null): bool {}























function imageline(GdImage $image, int $x1, int $y1, int $x2, int $y2, int $color): bool {}




















































#[LanguageLevelTypeAware(['8.1' => 'GdFont|false'], default: 'int|false')]
function imageloadfont(string $filename) {}

































function imagepolygon(
GdImage $image,
array $points,
int $num_points_or_color,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] ?int $color,
#[PhpStormStubsElementAvailable(from: '8.0')] ?int $color = null
): bool {}
























function imagerectangle(GdImage $image, int $x1, int $y1, int $x2, int $y2, int $color): bool {}

















function imagesetpixel(GdImage $image, int $x, int $y, int $color): bool {}





















function imagestring(
GdImage $image,
#[LanguageLevelTypeAware(['8.1' => 'GdFont|int'], default: 'int')] $font,
int $x,
int $y,
string $string,
int $color
): bool {}





















function imagestringup(
GdImage $image,
#[LanguageLevelTypeAware(['8.1' => 'GdFont|int'], default: 'int')] $font,
int $x,
int $y,
string $string,
int $color
): bool {}








#[Pure]
function imagesx(GdImage $image): int {}








#[Pure]
function imagesy(GdImage $image): int {}

























#[Deprecated("Use combination of imagesetstyle() and imageline() instead")]
function imagedashedline(GdImage $image, int $x1, int $y1, int $x2, int $y2, int $color): bool {}

































































#[Pure]
function imagettfbbox($size, $angle, $font_filename, $text) {}



























































































function imagettftext($image, $size, $angle, $x, $y, $color, $font_filename, $text) {}














































































#[Pure]
function imageftbbox($size, $angle, $font_filename, $text, $extrainfo = null) {}





















































































































function imagefttext($image, $size, $angle, $x, $y, $color, $font_filename, $text, $extrainfo = null) {}

/**
@removed







*/
function imagepsloadfont($filename) {}

/**
@removed






*/
function imagepsfreefont($font_index) {}

/**
@removed












*/
function imagepsencodefont($font_index, $encodingfile) {}

/**
@removed









*/
function imagepsextendfont($font_index, $extend) {}

/**
@removed









*/
function imagepsslantfont($font_index, $slant) {}

/**
@removed




































































*/
function imagepstext($image, $text, $font_index, $size, $foreground, $background, $x, $y, $space = null, $tightness = null, $angle = null, $antialias_steps = null) {}

/**
@removed



























*/
function imagepsbbox($text, $font, $size) {}








#[Pure]
function imagetypes(): int {}

/**
@removed



















*/
#[Deprecated(reason: "Use imagecreatefromjpeg() and imagewbmp() instead", since: "7.2")]
function jpeg2wbmp($jpegname, $wbmpname, $dest_height, $dest_width, $threshold) {}

/**
@removed




















*/
#[Deprecated("Use imagecreatefrompng() and imagewbmp() instead", since: "7.2")]
function png2wbmp($pngname, $wbmpname, $dest_height, $dest_width, $threshold) {}

/**
@removed












*/
#[Deprecated(replacement: "imagewbmp(%parametersList%)", since: "7.3")]
function image2wbmp($image, $filename = null, $threshold = null) {}












function imagelayereffect(GdImage $image, int $effect): bool {}













function imagecolormatch(GdImage $image1, GdImage $image2): bool {}
















function imagexbm(GdImage $image, ?string $filename, ?int $foreground_color = null): bool {}












function imagefilter(
GdImage $image,
int $filter,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $arg1 = null,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $arg2 = null,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $arg3 = null,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $arg4 = null,
#[PhpStormStubsElementAvailable(from: '8.0')] ...$args
): bool {}
















function imageconvolution(GdImage $image, array $matrix, float $divisor, float $offset): bool {}









function imageresolution(GdImage $image, ?int $resolution_x = null, ?int $resolution_y = null): array|bool {}













function imagesetclip(GdImage $image, int $x1, int $y1, int $x2, int $y2): bool {}
















function imagegetclip(GdImage $image): array {}


















function imageopenpolygon(
GdImage $image,
array $points,
#[Deprecated(since: "8.1")] int $num_points_or_color,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] ?int $color,
#[PhpStormStubsElementAvailable(from: '8.0')] ?int $color = null
): bool {}









function imagecreatefrombmp(string $filename): GdImage|false {}














function imagebmp(GdImage $image, $file = null, bool $compressed = true): bool {}





function imagecreatefromtga(string $filename): GdImage|false {}








#[Pure]
function imagegrabscreen() {}










#[Pure]
function imagegrabwindow($handle, $client_area = null) {}









#[Pure]
function imagegetinterpolation(GdImage $image): int {}





define('IMG_GIF', 1);





define('IMG_JPG', 2);








define('IMG_JPEG', 2);





define('IMG_PNG', 4);





define('IMG_WBMP', 8);





define('IMG_XPM', 16);







define('IMG_WEBP', 32);






define('IMG_BMP', 64);






define('IMG_COLOR_TILED', -5);






define('IMG_COLOR_STYLED', -2);






define('IMG_COLOR_BRUSHED', -3);






define('IMG_COLOR_STYLEDBRUSHED', -4);






define('IMG_COLOR_TRANSPARENT', -6);








define('IMG_ARC_ROUNDED', 0);





define('IMG_ARC_PIE', 0);





define('IMG_ARC_CHORD', 1);





define('IMG_ARC_NOFILL', 2);





define('IMG_ARC_EDGED', 4);





define('IMG_GD2_RAW', 1);





define('IMG_GD2_COMPRESSED', 2);





define('IMG_EFFECT_REPLACE', 0);





define('IMG_EFFECT_ALPHABLEND', 1);





define('IMG_EFFECT_NORMAL', 2);





define('IMG_EFFECT_OVERLAY', 3);






define('IMG_EFFECT_MULTIPLY', 4);






define('GD_BUNDLED', 1);





define('IMG_FILTER_NEGATE', 0);





define('IMG_FILTER_GRAYSCALE', 1);





define('IMG_FILTER_BRIGHTNESS', 2);





define('IMG_FILTER_CONTRAST', 3);





define('IMG_FILTER_COLORIZE', 4);





define('IMG_FILTER_EDGEDETECT', 5);





define('IMG_FILTER_GAUSSIAN_BLUR', 7);





define('IMG_FILTER_SELECTIVE_BLUR', 8);





define('IMG_FILTER_EMBOSS', 6);





define('IMG_FILTER_MEAN_REMOVAL', 9);





define('IMG_FILTER_SMOOTH', 10);





define('IMG_FILTER_PIXELATE', 11);






define('IMG_FILTER_SCATTER', 12);






define('GD_VERSION', "2.0.35");






define('GD_MAJOR_VERSION', 2);






define('GD_MINOR_VERSION', 0);






define('GD_RELEASE_VERSION', 35);






define('GD_EXTRA_VERSION', "");





define('PNG_NO_FILTER', 0);





define('PNG_FILTER_NONE', 8);





define('PNG_FILTER_SUB', 16);





define('PNG_FILTER_UP', 32);





define('PNG_FILTER_AVG', 64);





define('PNG_FILTER_PAETH', 128);





define('PNG_ALL_FILTERS', 248);






define('IMG_AFFINE_TRANSLATE', 0);






define('IMG_AFFINE_SCALE', 1);






define('IMG_AFFINE_ROTATE', 2);






define('IMG_AFFINE_SHEAR_HORIZONTAL', 3);






define('IMG_AFFINE_SHEAR_VERTICAL', 4);







define('IMG_CROP_DEFAULT', 0);






define('IMG_CROP_TRANSPARENT', 1);






define('IMG_CROP_BLACK', 2);






define('IMG_CROP_WHITE', 3);






define('IMG_CROP_SIDES', 4);






define('IMG_CROP_THRESHOLD', 5);






define('IMG_FLIP_BOTH', 3);






define('IMG_FLIP_HORIZONTAL', 1);






define('IMG_FLIP_VERTICAL', 2);






define('IMG_BELL', 1);






define('IMG_BESSEL', 2);






define('IMG_BICUBIC', 4);






define('IMG_BICUBIC_FIXED', 5);






define('IMG_BILINEAR_FIXED', 3);






define('IMG_BLACKMAN', 6);






define('IMG_BOX', 7);






define('IMG_BSPLINE', 8);






define('IMG_CATMULLROM', 9);






define('IMG_GAUSSIAN', 10);






define('IMG_GENERALIZED_CUBIC', 11);






define('IMG_HERMITE', 12);






define('IMG_HAMMING', 13);






define('IMG_HANNING', 14);






define('IMG_MITCHELL', 15);






define('IMG_POWER', 17);






define('IMG_QUADRATIC', 18);






define('IMG_SINC', 19);






define('IMG_NEAREST_NEIGHBOUR', 16);






define('IMG_WEIGHTED4', 21);






define('IMG_TRIANGLE', 20);

define('IMG_TGA', 128);




define('IMG_AVIF', 256);




define('IMG_WEBP_LOSSLESS', 101);











function imageavif(GdImage $image, string|null $file = null, int $quality = -1, int $speed = -1): bool {}










function imageaffine(GdImage $image, array $affine, ?array $clip = null): GdImage|false {}









function imageaffinematrixconcat(array $matrix1, array $matrix2): array|false {}









function imageaffinematrixget(
int $type,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $options = null,
#[PhpStormStubsElementAvailable(from: '8.0')] $options
): array|false {}











function imagecrop(GdImage $image, array $rectangle): GdImage|false {}




















function imagecropauto(GdImage $image, int $mode = IMG_CROP_DEFAULT, float $threshold = .5, int $color = -1): GdImage|false {}









































function imageflip(GdImage $image, int $mode): bool {}










function imagepalettetotruecolor(GdImage $image): bool {}













function imagescale(GdImage $image, int $width, int $height = -1, int $mode = IMG_BILINEAR_FIXED): GdImage|false {}














































































function imagesetinterpolation(GdImage $image, int $method = IMG_BILINEAR_FIXED): bool {}




final class GdImage
{



private function __construct() {}

private function __clone() {}
}
