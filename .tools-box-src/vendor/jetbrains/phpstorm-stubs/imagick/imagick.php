<?php



use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Pure;

class ImagickException extends Exception {}

class ImagickDrawException extends Exception {}

class ImagickPixelIteratorException extends Exception {}

class ImagickPixelException extends Exception {}

class ImagickKernelException extends Exception {}





class Imagick implements Iterator, Countable
{
public const COLOR_BLACK = 11;
public const COLOR_BLUE = 12;
public const COLOR_CYAN = 13;
public const COLOR_GREEN = 14;
public const COLOR_RED = 15;
public const COLOR_YELLOW = 16;
public const COLOR_MAGENTA = 17;
public const COLOR_OPACITY = 18;
public const COLOR_ALPHA = 19;
public const COLOR_FUZZ = 20;
public const IMAGICK_EXTNUM = 30403;
public const IMAGICK_EXTVER = "3.4.3";
public const QUANTUM_RANGE = 65535;
public const USE_ZEND_MM = 0;
public const COMPOSITE_DEFAULT = 40;
public const COMPOSITE_UNDEFINED = 0;
public const COMPOSITE_NO = 1;
public const COMPOSITE_ADD = 2;
public const COMPOSITE_ATOP = 3;
public const COMPOSITE_BLEND = 4;
public const COMPOSITE_BUMPMAP = 5;
public const COMPOSITE_CLEAR = 7;
public const COMPOSITE_COLORBURN = 8;
public const COMPOSITE_COLORDODGE = 9;
public const COMPOSITE_COLORIZE = 10;
public const COMPOSITE_COPYBLACK = 11;
public const COMPOSITE_COPYBLUE = 12;
public const COMPOSITE_COPY = 13;
public const COMPOSITE_COPYCYAN = 14;
public const COMPOSITE_COPYGREEN = 15;
public const COMPOSITE_COPYMAGENTA = 16;
public const COMPOSITE_COPYOPACITY = 17;
public const COMPOSITE_COPYRED = 18;
public const COMPOSITE_COPYYELLOW = 19;
public const COMPOSITE_DARKEN = 20;
public const COMPOSITE_DSTATOP = 21;
public const COMPOSITE_DST = 22;
public const COMPOSITE_DSTIN = 23;
public const COMPOSITE_DSTOUT = 24;
public const COMPOSITE_DSTOVER = 25;
public const COMPOSITE_DIFFERENCE = 26;
public const COMPOSITE_DISPLACE = 27;
public const COMPOSITE_DISSOLVE = 28;
public const COMPOSITE_EXCLUSION = 29;
public const COMPOSITE_HARDLIGHT = 30;
public const COMPOSITE_HUE = 31;
public const COMPOSITE_IN = 32;
public const COMPOSITE_LIGHTEN = 33;
public const COMPOSITE_LUMINIZE = 35;
public const COMPOSITE_MINUS = 36;
public const COMPOSITE_MODULATE = 37;
public const COMPOSITE_MULTIPLY = 38;
public const COMPOSITE_OUT = 39;
public const COMPOSITE_OVER = 40;
public const COMPOSITE_OVERLAY = 41;
public const COMPOSITE_PLUS = 42;
public const COMPOSITE_REPLACE = 43;
public const COMPOSITE_SATURATE = 44;
public const COMPOSITE_SCREEN = 45;
public const COMPOSITE_SOFTLIGHT = 46;
public const COMPOSITE_SRCATOP = 47;
public const COMPOSITE_SRC = 48;
public const COMPOSITE_SRCIN = 49;
public const COMPOSITE_SRCOUT = 50;
public const COMPOSITE_SRCOVER = 51;
public const COMPOSITE_SUBTRACT = 52;
public const COMPOSITE_THRESHOLD = 53;
public const COMPOSITE_XOR = 54;
public const COMPOSITE_CHANGEMASK = 6;
public const COMPOSITE_LINEARLIGHT = 34;
public const COMPOSITE_DIVIDE = 55;
public const COMPOSITE_DISTORT = 56;
public const COMPOSITE_BLUR = 57;
public const COMPOSITE_PEGTOPLIGHT = 58;
public const COMPOSITE_VIVIDLIGHT = 59;
public const COMPOSITE_PINLIGHT = 60;
public const COMPOSITE_LINEARDODGE = 61;
public const COMPOSITE_LINEARBURN = 62;
public const COMPOSITE_MATHEMATICS = 63;
public const COMPOSITE_MODULUSADD = 2;
public const COMPOSITE_MODULUSSUBTRACT = 52;
public const COMPOSITE_MINUSDST = 36;
public const COMPOSITE_DIVIDEDST = 55;
public const COMPOSITE_DIVIDESRC = 64;
public const COMPOSITE_MINUSSRC = 65;
public const COMPOSITE_DARKENINTENSITY = 66;
public const COMPOSITE_LIGHTENINTENSITY = 67;
public const MONTAGEMODE_FRAME = 1;
public const MONTAGEMODE_UNFRAME = 2;
public const MONTAGEMODE_CONCATENATE = 3;
public const STYLE_NORMAL = 1;
public const STYLE_ITALIC = 2;
public const STYLE_OBLIQUE = 3;
public const STYLE_ANY = 4;
public const FILTER_UNDEFINED = 0;
public const FILTER_POINT = 1;
public const FILTER_BOX = 2;
public const FILTER_TRIANGLE = 3;
public const FILTER_HERMITE = 4;
public const FILTER_HANNING = 5;
public const FILTER_HAMMING = 6;
public const FILTER_BLACKMAN = 7;
public const FILTER_GAUSSIAN = 8;
public const FILTER_QUADRATIC = 9;
public const FILTER_CUBIC = 10;
public const FILTER_CATROM = 11;
public const FILTER_MITCHELL = 12;
public const FILTER_LANCZOS = 22;
public const FILTER_BESSEL = 13;
public const FILTER_SINC = 14;
public const FILTER_KAISER = 16;
public const FILTER_WELSH = 17;
public const FILTER_PARZEN = 18;
public const FILTER_LAGRANGE = 21;
public const FILTER_SENTINEL = 31;
public const FILTER_BOHMAN = 19;
public const FILTER_BARTLETT = 20;
public const FILTER_JINC = 13;
public const FILTER_SINCFAST = 15;
public const FILTER_ROBIDOUX = 26;
public const FILTER_LANCZOSSHARP = 23;
public const FILTER_LANCZOS2 = 24;
public const FILTER_LANCZOS2SHARP = 25;
public const FILTER_ROBIDOUXSHARP = 27;
public const FILTER_COSINE = 28;
public const FILTER_SPLINE = 29;
public const FILTER_LANCZOSRADIUS = 30;
public const IMGTYPE_UNDEFINED = 0;
public const IMGTYPE_BILEVEL = 1;
public const IMGTYPE_GRAYSCALE = 2;
public const IMGTYPE_GRAYSCALEMATTE = 3;
public const IMGTYPE_PALETTE = 4;
public const IMGTYPE_PALETTEMATTE = 5;
public const IMGTYPE_TRUECOLOR = 6;
public const IMGTYPE_TRUECOLORMATTE = 7;
public const IMGTYPE_COLORSEPARATION = 8;
public const IMGTYPE_COLORSEPARATIONMATTE = 9;
public const IMGTYPE_OPTIMIZE = 10;
public const IMGTYPE_PALETTEBILEVELMATTE = 11;
public const RESOLUTION_UNDEFINED = 0;
public const RESOLUTION_PIXELSPERINCH = 1;
public const RESOLUTION_PIXELSPERCENTIMETER = 2;
public const COMPRESSION_UNDEFINED = 0;
public const COMPRESSION_NO = 1;
public const COMPRESSION_BZIP = 2;
public const COMPRESSION_FAX = 6;
public const COMPRESSION_GROUP4 = 7;
public const COMPRESSION_JPEG = 8;
public const COMPRESSION_JPEG2000 = 9;
public const COMPRESSION_LOSSLESSJPEG = 10;
public const COMPRESSION_LZW = 11;
public const COMPRESSION_RLE = 12;
public const COMPRESSION_ZIP = 13;
public const COMPRESSION_DXT1 = 3;
public const COMPRESSION_DXT3 = 4;
public const COMPRESSION_DXT5 = 5;
public const COMPRESSION_ZIPS = 14;
public const COMPRESSION_PIZ = 15;
public const COMPRESSION_PXR24 = 16;
public const COMPRESSION_B44 = 17;
public const COMPRESSION_B44A = 18;
public const COMPRESSION_LZMA = 19;
public const COMPRESSION_JBIG1 = 20;
public const COMPRESSION_JBIG2 = 21;
public const PAINT_POINT = 1;
public const PAINT_REPLACE = 2;
public const PAINT_FLOODFILL = 3;
public const PAINT_FILLTOBORDER = 4;
public const PAINT_RESET = 5;
public const GRAVITY_NORTHWEST = 1;
public const GRAVITY_NORTH = 2;
public const GRAVITY_NORTHEAST = 3;
public const GRAVITY_WEST = 4;
public const GRAVITY_CENTER = 5;
public const GRAVITY_EAST = 6;
public const GRAVITY_SOUTHWEST = 7;
public const GRAVITY_SOUTH = 8;
public const GRAVITY_SOUTHEAST = 9;
public const GRAVITY_FORGET = 0;
public const GRAVITY_STATIC = 10;
public const STRETCH_NORMAL = 1;
public const STRETCH_ULTRACONDENSED = 2;
public const STRETCH_EXTRACONDENSED = 3;
public const STRETCH_CONDENSED = 4;
public const STRETCH_SEMICONDENSED = 5;
public const STRETCH_SEMIEXPANDED = 6;
public const STRETCH_EXPANDED = 7;
public const STRETCH_EXTRAEXPANDED = 8;
public const STRETCH_ULTRAEXPANDED = 9;
public const STRETCH_ANY = 10;
public const ALIGN_UNDEFINED = 0;
public const ALIGN_LEFT = 1;
public const ALIGN_CENTER = 2;
public const ALIGN_RIGHT = 3;
public const DECORATION_NO = 1;
public const DECORATION_UNDERLINE = 2;
public const DECORATION_OVERLINE = 3;
public const DECORATION_LINETROUGH = 4;
public const DECORATION_LINETHROUGH = 4;
public const NOISE_UNIFORM = 1;
public const NOISE_GAUSSIAN = 2;
public const NOISE_MULTIPLICATIVEGAUSSIAN = 3;
public const NOISE_IMPULSE = 4;
public const NOISE_LAPLACIAN = 5;
public const NOISE_POISSON = 6;
public const NOISE_RANDOM = 7;
public const CHANNEL_UNDEFINED = 0;
public const CHANNEL_RED = 1;
public const CHANNEL_GRAY = 1;
public const CHANNEL_CYAN = 1;
public const CHANNEL_GREEN = 2;
public const CHANNEL_MAGENTA = 2;
public const CHANNEL_BLUE = 4;
public const CHANNEL_YELLOW = 4;
public const CHANNEL_ALPHA = 8;
public const CHANNEL_OPACITY = 8;
public const CHANNEL_MATTE = 8;
public const CHANNEL_BLACK = 32;
public const CHANNEL_INDEX = 32;
public const CHANNEL_ALL = 134217727;
public const CHANNEL_DEFAULT = 134217719;
public const CHANNEL_RGBA = 15;
public const CHANNEL_TRUEALPHA = 64;
public const CHANNEL_RGBS = 128;
public const CHANNEL_GRAY_CHANNELS = 128;
public const CHANNEL_SYNC = 256;
public const CHANNEL_COMPOSITES = 47;
public const METRIC_UNDEFINED = 0;
public const METRIC_ABSOLUTEERRORMETRIC = 1;
public const METRIC_MEANABSOLUTEERROR = 2;
public const METRIC_MEANERRORPERPIXELMETRIC = 3;
public const METRIC_MEANSQUAREERROR = 4;
public const METRIC_PEAKABSOLUTEERROR = 5;
public const METRIC_PEAKSIGNALTONOISERATIO = 6;
public const METRIC_ROOTMEANSQUAREDERROR = 7;
public const METRIC_NORMALIZEDCROSSCORRELATIONERRORMETRIC = 8;
public const METRIC_FUZZERROR = 9;
public const PIXEL_CHAR = 1;
public const PIXEL_DOUBLE = 2;
public const PIXEL_FLOAT = 3;
public const PIXEL_INTEGER = 4;
public const PIXEL_LONG = 5;
public const PIXEL_QUANTUM = 6;
public const PIXEL_SHORT = 7;
public const EVALUATE_UNDEFINED = 0;
public const EVALUATE_ADD = 1;
public const EVALUATE_AND = 2;
public const EVALUATE_DIVIDE = 3;
public const EVALUATE_LEFTSHIFT = 4;
public const EVALUATE_MAX = 5;
public const EVALUATE_MIN = 6;
public const EVALUATE_MULTIPLY = 7;
public const EVALUATE_OR = 8;
public const EVALUATE_RIGHTSHIFT = 9;
public const EVALUATE_SET = 10;
public const EVALUATE_SUBTRACT = 11;
public const EVALUATE_XOR = 12;
public const EVALUATE_POW = 13;
public const EVALUATE_LOG = 14;
public const EVALUATE_THRESHOLD = 15;
public const EVALUATE_THRESHOLDBLACK = 16;
public const EVALUATE_THRESHOLDWHITE = 17;
public const EVALUATE_GAUSSIANNOISE = 18;
public const EVALUATE_IMPULSENOISE = 19;
public const EVALUATE_LAPLACIANNOISE = 20;
public const EVALUATE_MULTIPLICATIVENOISE = 21;
public const EVALUATE_POISSONNOISE = 22;
public const EVALUATE_UNIFORMNOISE = 23;
public const EVALUATE_COSINE = 24;
public const EVALUATE_SINE = 25;
public const EVALUATE_ADDMODULUS = 26;
public const EVALUATE_MEAN = 27;
public const EVALUATE_ABS = 28;
public const EVALUATE_EXPONENTIAL = 29;
public const EVALUATE_MEDIAN = 30;
public const EVALUATE_SUM = 31;
public const COLORSPACE_UNDEFINED = 0;
public const COLORSPACE_RGB = 1;
public const COLORSPACE_GRAY = 2;
public const COLORSPACE_TRANSPARENT = 3;
public const COLORSPACE_OHTA = 4;
public const COLORSPACE_LAB = 5;
public const COLORSPACE_XYZ = 6;
public const COLORSPACE_YCBCR = 7;
public const COLORSPACE_YCC = 8;
public const COLORSPACE_YIQ = 9;
public const COLORSPACE_YPBPR = 10;
public const COLORSPACE_YUV = 11;
public const COLORSPACE_CMYK = 12;
public const COLORSPACE_SRGB = 13;
public const COLORSPACE_HSB = 14;
public const COLORSPACE_HSL = 15;
public const COLORSPACE_HWB = 16;
public const COLORSPACE_REC601LUMA = 17;
public const COLORSPACE_REC709LUMA = 19;
public const COLORSPACE_LOG = 21;
public const COLORSPACE_CMY = 22;
public const COLORSPACE_LUV = 23;
public const COLORSPACE_HCL = 24;
public const COLORSPACE_LCH = 25;
public const COLORSPACE_LMS = 26;
public const COLORSPACE_LCHAB = 27;
public const COLORSPACE_LCHUV = 28;
public const COLORSPACE_SCRGB = 29;
public const COLORSPACE_HSI = 30;
public const COLORSPACE_HSV = 31;
public const COLORSPACE_HCLP = 32;
public const COLORSPACE_YDBDR = 33;
public const COLORSPACE_REC601YCBCR = 18;
public const COLORSPACE_REC709YCBCR = 20;
public const VIRTUALPIXELMETHOD_UNDEFINED = 0;
public const VIRTUALPIXELMETHOD_BACKGROUND = 1;
public const VIRTUALPIXELMETHOD_CONSTANT = 2;
public const VIRTUALPIXELMETHOD_EDGE = 4;
public const VIRTUALPIXELMETHOD_MIRROR = 5;
public const VIRTUALPIXELMETHOD_TILE = 7;
public const VIRTUALPIXELMETHOD_TRANSPARENT = 8;
public const VIRTUALPIXELMETHOD_MASK = 9;
public const VIRTUALPIXELMETHOD_BLACK = 10;
public const VIRTUALPIXELMETHOD_GRAY = 11;
public const VIRTUALPIXELMETHOD_WHITE = 12;
public const VIRTUALPIXELMETHOD_HORIZONTALTILE = 13;
public const VIRTUALPIXELMETHOD_VERTICALTILE = 14;
public const VIRTUALPIXELMETHOD_HORIZONTALTILEEDGE = 15;
public const VIRTUALPIXELMETHOD_VERTICALTILEEDGE = 16;
public const VIRTUALPIXELMETHOD_CHECKERTILE = 17;
public const PREVIEW_UNDEFINED = 0;
public const PREVIEW_ROTATE = 1;
public const PREVIEW_SHEAR = 2;
public const PREVIEW_ROLL = 3;
public const PREVIEW_HUE = 4;
public const PREVIEW_SATURATION = 5;
public const PREVIEW_BRIGHTNESS = 6;
public const PREVIEW_GAMMA = 7;
public const PREVIEW_SPIFF = 8;
public const PREVIEW_DULL = 9;
public const PREVIEW_GRAYSCALE = 10;
public const PREVIEW_QUANTIZE = 11;
public const PREVIEW_DESPECKLE = 12;
public const PREVIEW_REDUCENOISE = 13;
public const PREVIEW_ADDNOISE = 14;
public const PREVIEW_SHARPEN = 15;
public const PREVIEW_BLUR = 16;
public const PREVIEW_THRESHOLD = 17;
public const PREVIEW_EDGEDETECT = 18;
public const PREVIEW_SPREAD = 19;
public const PREVIEW_SOLARIZE = 20;
public const PREVIEW_SHADE = 21;
public const PREVIEW_RAISE = 22;
public const PREVIEW_SEGMENT = 23;
public const PREVIEW_SWIRL = 24;
public const PREVIEW_IMPLODE = 25;
public const PREVIEW_WAVE = 26;
public const PREVIEW_OILPAINT = 27;
public const PREVIEW_CHARCOALDRAWING = 28;
public const PREVIEW_JPEG = 29;
public const RENDERINGINTENT_UNDEFINED = 0;
public const RENDERINGINTENT_SATURATION = 1;
public const RENDERINGINTENT_PERCEPTUAL = 2;
public const RENDERINGINTENT_ABSOLUTE = 3;
public const RENDERINGINTENT_RELATIVE = 4;
public const INTERLACE_UNDEFINED = 0;
public const INTERLACE_NO = 1;
public const INTERLACE_LINE = 2;
public const INTERLACE_PLANE = 3;
public const INTERLACE_PARTITION = 4;
public const INTERLACE_GIF = 5;
public const INTERLACE_JPEG = 6;
public const INTERLACE_PNG = 7;
public const FILLRULE_UNDEFINED = 0;
public const FILLRULE_EVENODD = 1;
public const FILLRULE_NONZERO = 2;
public const PATHUNITS_UNDEFINED = 0;
public const PATHUNITS_USERSPACE = 1;
public const PATHUNITS_USERSPACEONUSE = 2;
public const PATHUNITS_OBJECTBOUNDINGBOX = 3;
public const LINECAP_UNDEFINED = 0;
public const LINECAP_BUTT = 1;
public const LINECAP_ROUND = 2;
public const LINECAP_SQUARE = 3;
public const LINEJOIN_UNDEFINED = 0;
public const LINEJOIN_MITER = 1;
public const LINEJOIN_ROUND = 2;
public const LINEJOIN_BEVEL = 3;
public const RESOURCETYPE_UNDEFINED = 0;
public const RESOURCETYPE_AREA = 1;
public const RESOURCETYPE_DISK = 2;
public const RESOURCETYPE_FILE = 3;
public const RESOURCETYPE_MAP = 4;
public const RESOURCETYPE_MEMORY = 5;
public const RESOURCETYPE_TIME = 7;
public const RESOURCETYPE_THROTTLE = 8;
public const RESOURCETYPE_THREAD = 6;
public const DISPOSE_UNRECOGNIZED = 0;
public const DISPOSE_UNDEFINED = 0;
public const DISPOSE_NONE = 1;
public const DISPOSE_BACKGROUND = 2;
public const DISPOSE_PREVIOUS = 3;
public const INTERPOLATE_UNDEFINED = 0;
public const INTERPOLATE_AVERAGE = 1;
public const INTERPOLATE_BICUBIC = 2;
public const INTERPOLATE_BILINEAR = 3;
public const INTERPOLATE_FILTER = 4;
public const INTERPOLATE_INTEGER = 5;
public const INTERPOLATE_MESH = 6;
public const INTERPOLATE_NEARESTNEIGHBOR = 7;
public const INTERPOLATE_SPLINE = 8;
public const LAYERMETHOD_UNDEFINED = 0;
public const LAYERMETHOD_COALESCE = 1;
public const LAYERMETHOD_COMPAREANY = 2;
public const LAYERMETHOD_COMPARECLEAR = 3;
public const LAYERMETHOD_COMPAREOVERLAY = 4;
public const LAYERMETHOD_DISPOSE = 5;
public const LAYERMETHOD_OPTIMIZE = 6;
public const LAYERMETHOD_OPTIMIZEPLUS = 8;
public const LAYERMETHOD_OPTIMIZETRANS = 9;
public const LAYERMETHOD_COMPOSITE = 12;
public const LAYERMETHOD_OPTIMIZEIMAGE = 7;
public const LAYERMETHOD_REMOVEDUPS = 10;
public const LAYERMETHOD_REMOVEZERO = 11;
public const LAYERMETHOD_TRIMBOUNDS = 16;
public const ORIENTATION_UNDEFINED = 0;
public const ORIENTATION_TOPLEFT = 1;
public const ORIENTATION_TOPRIGHT = 2;
public const ORIENTATION_BOTTOMRIGHT = 3;
public const ORIENTATION_BOTTOMLEFT = 4;
public const ORIENTATION_LEFTTOP = 5;
public const ORIENTATION_RIGHTTOP = 6;
public const ORIENTATION_RIGHTBOTTOM = 7;
public const ORIENTATION_LEFTBOTTOM = 8;
public const DISTORTION_UNDEFINED = 0;
public const DISTORTION_AFFINE = 1;
public const DISTORTION_AFFINEPROJECTION = 2;
public const DISTORTION_ARC = 9;
public const DISTORTION_BILINEAR = 6;
public const DISTORTION_PERSPECTIVE = 4;
public const DISTORTION_PERSPECTIVEPROJECTION = 5;
public const DISTORTION_SCALEROTATETRANSLATE = 3;
public const DISTORTION_POLYNOMIAL = 8;
public const DISTORTION_POLAR = 10;
public const DISTORTION_DEPOLAR = 11;
public const DISTORTION_BARREL = 14;
public const DISTORTION_SHEPARDS = 16;
public const DISTORTION_SENTINEL = 18;
public const DISTORTION_BARRELINVERSE = 15;
public const DISTORTION_BILINEARFORWARD = 6;
public const DISTORTION_BILINEARREVERSE = 7;
public const DISTORTION_RESIZE = 17;
public const DISTORTION_CYLINDER2PLANE = 12;
public const DISTORTION_PLANE2CYLINDER = 13;
public const LAYERMETHOD_MERGE = 13;
public const LAYERMETHOD_FLATTEN = 14;
public const LAYERMETHOD_MOSAIC = 15;
public const ALPHACHANNEL_ACTIVATE = 1;
public const ALPHACHANNEL_RESET = 7;
public const ALPHACHANNEL_SET = 8;
public const ALPHACHANNEL_UNDEFINED = 0;
public const ALPHACHANNEL_COPY = 3;
public const ALPHACHANNEL_DEACTIVATE = 4;
public const ALPHACHANNEL_EXTRACT = 5;
public const ALPHACHANNEL_OPAQUE = 6;
public const ALPHACHANNEL_SHAPE = 9;
public const ALPHACHANNEL_TRANSPARENT = 10;
public const SPARSECOLORMETHOD_UNDEFINED = 0;
public const SPARSECOLORMETHOD_BARYCENTRIC = 1;
public const SPARSECOLORMETHOD_BILINEAR = 7;
public const SPARSECOLORMETHOD_POLYNOMIAL = 8;
public const SPARSECOLORMETHOD_SPEPARDS = 16;
public const SPARSECOLORMETHOD_VORONOI = 18;
public const SPARSECOLORMETHOD_INVERSE = 19;
public const DITHERMETHOD_UNDEFINED = 0;
public const DITHERMETHOD_NO = 1;
public const DITHERMETHOD_RIEMERSMA = 2;
public const DITHERMETHOD_FLOYDSTEINBERG = 3;
public const FUNCTION_UNDEFINED = 0;
public const FUNCTION_POLYNOMIAL = 1;
public const FUNCTION_SINUSOID = 2;
public const ALPHACHANNEL_BACKGROUND = 2;
public const FUNCTION_ARCSIN = 3;
public const FUNCTION_ARCTAN = 4;
public const ALPHACHANNEL_FLATTEN = 11;
public const ALPHACHANNEL_REMOVE = 12;
public const STATISTIC_GRADIENT = 1;
public const STATISTIC_MAXIMUM = 2;
public const STATISTIC_MEAN = 3;
public const STATISTIC_MEDIAN = 4;
public const STATISTIC_MINIMUM = 5;
public const STATISTIC_MODE = 6;
public const STATISTIC_NONPEAK = 7;
public const STATISTIC_STANDARD_DEVIATION = 8;
public const MORPHOLOGY_CONVOLVE = 1;
public const MORPHOLOGY_CORRELATE = 2;
public const MORPHOLOGY_ERODE = 3;
public const MORPHOLOGY_DILATE = 4;
public const MORPHOLOGY_ERODE_INTENSITY = 5;
public const MORPHOLOGY_DILATE_INTENSITY = 6;
public const MORPHOLOGY_DISTANCE = 7;
public const MORPHOLOGY_OPEN = 8;
public const MORPHOLOGY_CLOSE = 9;
public const MORPHOLOGY_OPEN_INTENSITY = 10;
public const MORPHOLOGY_CLOSE_INTENSITY = 11;
public const MORPHOLOGY_SMOOTH = 12;
public const MORPHOLOGY_EDGE_IN = 13;
public const MORPHOLOGY_EDGE_OUT = 14;
public const MORPHOLOGY_EDGE = 15;
public const MORPHOLOGY_TOP_HAT = 16;
public const MORPHOLOGY_BOTTOM_HAT = 17;
public const MORPHOLOGY_HIT_AND_MISS = 18;
public const MORPHOLOGY_THINNING = 19;
public const MORPHOLOGY_THICKEN = 20;
public const MORPHOLOGY_VORONOI = 21;
public const MORPHOLOGY_ITERATIVE = 22;
public const KERNEL_UNITY = 1;
public const KERNEL_GAUSSIAN = 2;
public const KERNEL_DIFFERENCE_OF_GAUSSIANS = 3;
public const KERNEL_LAPLACIAN_OF_GAUSSIANS = 4;
public const KERNEL_BLUR = 5;
public const KERNEL_COMET = 6;
public const KERNEL_LAPLACIAN = 7;
public const KERNEL_SOBEL = 8;
public const KERNEL_FREI_CHEN = 9;
public const KERNEL_ROBERTS = 10;
public const KERNEL_PREWITT = 11;
public const KERNEL_COMPASS = 12;
public const KERNEL_KIRSCH = 13;
public const KERNEL_DIAMOND = 14;
public const KERNEL_SQUARE = 15;
public const KERNEL_RECTANGLE = 16;
public const KERNEL_OCTAGON = 17;
public const KERNEL_DISK = 18;
public const KERNEL_PLUS = 19;
public const KERNEL_CROSS = 20;
public const KERNEL_RING = 21;
public const KERNEL_PEAKS = 22;
public const KERNEL_EDGES = 23;
public const KERNEL_CORNERS = 24;
public const KERNEL_DIAGONALS = 25;
public const KERNEL_LINE_ENDS = 26;
public const KERNEL_LINE_JUNCTIONS = 27;
public const KERNEL_RIDGES = 28;
public const KERNEL_CONVEX_HULL = 29;
public const KERNEL_THIN_SE = 30;
public const KERNEL_SKELETON = 31;
public const KERNEL_CHEBYSHEV = 32;
public const KERNEL_MANHATTAN = 33;
public const KERNEL_OCTAGONAL = 34;
public const KERNEL_EUCLIDEAN = 35;
public const KERNEL_USER_DEFINED = 36;
public const KERNEL_BINOMIAL = 37;
public const DIRECTION_LEFT_TO_RIGHT = 2;
public const DIRECTION_RIGHT_TO_LEFT = 1;
public const NORMALIZE_KERNEL_NONE = 0;
public const NORMALIZE_KERNEL_VALUE = 8192;
public const NORMALIZE_KERNEL_CORRELATE = 65536;
public const NORMALIZE_KERNEL_PERCENT = 4096;








public function optimizeImageLayers() {}











public function compareImageLayers($method) {}











public function pingImageBlob($image) {}














public function pingImageFile($filehandle, $fileName = null) {}








public function transposeImage() {}








public function transverseImage() {}















public function trimImage($fuzz) {}














public function waveImage($amplitude, $length) {}




















public function vignetteImage($blackPoint, $whitePoint, $x, $y) {}








public function uniqueImageColors() {}








#[Deprecated]
#[Pure]
public function getImageMatte() {}











public function setImageMatte($matte) {}



















public function adaptiveResizeImage($columns, $rows, $bestfit = false, $legacy = false) {}

















public function sketchImage($radius, $sigma, $angle) {}

















public function shadeImage($gray, $azimuth, $elevation) {}








#[Pure]
public function getSizeOffset() {}

















public function setSizeOffset($columns, $rows, $offset) {}


















public function adaptiveBlurImage($radius, $sigma, $channel = Imagick::CHANNEL_DEFAULT) {}




















public function contrastStretchImage($black_point, $white_point, $channel = Imagick::CHANNEL_ALL) {}

















public function adaptiveSharpenImage($radius, $sigma, $channel = Imagick::CHANNEL_DEFAULT) {}




















public function randomThresholdImage($low, $high, $channel = Imagick::CHANNEL_ALL) {}









public function roundCornersImage($xRounding, $yRounding, $strokeWidth, $displace, $sizeCorrection) {}
























#[Deprecated(replacement: "%class%->roundCornersImage(%parametersList%)")]
public function roundCorners($x_rounding, $y_rounding, $stroke_width = 10.0, $displace = 5.0, $size_correction = -6.0) {}











public function setIteratorIndex($index) {}







#[Pure]
public function getIteratorIndex() {}














public function transformImage($crop, $geometry) {}












public function setImageOpacity($opacity) {}

















public function orderedPosterizeImage($threshold_map, $channel = Imagick::CHANNEL_ALL) {}














public function polaroidImage(ImagickDraw $properties, $angle) {}












#[Pure]
public function getImageProperty($name) {}










public function setImageProperty($name, $value) {}











public function setImageInterpolateMethod($method) {}








#[Pure]
public function getImageInterpolateMethod() {}














public function linearStretchImage($blackPoint, $whitePoint) {}








#[Pure]
public function getImageLength() {}




















public function extentImage($width, $height, $x, $y) {}








#[Pure]
public function getImageOrientation() {}











public function setImageOrientation($orientation) {}




























#[Deprecated]
public function paintFloodfillImage($fill, $fuzz, $bordercolor, $x, $y, $channel = Imagick::CHANNEL_ALL) {}
















public function clutImage(Imagick $lookup_table, $channel = Imagick::CHANNEL_DEFAULT) {}














#[Pure]
public function getImageProperties($pattern = "*", $only_names = true) {}














#[Pure]
public function getImageProfiles($pattern = "*", $include_values = true) {}

















public function distortImage($method, array $arguments, $bestfit) {}











public function writeImageFile($filehandle) {}











public function writeImagesFile($filehandle) {}











public function resetImagePage($page) {}










public function setImageClipMask(Imagick $clip_mask) {}








#[Pure]
public function getImageClipMask() {}











public function animateImages($x_server) {}











#[Deprecated]
public function recolorImage(array $matrix) {}











public function setFont($font) {}







#[Pure]
public function getFont() {}











public function setPointSize($point_size) {}







#[Pure]
public function getPointSize() {}











public function mergeImageLayers($layer_method) {}











public function setImageAlphaChannel($mode) {}





























public function floodFillPaintImage($fill, $fuzz, $target, $x, $y, $invert, $channel = Imagick::CHANNEL_DEFAULT) {}























public function opaquePaintImage($target, $fill, $fuzz, $invert, $channel = Imagick::CHANNEL_DEFAULT) {}




















public function transparentPaintImage($target, $alpha, $fuzz, $invert) {}






















public function liquidRescaleImage($width, $height, $delta_x, $rigidity) {}











public function encipherImage($passphrase) {}











public function decipherImage($passphrase) {}












public function setGravity($gravity) {}








#[Pure]
public function getGravity() {}











#[ArrayShape(["minima" => "float", "maxima" => "float"])]
#[Pure]
public function getImageChannelRange($channel) {}









#[Pure]
public function getImageAlphaChannel() {}

















#[Pure]
public function getImageChannelDistortions(Imagick $reference, $metric, $channel = Imagick::CHANNEL_DEFAULT) {}












public function setImageGravity($gravity) {}









#[Pure]
public function getImageGravity() {}
































public function importImagePixels($x, $y, $width, $height, $map, $storage, array $pixels) {}











public function deskewImage($threshold) {}





















public function segmentImage($COLORSPACE, $cluster_threshold, $smooth_threshold, $verbose = false) {}
















public function sparseColorImage($SPARSE_METHOD, array $arguments, $channel = Imagick::CHANNEL_DEFAULT) {}














public function remapImage(Imagick $replacement, $DITHER) {}



























public function exportImagePixels($x, $y, $width, $height, $map, $STORAGE) {}












#[ArrayShape(["kurtosis" => "float", "skewness" => "float"])]
#[Pure]
public function getImageChannelKurtosis($channel = Imagick::CHANNEL_DEFAULT) {}















public function functionImage($function, array $arguments, $channel = Imagick::CHANNEL_DEFAULT) {}







public function transformImageColorspace($COLORSPACE) {}














public function haldClutImage(Imagick $clut, $channel = Imagick::CHANNEL_DEFAULT) {}







public function autoLevelImage($CHANNEL) {}







public function blueShiftImage($factor) {}











#[Pure]
public function getImageArtifact($artifact) {}














public function setImageArtifact($artifact, $value) {}











public function deleteImageArtifact($artifact) {}







#[Pure]
public function getColorspace() {}










public function setColorspace($COLORSPACE) {}





public function clampImage($CHANNEL) {}







public function smushImages($stack, $offset) {}











public function __construct($files = null) {}




public function __toString() {}

public function count() {}









#[Pure]
public function getPixelIterator() {}





















#[Pure]
public function getPixelRegionIterator($x, $y, $columns, $rows) {}









public function readImage($filename) {}





public function readImages($filenames) {}










public function readImageBlob($image, $filename = null) {}












public function setImageFormat($format) {}














public function scaleImage($cols, $rows, $bestfit = false, $legacy = false) {}














public function writeImage($filename = null) {}










public function writeImages($filename, $adjoin) {}


















public function blurImage($radius, $sigma, $channel = null) {}
























public function thumbnailImage($columns, $rows, $bestfit = false, $fill = false, $legacy = false) {}













public function cropThumbnailImage($width, $height, $legacy = false) {}








#[Pure]
public function getImageFilename() {}









public function setImageFilename($filename) {}








#[Pure]
public function getImageFormat() {}






#[Pure]
public function getImageMimeType() {}








public function removeImage() {}







public function destroy() {}







public function clear() {}








#[Deprecated(replacement: "%class%->getImageLength()")]
#[Pure]
public function getImageSize() {}








#[Pure]
public function getImageBlob() {}








#[Pure]
public function getImagesBlob() {}







public function setFirstIterator() {}







public function setLastIterator() {}

public function resetIterator() {}







public function previousImage() {}







public function nextImage() {}








public function hasPreviousImage() {}








public function hasNextImage() {}











#[Deprecated]
public function setImageIndex($index) {}







#[Deprecated]
#[Pure]
public function getImageIndex() {}











public function commentImage($comment) {}




















public function cropImage($width, $height, $x, $y) {}











public function labelImage($label) {}








#[ArrayShape(["width" => "int", "height" => "int"])]
#[Pure]
public function getImageGeometry() {}











public function drawImage(ImagickDraw $draw) {}











public function setImageCompressionQuality($quality) {}







#[Pure]
public function getImageCompressionQuality() {}























public function annotateImage(ImagickDraw $draw_settings, $x, $y, $angle, $text) {}























public function compositeImage(Imagick $composite_object, $composite, $x, $y, $channel = Imagick::CHANNEL_ALL) {}











public function modulateImage($brightness, $saturation, $hue) {}








#[Pure]
public function getImageColors() {}

























public function montageImage(ImagickDraw $draw, $tile_geometry, $thumbnail_geometry, $mode, $frame) {}










public function identifyImage($appendRawOutput = false) {}










public function thresholdImage($threshold, $channel = Imagick::CHANNEL_ALL) {}

















public function adaptiveThresholdImage($width, $height, $offset) {}











public function blackThresholdImage($threshold) {}









public function whiteThresholdImage($threshold) {}













public function appendImages($stack = false) {}














public function charcoalImage($radius, $sigma) {}














public function normalizeImage($channel = Imagick::CHANNEL_ALL) {}











public function oilPaintImage($radius) {}










public function posterizeImage($levels, $dither) {}










public function radialBlurImage($angle, $channel = Imagick::CHANNEL_ALL) {}













public function raiseImage($width, $height, $x, $y, $raise) {}












public function resampleImage($x_resolution, $y_resolution, $filter, $blur) {}



















public function resizeImage($columns, $rows, $filter, $blur, $bestfit = false, $legacy = false) {}














public function rollImage($x, $y) {}














public function rotateImage($background, $degrees) {}










public function sampleImage($columns, $rows) {}









public function solarizeImage($threshold) {}












public function shadowImage($opacity, $sigma, $x, $y) {}







#[Deprecated]
public function setImageAttribute($key, $value) {}









public function setImageBackgroundColor($background) {}









public function setImageCompose($compose) {}











public function setImageCompression($compression) {}













public function setImageDelay($delay) {}









public function setImageDepth($depth) {}









public function setImageGamma($gamma) {}












public function setImageIterations($iterations) {}









public function setImageMatteColor($matte) {}












public function setImagePage($width, $height, $x, $y) {}





public function setImageProgressMonitor($filename) {}










public function setImageResolution($x_resolution, $y_resolution) {}









public function setImageScene($scene) {}












public function setImageTicksPerSecond($ticks_per_second) {}









public function setImageType($image_type) {}









public function setImageUnits($units) {}











public function sharpenImage($radius, $sigma, $channel = Imagick::CHANNEL_ALL) {}










public function shaveImage($columns, $rows) {}

















public function shearImage($background, $x_shear, $y_shear) {}












public function spliceImage($width, $height, $x, $y) {}











public function pingImage($filename) {}










public function readImageFile($filehandle, $fileName = null) {}











public function displayImage($servername) {}











public function displayImages($servername) {}









public function spreadImage($radius) {}









public function swirlImage($degrees) {}








public function stripImage() {}








public static function queryFormats($pattern = "*") {}










public static function queryFonts($pattern = "*") {}

















public function queryFontMetrics(ImagickDraw $properties, $text, $multiline = null) {}










public function steganoImage(Imagick $watermark_wand, $offset) {}















public function addNoiseImage($noise_type, $channel = Imagick::CHANNEL_DEFAULT) {}

























public function motionBlurImage($radius, $sigma, $angle, $channel = Imagick::CHANNEL_DEFAULT) {}








#[Deprecated]
public function mosaicImages() {}













public function morphImages($number_frames) {}








public function minifyImage() {}











public function affineTransformImage(ImagickDraw $matrix) {}








#[Deprecated]
public function averageImages() {}

















public function borderImage($bordercolor, $width, $height) {}




















public function chopImage($width, $height, $x, $y) {}








public function clipImage() {}















public function clipPathImage($pathname, $inside) {}







public function clipImagePath($pathname, $inside) {}








public function coalesceImages() {}

























#[Deprecated]
public function colorFloodfillImage($fill, $fuzz, $bordercolor, $x, $y) {}

















public function colorizeImage($colorize, $opacity, $legacy = false) {}





















public function compareImageChannels(Imagick $image, $channelType, $metricType) {}
















public function compareImages(Imagick $compare, $metric) {}











public function contrastImage($sharpen) {}














public function combineImages($channelType) {}

















public function convolveImage(array $kernel, $channel = Imagick::CHANNEL_ALL) {}











public function cycleColormapImage($displace) {}








public function deconstructImages() {}








public function despeckleImage() {}











public function edgeImage($radius) {}














public function embossImage($radius, $sigma) {}








public function enhanceImage() {}








public function equalizeImage() {}




















public function evaluateImage($op, $constant, $channel = Imagick::CHANNEL_ALL) {}












#[Deprecated]
public function flattenImages() {}








public function flipImage() {}








public function flopImage() {}























public function frameImage($matte_color, $width, $height, $inner_bevel, $outer_bevel) {}

















public function fxImage($expression, $channel = Imagick::CHANNEL_ALL) {}

















public function gammaImage($gamma, $channel = Imagick::CHANNEL_ALL) {}




















public function gaussianBlurImage($radius, $sigma, $channel = Imagick::CHANNEL_ALL) {}






#[Deprecated]
#[Pure]
public function getImageAttribute($key) {}








#[Pure]
public function getImageBackgroundColor() {}








#[ArrayShape(["x" => "float", "y" => "float"])]
#[Pure]
public function getImageBluePrimary() {}








#[Pure]
public function getImageBorderColor() {}











#[Pure]
public function getImageChannelDepth($channel) {}




















#[Pure]
public function getImageChannelDistortion(Imagick $reference, $channel, $metric) {}














#[ArrayShape(["minima" => "int", "maxima" => "int"])]
#[Deprecated]
#[Pure]
public function getImageChannelExtrema($channel) {}














#[ArrayShape(["mean" => "float", "standardDeviation" => "float"])]
#[Pure]
public function getImageChannelMean($channel) {}








#[Pure]
public function getImageChannelStatistics() {}











#[Pure]
public function getImageColormapColor($index) {}








#[Pure]
public function getImageColorspace() {}








#[Pure]
public function getImageCompose() {}








#[Pure]
public function getImageDelay() {}








#[Pure]
public function getImageDepth() {}















#[Pure]
public function getImageDistortion(Imagick $reference, $metric) {}








#[ArrayShape(["min" => "int", "max" => "int"])]
#[Deprecated]
#[Pure]
public function getImageExtrema() {}








#[Pure]
public function getImageDispose() {}








#[Pure]
public function getImageGamma() {}









#[ArrayShape(["x" => "float", "y" => "float"])]
#[Pure]
public function getImageGreenPrimary() {}








#[Pure]
public function getImageHeight() {}








#[Pure]
public function getImageHistogram() {}









#[Deprecated]
#[Pure]
public function getImageInterlaceScheme() {}








#[Pure]
public function getImageIterations() {}








#[Pure]
public function getImageMatteColor() {}









#[ArrayShape(["width" => "int", "height" => "int", "x" => "int", "y" => "int"])]
#[Pure]
public function getImagePage() {}














#[Pure]
public function getImagePixelColor($x, $y) {}











#[Pure]
public function getImageProfile($name) {}










#[ArrayShape(["x" => "float", "y" => "float"])]
#[Pure]
public function getImageRedPrimary() {}








#[Pure]
public function getImageRenderingIntent() {}








#[ArrayShape(["x" => "float", "y" => "float"])]
#[Pure]
public function getImageResolution() {}








#[Pure]
public function getImageScene() {}








#[Pure]
public function getImageSignature() {}








#[Pure]
public function getImageTicksPerSecond() {}



















#[Pure]
public function getImageType() {}








#[Pure]
public function getImageUnits() {}








#[Pure]
public function getImageVirtualPixelMethod() {}









#[ArrayShape(["x" => "float", "y" => "float"])]
#[Pure]
public function getImageWhitePoint() {}








#[Pure]
public function getImageWidth() {}







#[Pure]
public function getNumberImages() {}










#[Pure]
public function getImageTotalInkDensity() {}




















#[Pure]
public function getImageRegion($width, $height, $x, $y) {}











public function implodeImage($radius) {}























public function levelImage($blackPoint, $gamma, $whitePoint, $channel = Imagick::CHANNEL_ALL) {}








public function magnifyImage() {}










#[Deprecated]
public function mapImage(Imagick $map, $dither) {}

























#[Deprecated]
public function matteFloodfillImage($alpha, $fuzz, $bordercolor, $x, $y) {}











#[Deprecated]
public function medianFilterImage($radius) {}

















public function negateImage($gray, $channel = Imagick::CHANNEL_ALL) {}

























#[Deprecated]
public function paintOpaqueImage($target, $fill, $fuzz, $channel = Imagick::CHANNEL_ALL) {}



















#[Deprecated]
public function paintTransparentImage($target, $alpha, $fuzz) {}











public function previewImages($preview) {}










public function profileImage($name, $profile) {}













public function quantizeImage($numberColors, $colorspace, $treedepth, $dither, $measureError) {}













public function quantizeImages($numberColors, $colorspace, $treedepth, $dither, $measureError) {}









#[Deprecated]
public function reduceNoiseImage($radius) {}









public function removeImageProfile($name) {}









public function separateImageChannel($channel) {}









public function sepiaToneImage($threshold) {}








public function setImageBias($bias) {}










public function setImageBluePrimary($x, $y) {}











public function setImageBorderColor($border) {}










public function setImageChannelDepth($channel, $depth) {}










public function setImageColormapColor($index, ImagickPixel $color) {}











public function setImageColorspace($colorspace) {}









public function setImageDispose($dispose) {}










public function setImageExtent($columns, $rows) {}










public function setImageGreenPrimary($x, $y) {}









public function setImageInterlaceScheme($interlace_scheme) {}










public function setImageProfile($name, $profile) {}










public function setImageRedPrimary($x, $y) {}









public function setImageRenderingIntent($rendering_intent) {}









public function setImageVirtualPixelMethod($method) {}










public function setImageWhitePoint($x, $y) {}












public function sigmoidalContrastImage($sharpen, $alpha, $beta, $channel = Imagick::CHANNEL_ALL) {}









public function stereoImage(Imagick $offset_wand) {}









public function textureImage(Imagick $texture_wand) {}












public function tintImage($tint, $opacity, $legacy = false) {}













public function unsharpMaskImage($radius, $sigma, $amount, $threshold, $channel = Imagick::CHANNEL_ALL) {}








#[Pure]
public function getImage() {}











public function addImage(Imagick $source) {}











public function setImage(Imagick $replace) {}




















public function newImage($cols, $rows, $background, $format = null) {}

















public function newPseudoImage($columns, $rows, $pseudoString) {}







#[Pure]
public function getCompression() {}







#[Pure]
public function getCompressionQuality() {}








public static function getCopyright() {}







#[Pure]
public function getFilename() {}







#[Pure]
public function getFormat() {}







public static function getHomeURL() {}








#[Pure]
public function getInterlaceScheme() {}










#[Pure]
public function getOption($key) {}







public static function getPackageName() {}











#[ArrayShape(["width" => "int", "height" => "int", "x" => "int", "y" => "int"])]
#[Pure]
public function getPage() {}








#[ArrayShape(["quantumDepthLong" => "int", "quantumDepthString" => "string"])]
public static function getQuantumDepth() {}







#[ArrayShape(["quantumRangeLong" => "int", "quantumRangeString" => "string"])]
public static function getQuantumRange() {}







public static function getReleaseDate() {}










public static function getResource($type) {}










public static function getResourceLimit($type) {}








#[Pure]
public function getSamplingFactors() {}









#[ArrayShape(["columns" => "int", "rows" => "int"])]
#[Pure]
public function getSize() {}







#[ArrayShape(["versionNumber" => "int", "versionString" => "string"])]
public static function getVersion() {}









public function setBackgroundColor($background) {}









public function setCompression($compression) {}









public function setCompressionQuality($quality) {}









public function setFilename($filename) {}









public function setFormat($format) {}









public function setInterlaceScheme($interlace_scheme) {}










public function setOption($key, $value) {}












public function setPage($width, $height, $x, $y) {}














public static function setResourceLimit($type, $limit) {}














public function setResolution($x_resolution, $y_resolution) {}









public function setSamplingFactors(array $factors) {}










public function setSize($columns, $rows) {}









public function setType($image_type) {}

public function key() {}

public function next() {}

public function rewind() {}








public function valid() {}







public function current() {}











public function brightnessContrastImage($brightness, $contrast, $CHANNEL = Imagick::CHANNEL_DEFAULT) {}













public function morphology($morphologyMethod, $iterations, ImagickKernel $ImagickKernel, $CHANNEL = Imagick::CHANNEL_DEFAULT) {}










public function filter(ImagickKernel $ImagickKernel, $CHANNEL = Imagick::CHANNEL_DEFAULT) {}










public function colorMatrixImage($color_matrix = Imagick::CHANNEL_DEFAULT) {}









public function deleteImageProperty($name) {}









public function forwardFourierTransformimage($magnitude) {}







#[Pure]
public function getImageCompression() {}









public static function getRegistry($key) {}







public static function getQuantum() {}










public function identifyFormat($embedText) {}










public function inverseFourierTransformImage($complement, $magnitude) {}







public static function listRegistry() {}










public function rotationalBlurImage($angle, $CHANNEL = Imagick::CHANNEL_DEFAULT) {}












public function selectiveBlurImage($radius, $sigma, $threshold, $CHANNEL = Imagick::CHANNEL_DEFAULT) {}








public function setAntiAlias($antialias) {}







public function setImageBiasQuantum($bias) {}













public function setProgressMonitor($callback) {}









public static function setRegistry($key, $value) {}












public function statisticImage($type, $width, $height, $channel = Imagick::CHANNEL_DEFAULT) {}


















public function subImageMatch(Imagick $imagick, array &$bestMatch, &$similarity, $similarity_threshold, $metric) {}














public function similarityImage(Imagick $imagick, array &$bestMatch, &$similarity, $similarity_threshold, $metric) {}






#[Pure]
public function getConfigureOptions() {}






#[Pure]
public function getFeatures() {}





#[Pure]
public function getHDRIEnabled() {}








public function setImageChannelMask($channel) {}









public function evaluateImages($EVALUATE_CONSTANT) {}








public function autoGammaImage($channel = Imagick::CHANNEL_ALL) {}







public function autoOrient() {}











public function compositeImageGravity(Imagick $imagick, $COMPOSITE_CONSTANT, $GRAVITY_CONSTANT) {}










public function localContrastImage($radius, $strength) {}







public function identifyImageType() {}









public function setImageAlpha($alpha) {}
}





class ImagickDraw
{
public function resetVectorGraphics() {}

#[Pure]
public function getTextKerning() {}




public function setTextKerning($kerning) {}

#[Pure]
public function getTextInterWordSpacing() {}




public function setTextInterWordSpacing($spacing) {}

#[Pure]
public function getTextInterLineSpacing() {}




public function setTextInterLineSpacing($spacing) {}






public function __construct() {}











public function setFillColor(ImagickPixel $fill_pixel) {}










#[Deprecated]
public function setFillAlpha($opacity) {}








public function setResolution($x_resolution, $y_resolution) {}











public function setStrokeColor(ImagickPixel $stroke_pixel) {}










#[Deprecated]
public function setStrokeAlpha($opacity) {}










public function setStrokeWidth($stroke_width) {}







public function clear() {}



















public function circle($ox, $oy, $px, $py) {}

















public function annotation($x, $y, $text) {}








public function setTextAntialias($antiAlias) {}










public function setTextEncoding($encoding) {}










public function setFont($font_name) {}












public function setFontFamily($font_family) {}










public function setFontSize($pointsize) {}










public function setFontStyle($style) {}









public function setFontWeight($font_weight) {}







#[Pure]
public function getFont() {}







#[Pure]
public function getFontFamily() {}







#[Pure]
public function getFontSize() {}








#[Pure]
public function getFontStyle() {}







#[Pure]
public function getFontWeight() {}







public function destroy() {}



















public function rectangle($x1, $y1, $x2, $y2) {}

























public function roundRectangle($x1, $y1, $x2, $y2, $rx, $ry) {}













public function ellipse($ox, $oy, $rx, $ry, $start, $end) {}










public function skewX($degrees) {}










public function skewY($degrees) {}













public function translate($x, $y) {}



















public function line($sx, $sy, $ex, $ey) {}

























public function arc($sx, $sy, $ex, $ey, $sd, $ed) {}
















public function matte($x, $y, $paintMethod) {}











public function polygon(array $coordinates) {}













public function point($x, $y) {}








#[Pure]
public function getTextDecoration() {}








#[Pure]
public function getTextEncoding() {}

#[Pure]
public function getFontStretch() {}










public function setFontStretch($fontStretch) {}










public function setStrokeAntialias($stroke_antialias) {}










public function setTextAlignment($alignment) {}










public function setTextDecoration($decoration) {}











public function setTextUnderColor(ImagickPixel $under_color) {}



















public function setViewbox($x1, $y1, $x2, $y2) {}











public function affine(array $affine) {}












public function bezier(array $coordinates) {}


























public function composite($compose, $x, $y, $width, $height, Imagick $compositeWand) {}
















public function color($x, $y, $paintMethod) {}










public function comment($comment) {}







#[Pure]
public function getClipPath() {}







#[Pure]
public function getClipRule() {}







#[Pure]
public function getClipUnits() {}







#[Pure]
public function getFillColor() {}







#[Pure]
public function getFillOpacity() {}







#[Pure]
public function getFillRule() {}







#[Pure]
public function getGravity() {}







#[Pure]
public function getStrokeAntialias() {}







#[Pure]
public function getStrokeColor() {}







#[Pure]
public function getStrokeDashArray() {}







#[Pure]
public function getStrokeDashOffset() {}







#[Pure]
public function getStrokeLineCap() {}







#[Pure]
public function getStrokeLineJoin() {}








#[Pure]
public function getStrokeMiterLimit() {}







#[Pure]
public function getStrokeOpacity() {}







#[Pure]
public function getStrokeWidth() {}







#[Pure]
public function getTextAlignment() {}







#[Pure]
public function getTextAntialias() {}







#[Pure]
public function getVectorGraphics() {}








#[Pure]
public function getTextUnderColor() {}







public function pathClose() {}

























public function pathCurveToAbsolute($x1, $y1, $x2, $y2, $x, $y) {}

























public function pathCurveToRelative($x1, $y1, $x2, $y2, $x, $y) {}



















public function pathCurveToQuadraticBezierAbsolute($x1, $y1, $x, $y) {}



















public function pathCurveToQuadraticBezierRelative($x1, $y1, $x, $y) {}













public function pathCurveToQuadraticBezierSmoothAbsolute($x, $y) {}













public function pathCurveToQuadraticBezierSmoothRelative($x, $y) {}



















public function pathCurveToSmoothAbsolute($x2, $y2, $x, $y) {}



















public function pathCurveToSmoothRelative($x2, $y2, $x, $y) {}




























public function pathEllipticArcAbsolute($rx, $ry, $x_axis_rotation, $large_arc_flag, $sweep_flag, $x, $y) {}




























public function pathEllipticArcRelative($rx, $ry, $x_axis_rotation, $large_arc_flag, $sweep_flag, $x, $y) {}







public function pathFinish() {}













public function pathLineToAbsolute($x, $y) {}













public function pathLineToRelative($x, $y) {}










public function pathLineToHorizontalAbsolute($x) {}










public function pathLineToHorizontalRelative($x) {}










public function pathLineToVerticalAbsolute($y) {}










public function pathLineToVerticalRelative($y) {}













public function pathMoveToAbsolute($x, $y) {}













public function pathMoveToRelative($x, $y) {}







public function pathStart() {}











public function polyline(array $coordinates) {}







public function popClipPath() {}







public function popDefs() {}








public function popPattern() {}










public function pushClipPath($clip_mask_id) {}







public function pushDefs() {}






















public function pushPattern($pattern_id, $x, $y, $width, $height) {}








public function render() {}










public function rotate($degrees) {}













public function scale($x, $y) {}











public function setClipPath($clip_mask) {}










public function setClipRule($fill_rule) {}










public function setClipUnits($clip_units) {}










public function setFillOpacity($fillOpacity) {}











public function setFillPatternURL($fill_url) {}










public function setFillRule($fill_rule) {}










public function setGravity($gravity) {}











public function setStrokePatternURL($stroke_url) {}










public function setStrokeDashOffset($dash_offset) {}










public function setStrokeLineCap($linecap) {}










public function setStrokeLineJoin($linejoin) {}










public function setStrokeMiterLimit($miterlimit) {}










public function setStrokeOpacity($stroke_opacity) {}










public function setVectorGraphics($xml) {}








public function pop() {}








public function push() {}










public function setStrokeDashArray(array $dashArray) {}








public function setOpacity($opacity) {}







#[Pure]
public function getOpacity() {}










public function setFontResolution($x, $y) {}








#[Pure]
public function getFontResolution() {}






#[Pure]
public function getTextDirection() {}








public function setTextDirection($direction) {}







#[Pure]
public function getBorderColor() {}








public function setBorderColor(ImagickPixel $color) {}







#[Pure]
public function getDensity() {}








public function setDensity($density_string) {}
}




class ImagickPixelIterator implements Iterator
{








public function __construct(Imagick $wand) {}










#[Deprecated(replacement: "%class%->getPixelIterator(%parametersList%)")]
public function newPixelIterator(Imagick $wand) {}














#[Deprecated(replacement: "%class%->getPixelRegionIterator(%parametersList%)")]
public function newPixelRegionIterator(Imagick $wand, $x, $y, $columns, $rows) {}








#[Pure]
public function getIteratorRow() {}









public function setIteratorRow($row) {}








public function setIteratorFirstRow() {}








public function setIteratorLastRow() {}









#[Pure]
public function getPreviousIteratorRow() {}








#[Pure]
public function getCurrentIteratorRow() {}









#[Pure]
public function getNextIteratorRow() {}








public function resetIterator() {}








public function syncIterator() {}








public function destroy() {}








public function clear() {}






public static function getpixeliterator(Imagick $Imagick) {}










public static function getpixelregioniterator(Imagick $Imagick, $x, $y, $columns, $rows) {}




public function key() {}




public function next() {}




public function rewind() {}




public function current() {}




public function valid() {}
}





class ImagickPixel
{








#[ArrayShape(["hue" => "float", "saturation" => "float", "luminosity" => "float"])]
#[Pure]
public function getHSL() {}




















public function setHSL($hue, $saturation, $luminosity) {}




#[Pure]
public function getColorValueQuantum() {}





public function setColorValueQuantum($color_value) {}





#[Pure]
public function getIndex() {}





public function setIndex($index) {}










public function __construct($color = null) {}












public function setColor($color) {}














public function setColorValue($color, $value) {}














#[Pure]
public function getColorValue($color) {}








public function clear() {}








public function destroy() {}
















public function isSimilar(ImagickPixel $color, $fuzz) {}
















public function isPixelSimilar(ImagickPixel $color, $fuzz) {}












#[ArrayShape(["r" => "int|float", "g" => "int|float", "b" => "int|float", "a" => "int|float"])]
#[Pure]
public function getColor($normalized = 0) {}








#[Pure]
public function getColorAsString() {}









#[Pure]
public function getColorCount() {}





public function setColorCount($colorCount) {}











public function isPixelSimilarQuantum($color, $fuzz) {}








#[Pure]
public function getColorQuantum() {}









public function setColorFromPixel(ImagickPixel $srcPixel) {}
}







class ImagickKernel
{








public function addKernel(ImagickKernel $imagickKernel) {}








public function addUnityKernel() {}










public static function fromBuiltin($kernelType, $kernelString) {}












public static function fromMatrix($matrix, $origin) {}








#[Pure]
public function getMatrix() {}











public function scale() {}








public function seperate() {}
}
