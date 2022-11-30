<?php

use JetBrains\PhpStorm\Pure;




class Gmagick
{
public const COLOR_BLACK = 0;
public const COLOR_BLUE = 0;
public const COLOR_CYAN = 0;
public const COLOR_GREEN = 0;
public const COLOR_RED = 0;
public const COLOR_YELLOW = 0;
public const COLOR_MAGENTA = 0;
public const COLOR_OPACITY = 0;
public const COLOR_ALPHA = 0;
public const COLOR_FUZZ = 0;
public const GMAGICK_EXTNUM = 0;
public const COMPOSITE_DEFAULT = 0;
public const COMPOSITE_UNDEFINED = 0;
public const COMPOSITE_NO = 0;
public const COMPOSITE_ADD = 0;
public const COMPOSITE_ATOP = 0;
public const COMPOSITE_BUMPMAP = 0;
public const COMPOSITE_CLEAR = 0;
public const COMPOSITE_COLORIZE = 0;
public const COMPOSITE_COPYBLACK = 0;
public const COMPOSITE_COPYBLUE = 0;
public const COMPOSITE_COPY = 0;
public const COMPOSITE_COPYCYAN = 0;
public const COMPOSITE_COPYGREEN = 0;
public const COMPOSITE_COPYMAGENTA = 0;
public const COMPOSITE_COPYOPACITY = 0;
public const COMPOSITE_COPYRED = 0;
public const COMPOSITE_COPYYELLOW = 0;
public const COMPOSITE_DARKEN = 0;
public const COMPOSITE_DIFFERENCE = 0;
public const COMPOSITE_DISPLACE = 0;
public const COMPOSITE_DISSOLVE = 0;
public const COMPOSITE_HUE = 0;
public const COMPOSITE_IN = 0;
public const COMPOSITE_LIGHTEN = 0;
public const COMPOSITE_LUMINIZE = 0;
public const COMPOSITE_MINUS = 0;
public const COMPOSITE_MODULATE = 0;
public const COMPOSITE_MULTIPLY = 0;
public const COMPOSITE_OUT = 0;
public const COMPOSITE_OVER = 0;
public const COMPOSITE_OVERLAY = 0;
public const COMPOSITE_PLUS = 0;
public const COMPOSITE_REPLACE = 0;
public const COMPOSITE_SATURATE = 0;
public const COMPOSITE_SCREEN = 0;
public const COMPOSITE_SUBTRACT = 0;
public const COMPOSITE_THRESHOLD = 0;
public const COMPOSITE_XOR = 0;
public const COMPOSITE_DIVIDE = 0;
public const COMPOSITE_HARDLIGHT = 0;
public const COMPOSITE_EXCLUSION = 0;
public const COMPOSITE_COLORDODGE = 0;
public const COMPOSITE_COLORBURN = 0;
public const COMPOSITE_SOFTLIGHT = 0;
public const COMPOSITE_LINEARBURN = 0;
public const COMPOSITE_LINEARDODGE = 0;
public const COMPOSITE_LINEARLIGHT = 0;
public const COMPOSITE_VIVIDLIGHT = 0;
public const COMPOSITE_PINLIGHT = 0;
public const COMPOSITE_HARDMIX = 0;
public const MONTAGEMODE_FRAME = 0;
public const MONTAGEMODE_UNFRAME = 0;
public const MONTAGEMODE_CONCATENATE = 0;
public const STYLE_NORMAL = 0;
public const STYLE_ITALIC = 0;
public const STYLE_OBLIQUE = 0;
public const STYLE_ANY = 0;
public const FILTER_UNDEFINED = 0;
public const FILTER_POINT = 0;
public const FILTER_BOX = 0;
public const FILTER_TRIANGLE = 0;
public const FILTER_HERMITE = 0;
public const FILTER_HANNING = 0;
public const FILTER_HAMMING = 0;
public const FILTER_BLACKMAN = 0;
public const FILTER_GAUSSIAN = 0;
public const FILTER_QUADRATIC = 0;
public const FILTER_CUBIC = 0;
public const FILTER_CATROM = 0;
public const FILTER_MITCHELL = 0;
public const FILTER_LANCZOS = 0;
public const FILTER_BESSEL = 0;
public const FILTER_SINC = 0;
public const IMGTYPE_UNDEFINED = 0;
public const IMGTYPE_BILEVEL = 0;
public const IMGTYPE_GRAYSCALE = 0;
public const IMGTYPE_GRAYSCALEMATTE = 0;
public const IMGTYPE_PALETTE = 0;
public const IMGTYPE_PALETTEMATTE = 0;
public const IMGTYPE_TRUECOLOR = 0;
public const IMGTYPE_TRUECOLORMATTE = 0;
public const IMGTYPE_COLORSEPARATION = 0;
public const IMGTYPE_COLORSEPARATIONMATTE = 0;
public const IMGTYPE_OPTIMIZE = 0;
public const RESOLUTION_UNDEFINED = 0;
public const RESOLUTION_PIXELSPERINCH = 0;
public const RESOLUTION_PIXELSPERCENTIMETER = 0;
public const COMPRESSION_UNDEFINED = 0;
public const COMPRESSION_NO = 0;
public const COMPRESSION_BZIP = 0;
public const COMPRESSION_FAX = 0;
public const COMPRESSION_GROUP4 = 0;
public const COMPRESSION_JPEG = 0;
public const COMPRESSION_LOSSLESSJPEG = 0;
public const COMPRESSION_LZW = 0;
public const COMPRESSION_RLE = 0;
public const COMPRESSION_ZIP = 0;
public const COMPRESSION_GROUP3 = 0;
public const COMPRESSION_LZMA = 0;
public const COMPRESSION_JPEG2000 = 0;
public const COMPRESSION_JBIG1 = 0;
public const COMPRESSION_JBIG2 = 0;
public const INTERLACE_NONE = 0;
public const INTERLACE_LINE = 0;
public const INTERLACE_PLANE = 0;
public const INTERLACE_PARTITION = 0;
public const PAINT_POINT = 0;
public const PAINT_REPLACE = 0;
public const PAINT_FLOODFILL = 0;
public const PAINT_FILLTOBORDER = 0;
public const PAINT_RESET = 0;
public const GRAVITY_NORTHWEST = 0;
public const GRAVITY_NORTH = 0;
public const GRAVITY_NORTHEAST = 0;
public const GRAVITY_WEST = 0;
public const GRAVITY_CENTER = 0;
public const GRAVITY_EAST = 0;
public const GRAVITY_SOUTHWEST = 0;
public const GRAVITY_SOUTH = 0;
public const GRAVITY_SOUTHEAST = 0;
public const STRETCH_NORMAL = 0;
public const STRETCH_ULTRACONDENSED = 0;
public const STRETCH_CONDENSED = 0;
public const STRETCH_SEMICONDENSED = 0;
public const STRETCH_SEMIEXPANDED = 0;
public const STRETCH_EXPANDED = 0;
public const STRETCH_EXTRAEXPANDED = 0;
public const STRETCH_ULTRAEXPANDED = 0;
public const STRETCH_ANY = 0;
public const STRETCH_EXTRACONDENSED = 0;
public const ALIGN_UNDEFINED = 0;
public const ALIGN_LEFT = 0;
public const ALIGN_CENTER = 0;
public const ALIGN_RIGHT = 0;
public const DECORATION_NO = 0;
public const DECORATION_UNDERLINE = 0;
public const DECORATION_OVERLINE = 0;
public const DECORATION_LINETROUGH = 0;
public const NOISE_UNIFORM = 0;
public const NOISE_GAUSSIAN = 0;
public const NOISE_MULTIPLICATIVEGAUSSIAN = 0;
public const NOISE_IMPULSE = 0;
public const NOISE_LAPLACIAN = 0;
public const NOISE_POISSON = 0;
public const NOISE_RANDOM = 0;
public const CHANNEL_UNDEFINED = 0;
public const CHANNEL_RED = 0;
public const CHANNEL_GRAY = 0;
public const CHANNEL_CYAN = 0;
public const CHANNEL_GREEN = 0;
public const CHANNEL_MAGENTA = 0;
public const CHANNEL_BLUE = 0;
public const CHANNEL_YELLOW = 0;
public const CHANNEL_OPACITY = 0;
public const CHANNEL_MATTE = 0;
public const CHANNEL_BLACK = 0;
public const CHANNEL_INDEX = 0;
public const CHANNEL_ALL = 0;
public const CHANNEL_DEFAULT = 0;
public const METRIC_UNDEFINED = 0;
public const METRIC_MEANABSOLUTEERROR = 0;
public const METRIC_MEANSQUAREERROR = 0;
public const METRIC_PEAKABSOLUTEERROR = 0;
public const METRIC_PEAKSIGNALTONOISERATIO = 0;
public const METRIC_ROOTMEANSQUAREDERROR = 0;
public const PIXEL_CHAR = 0;
public const PIXEL_DOUBLE = 0;
public const PIXEL_FLOAT = 0;
public const PIXEL_INTEGER = 0;
public const PIXEL_LONG = 0;
public const PIXEL_SHORT = 0;
public const COLORSPACE_UNDEFINED = 0;
public const COLORSPACE_RGB = 0;
public const COLORSPACE_GRAY = 0;
public const COLORSPACE_TRANSPARENT = 0;
public const COLORSPACE_OHTA = 0;
public const COLORSPACE_LAB = 0;
public const COLORSPACE_XYZ = 0;
public const COLORSPACE_YCBCR = 0;
public const COLORSPACE_YCC = 0;
public const COLORSPACE_YIQ = 0;
public const COLORSPACE_YPBPR = 0;
public const COLORSPACE_YUV = 0;
public const COLORSPACE_CMYK = 0;
public const COLORSPACE_SRGB = 0;
public const COLORSPACE_HSL = 0;
public const COLORSPACE_HWB = 0;
public const COLORSPACE_REC601LUMA = 0;
public const COLORSPACE_REC709LUMA = 0;
public const COLORSPACE_CINEONLOGRGB = 0;
public const COLORSPACE_REC601YCBCR = 0;
public const COLORSPACE_REC709YCBCR = 0;
public const VIRTUALPIXELMETHOD_UNDEFINED = 0;
public const VIRTUALPIXELMETHOD_CONSTANT = 0;
public const VIRTUALPIXELMETHOD_EDGE = 0;
public const VIRTUALPIXELMETHOD_MIRROR = 0;
public const VIRTUALPIXELMETHOD_TILE = 0;
public const PREVIEW_UNDEFINED = 0;
public const PREVIEW_ROTATE = 0;
public const PREVIEW_SHEAR = 0;
public const PREVIEW_ROLL = 0;
public const PREVIEW_HUE = 0;
public const PREVIEW_SATURATION = 0;
public const PREVIEW_BRIGHTNESS = 0;
public const PREVIEW_GAMMA = 0;
public const PREVIEW_SPIFF = 0;
public const PREVIEW_DULL = 0;
public const PREVIEW_GRAYSCALE = 0;
public const PREVIEW_QUANTIZE = 0;
public const PREVIEW_DESPECKLE = 0;
public const PREVIEW_REDUCENOISE = 0;
public const PREVIEW_ADDNOISE = 0;
public const PREVIEW_SHARPEN = 0;
public const PREVIEW_BLUR = 0;
public const PREVIEW_THRESHOLD = 0;
public const PREVIEW_EDGEDETECT = 0;
public const PREVIEW_SPREAD = 0;
public const PREVIEW_SOLARIZE = 0;
public const PREVIEW_SHADE = 0;
public const PREVIEW_RAISE = 0;
public const PREVIEW_SEGMENT = 0;
public const PREVIEW_SWIRL = 0;
public const PREVIEW_IMPLODE = 0;
public const PREVIEW_WAVE = 0;
public const PREVIEW_OILPAINT = 0;
public const PREVIEW_CHARCOALDRAWING = 0;
public const PREVIEW_JPEG = 0;
public const RENDERINGINTENT_UNDEFINED = 0;
public const RENDERINGINTENT_SATURATION = 0;
public const RENDERINGINTENT_PERCEPTUAL = 0;
public const RENDERINGINTENT_ABSOLUTE = 0;
public const RENDERINGINTENT_RELATIVE = 0;
public const INTERLACE_UNDEFINED = 0;
public const INTERLACE_NO = 0;
public const FILLRULE_UNDEFINED = 0;
public const FILLRULE_EVENODD = 0;
public const FILLRULE_NONZERO = 0;
public const PATHUNITS_USERSPACE = 0;
public const PATHUNITS_USERSPACEONUSE = 0;
public const PATHUNITS_OBJECTBOUNDINGBOX = 0;
public const LINECAP_UNDEFINED = 0;
public const LINECAP_BUTT = 0;
public const LINECAP_ROUND = 0;
public const LINECAP_SQUARE = 0;
public const LINEJOIN_UNDEFINED = 0;
public const LINEJOIN_MITER = 0;
public const LINEJOIN_ROUND = 0;
public const LINEJOIN_BEVEL = 0;
public const RESOURCETYPE_UNDEFINED = 0;
public const RESOURCETYPE_AREA = 0;
public const RESOURCETYPE_DISK = 0;
public const RESOURCETYPE_FILE = 0;
public const RESOURCETYPE_MAP = 0;
public const RESOURCETYPE_MEMORY = 0;
public const RESOURCETYPE_PIXELS = 0;
public const RESOURCETYPE_THREADS = 0;
public const RESOURCETYPE_WIDTH = 0;
public const RESOURCETYPE_HEIGHT = 0;
public const DISPOSE_UNDEFINED = 0;
public const DISPOSE_NONE = 0;
public const DISPOSE_BACKGROUND = 0;
public const DISPOSE_PREVIOUS = 0;
public const ORIENTATION_UNDEFINED = 0;
public const ORIENTATION_TOPLEFT = 0;
public const ORIENTATION_TOPRIGHT = 0;
public const ORIENTATION_BOTTOMRIGHT = 0;
public const ORIENTATION_BOTTOMLEFT = 0;
public const ORIENTATION_LEFTTOP = 0;
public const ORIENTATION_RIGHTTOP = 0;
public const ORIENTATION_RIGHTBOTTOM = 0;
public const ORIENTATION_LEFTBOTTOM = 0;
public const QUANTUM_DEPTH = 0;
public const QUANTUM = 0;
public const VERSION_LIB = 0;
public const VERSION_NUM = 0;
public const VERSION_TXT = '';








public function __construct($filename = null) {}













public function addimage($Gmagick) {}












public function addnoiseimage($NOISE) {}
















public function annotateimage($GmagickDraw, $x, $y, $angle, $text) {}














public function blurimage($radius, $sigma, $channel = null) {}














public function borderimage($color, $width, $height) {}













public function charcoalimage($radius, $sigma) {}















public function chopimage($width, $height, $x, $y) {}










public function clear() {}












public function commentimage($comment) {}















public function compositeimage($source, $COMPOSE, $x, $y) {}















public function cropimage($width, $height, $x, $y) {}













public function cropthumbnailimage($width, $height) {}










public function current() {}













public function cyclecolormapimage($displace) {}











public function deconstructimages() {}










public function despeckleimage() {}










public function destroy() {}












public function drawimage($GmagickDraw) {}













public function edgeimage($radius) {}
















public function embossimage($radius, $sigma) {}










public function enhanceimage() {}










public function equalizeimage() {}










public function flipimage() {}










public function flopimage() {}


















public function frameimage($color, $width, $height, $inner_bevel, $outer_bevel) {}















public function gammaimage($gamma) {}










#[Pure]
public function getcopyright() {}










#[Pure]
public function getfilename() {}










#[Pure]
public function getimagebackgroundcolor() {}










#[Pure]
public function getimageblueprimary() {}










#[Pure]
public function getimagebordercolor() {}












#[Pure]
public function getimagechanneldepth($channel_type) {}










#[Pure]
public function getimagecolors() {}










#[Pure]
public function getimagecolorspace() {}










#[Pure]
public function getimagecompose() {}










#[Pure]
public function getimagedelay() {}










#[Pure]
public function getimagedepth() {}










#[Pure]
public function getimagedispose() {}










#[Pure]
public function getimageextrema() {}










#[Pure]
public function getimagefilename() {}










#[Pure]
public function getimageformat() {}










#[Pure]
public function getimagegamma() {}










#[Pure]
public function getimagegreenprimary() {}










#[Pure]
public function getimageheight() {}










#[Pure]
public function getimagehistogram() {}










#[Pure]
public function getimageindex() {}










#[Pure]
public function getimageinterlacescheme() {}










#[Pure]
public function getimageiterations() {}










#[Pure]
public function getimagematte() {}










#[Pure]
public function getimagemattecolor() {}












#[Pure]
public function getimageprofile($name) {}










#[Pure]
public function getimageredprimary() {}










#[Pure]
public function getimagerenderingintent() {}










#[Pure]
public function getimageresolution() {}










#[Pure]
public function getimagescene() {}










#[Pure]
public function getimagesignature() {}










#[Pure]
public function getimagetype() {}








#[Pure]
public function getimageunits() {}










#[Pure]
public function getimagewhitepoint() {}










#[Pure]
public function getimagewidth() {}










#[Pure]
public function getpackagename() {}










#[Pure]
public function getquantumdepth() {}










#[Pure]
public function getreleasedate() {}










#[Pure]
public function getsamplingfactors() {}










#[Pure]
public function getsize() {}










#[Pure]
public function getversion() {}










public function hasnextimage() {}










public function haspreviousimage() {}












public function implodeimage($radius) {}












public function labelimage($label) {}























public function levelimage($blackPoint, $gamma, $whitePoint, $channel = false) {}










public function magnifyimage() {}













public function mapimage($gmagick, $dither) {}













public function medianfilterimage($radius) {}










public function minifyimage() {}



















public function modulateimage($brightness, $saturation, $hue) {}



















public function motionblurimage($radius, $sigma, $angle) {}















public function newimage($width, $height, $background, $format = null) {}










public function nextimage() {}












public function normalizeimage($channel = null) {}















public function oilpaintimage($radius) {}












public function previousimage() {}

















public function profileimage($name, $profile) {}





























public function quantizeimage($numColors, $colorspace, $treeDepth, $dither, $measureError) {}





























public function quantizeimages($numColors, $colorspace, $treeDepth, $dither, $measureError) {}













public function queryfontmetrics($draw, $text) {}












public function queryfonts($pattern = '*') {}












public function queryformats($pattern = '*') {}













public function radialblurimage($angle, $channel = Gmagick::CHANNEL_DEFAULT) {}



















public function raiseimage($width, $height, $x, $y, $raise) {}














public function read($filename) {}












public function readimage($filename) {}













public function readimageblob($imageContents, $filename = null) {}













public function readimagefile($fp, $filename = null) {}

















public function reducenoiseimage($radius) {}










public function removeimage() {}












public function removeimageprofile($name) {}















public function resampleimage($xResolution, $yResolution, $filter, $blur) {}
















public function resizeimage($width, $height, $filter, $blur, $fit = false) {}













public function rollimage($x, $y) {}















public function rotateimage($color, $degrees) {}
















public function scaleimage($width, $height, $fit = false) {}
















public function separateimagechannel($channel) {}












public function setCompressionQuality($quality = 75) {}












public function setfilename($filename) {}












public function setimagebackgroundcolor($color) {}













public function setimageblueprimary($x, $y) {}












public function setimagebordercolor(GmagickPixel $color) {}














public function setimagechanneldepth($channel, $depth) {}















public function setimagecolorspace($colorspace) {}












public function setimagecompose($composite) {}












public function setimagedelay($delay) {}












public function setimagedepth($depth) {}












public function setimagedispose($disposeType) {}












public function setimagefilename($filename) {}












public function setimageformat($imageFormat) {}












public function setimagegamma($gamma) {}













public function setimagegreenprimary($x, $y) {}












public function setimageindex($index) {}












public function setimageinterlacescheme($interlace) {}












public function setimageiterations($iterations) {}
















public function setimageprofile($name, $profile) {}













public function setimageredprimary($x, $y) {}













public function setimagerenderingintent($rendering_intent) {}













public function setimageresolution($xResolution, $yResolution) {}












public function setimagescene($scene) {}














public function setimagetype($imgType) {}













public function setimageunits($resolution) {}













public function setimagewhitepoint($x, $y) {}













public function setsamplingfactors($factors) {}















public function setsize($columns, $rows) {}



















public function shearimage($color, $xShear, $yShear) {}
















public function solarizeimage($threshold) {}














public function spreadimage($radius) {}










public function stripimage() {}















public function swirlimage($degrees) {}


















public function thumbnailimage($width, $height, $fit = false) {}















public function trimimage($fuzz) {}

















public function write($filename) {}
















public function writeimage($filename, $all_frames = false) {}
}




class GmagickDraw
{











public function annotate($x, $y, $text) {}















public function arc($sx, $sy, $ex, $ey, $sd, $ed) {}










public function bezier(array $coordinate_array) {}















public function ellipse($ox, $oy, $rx, $ry, $start, $end) {}








#[Pure]
public function getfillcolor() {}








#[Pure]
public function getfillopacity() {}








#[Pure]
public function getfont() {}








#[Pure]
public function getfontsize() {}








#[Pure]
public function getfontstyle() {}








#[Pure]
public function getfontweight() {}








#[Pure]
public function getstrokecolor() {}








#[Pure]
public function getstrokeopacity() {}








#[Pure]
public function getstrokewidth() {}








#[Pure]
public function gettextdecoration() {}








#[Pure]
public function gettextencoding() {}













public function line($sx, $sy, $ex, $ey) {}











public function point($x, $y) {}










public function polygon(array $coordinates) {}










public function polyline(array $coordinate_array) {}













public function rectangle($x1, $y1, $x2, $y2) {}










public function rotate($degrees) {}















public function roundrectangle($x1, $y1, $x2, $y2, $rx, $ry) {}











public function scale($x, $y) {}










public function setfillcolor($color) {}










public function setfillopacity($fill_opacity) {}










public function setfont($font) {}










public function setfontsize($pointsize) {}












public function setfontstyle($style) {}










public function setfontweight($weight) {}










public function setstrokecolor($color) {}










public function setstrokeopacity($stroke_opacity) {}










public function setstrokewidth($width) {}











public function settextdecoration($decoration) {}














public function settextencoding($encoding) {}
}

class GmagickException extends \Exception {}




class GmagickPixel
{









public function __construct($color = null) {}















#[Pure]
public function getcolor($as_array = null, $normalize_array = null) {}










#[Pure]
public function getcolorcount() {}












#[Pure]
public function getcolorvalue($color) {}













public function setcolor($color) {}














public function setcolorvalue($color, $value) {}
}

class GmagickPixelException extends \Exception {}
