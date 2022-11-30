<?php











const MS_TRUE = 1;
const MS_FALSE = 0;
const MS_ON = 1;
const MS_OFF = 0;
const MS_YES = 1;
const MS_NO = 0;
const MS_INCHES = 0;
const MS_FEET = 1;
const MS_MILES = 2;
const MS_METERS = 3;
const MS_KILOMETERS = 4;
const MS_DD = 5;
const MS_PIXELS = 6;
const MS_NAUTICALMILES = 8;
const MS_LAYER_POINT = 0;
const MS_LAYER_LINE = 1;
const MS_LAYER_POLYGON = 2;
const MS_LAYER_RASTER = 3;

const MS_LAYER_ANNOTATION = 4;
const MS_LAYER_QUERY = 5;
const MS_LAYER_CIRCLE = 6;
const MS_LAYER_TILEINDEX = 7;
const MS_LAYER_CHART = 8;
const MS_DEFAULT = 2;
const MS_EMBED = 3;
const MS_DELETE = 4;
const MS_GD_ALPHA = 1000;
const MS_UL = 101;
const MS_LR = 102;
const MS_UR = 103;
const MS_LL = 104;
const MS_CR = 105;
const MS_CL = 106;
const MS_UC = 107;
const MS_LC = 108;
const MS_CC = 109;
const MS_XY = 111;
const MS_AUTO = 110;
const MS_AUTO2 = 114;
const MS_FOLLOW = 112;
const MS_NONE = 113;
const MS_TINY = 0;
const MS_SMALL = 1;
const MS_MEDIUM = 2;
const MS_LARGE = 3;
const MS_GIANT = 4;
const MS_SHAPE_POINT = 0;
const MS_SHAPE_LINE = 1;
const MS_SHAPE_POLYGON = 2;
const MS_SHAPE_NULL = 3;
const MS_SHP_POINT = 1;
const MS_SHP_ARC = 3;
const MS_SHP_POLYGON = 5;
const MS_SHP_MULTIPOINT = 8;
const MS_SINGLE = 0;
const MS_MULTIPLE = 1;
const MS_NORMAL = 0;
const MS_HILITE = 1;
const MS_SELECTED = 2;
const MS_INLINE = 0;
const MS_SHAPEFILE = 1;
const MS_TILED_SHAPEFILE = 2;
const MS_SDE = 3;
const MS_OGR = 4;
const MS_TILED_OGR = 5;
const MS_POSTGIS = 6;
const MS_WMS = 7;
const MS_ORACLESPATIAL = 8;
const MS_WFS = 9;
const MS_GRATICULE = 10;
const MS_RASTER = 12;
const MS_PLUGIN = 13;
const MS_UNION = 14;
const MS_NOERR = 0;
const MS_IOERR = 1;
const MS_MEMERR = 2;
const MS_TYPEERR = 3;
const MS_SYMERR = 4;
const MS_REGEXERR = 5;
const MS_TTFERR = 6;
const MS_DBFERR = 7;
const MS_GDERR = 8;
const MS_IDENTERR = 9;
const MS_EOFERR = 10;
const MS_PROJERR = 11;
const MS_MISCERR = 12;
const MS_CGIERR = 13;
const MS_WEBERR = 14;
const MS_IMGERR = 15;
const MS_HASHERR = 16;
const MS_JOINERR = 17;
const MS_NOTFOUND = 18;
const MS_SHPERR = 19;
const MS_PARSEERR = 20;
const MS_SDEERR = 21;
const MS_OGRERR = 22;
const MS_QUERYERR = 23;
const MS_WMSERR = 24;
const MS_WMSCONNERR = 25;
const MS_ORACLESPATIALERR = 26;
const MS_WFSERR = 27;
const MS_WFSCONNERR = 28;
const MS_MAPCONTEXTERR = 29;
const MS_HTTPERR = 30;
const MS_WCSERR = 32;
const MS_SYMBOL_SIMPLE = 1000;
const MS_SYMBOL_VECTOR = 1001;
const MS_SYMBOL_ELLIPSE = 1002;
const MS_SYMBOL_PIXMAP = 1003;
const MS_SYMBOL_TRUETYPE = 1004;
const MS_IMAGEMODE_PC256 = 0;
const MS_IMAGEMODE_RGB = 1;
const MS_IMAGEMODE_RGBA = 2;
const MS_IMAGEMODE_INT16 = 3;
const MS_IMAGEMODE_FLOAT32 = 4;
const MS_IMAGEMODE_BYTE = 5;
const MS_IMAGEMODE_FEATURE = 6;
const MS_IMAGEMODE_NULL = 7;
const MS_STYLE_BINDING_SIZE = 0;
const MS_STYLE_BINDING_ANGLE = 2;
const MS_STYLE_BINDING_COLOR = 3;
const MS_STYLE_BINDING_OUTLINECOLOR = 4;
const MS_STYLE_BINDING_SYMBOL = 5;
const MS_STYLE_BINDING_WIDTH = 1;
const MS_LABEL_BINDING_SIZE = 0;
const MS_LABEL_BINDING_ANGLE = 1;
const MS_LABEL_BINDING_COLOR = 2;
const MS_LABEL_BINDING_OUTLINECOLOR = 3;
const MS_LABEL_BINDING_FONT = 4;
const MS_LABEL_BINDING_PRIORITY = 5;
const MS_LABEL_BINDING_POSITION = 6;
const MS_LABEL_BINDING_SHADOWSIZEX = 7;
const MS_LABEL_BINDING_SHADOWSIZEY = 8;
const MS_ALIGN_LEFT = 0;
const MS_ALIGN_CENTER = 1;
const MS_ALIGN_RIGHT = 2;
const MS_GET_REQUEST = 0;
const MS_POST_REQUEST = 1;








function ms_GetVersion() {}








function ms_GetVersionInt() {}









function ms_iogetStdoutBufferBytes() {}







function ms_iogetstdoutbufferstring() {}







function ms_ioinstallstdinfrombuffer() {}







function ms_ioinstallstdouttobuffer() {}







function ms_ioresethandlers() {}








function ms_iostripstdoutbuffercontenttype() {}







function ms_iostripstdoutbuffercontentheaders() {}










function ms_TokenizeMap($map_file_name) {}






function ms_GetErrorObj() {}








function ms_ResetErrorList() {}





final class classObj
{



public $group;




public $keyimage;






public $label;




public $maxscaledenom;




public $metadata;




public $minscaledenom;




public $name;






public $numlabels;






public $numstyles;






public $status;




public $template;




public $title;




public $type;








final public function __construct(layerObj $layer, classObj $class) {}








final public function ms_newClassObj(layerObj $layer, classObj $class) {}









final public function addLabel(labelObj $label) {}







final public function convertToString() {}








final public function createLegendIcon($width, $height) {}








final public function deletestyle($index) {}












final public function drawLegendIcon($width, $height, imageObj $im, $dstX, $dstY) {}








final public function free() {}







final public function getExpressionString() {}










final public function getLabel($index) {}










final public function getMetaData($name) {}








final public function getStyle($index) {}






final public function getTextString() {}











final public function movestyledown($index) {}











final public function movestyleup($index) {}










final public function removeLabel($index) {}







final public function removeMetaData($name) {}








final public function set($property_name, $new_value) {}








final public function setExpression($expression) {}








final public function setMetaData($name, $value) {}







final public function settext($text) {}










final public function updateFromString($snippet) {}
}




final class clusterObj
{



public $buffer;




public $maxdistance;




public $region;







final public function convertToString() {}







final public function getFilterString() {}







final public function getGroupString() {}







final public function setFilter($expression) {}







final public function setGroup($expression) {}
}




final class colorObj
{



public $red;




public $green;




public $blue;




public $alpha;







final public function toHex() {}








final public function setHex($hex) {}
}

final class errorObj
{





public $code;




public $message;




public $routine;
}












final class gridObj
{



public $labelformat;




public $maxacrs;




public $maxinterval;




public $maxsubdivide;




public $minarcs;




public $mininterval;




public $minsubdivide;








final public function set($property_name, $new_value) {}
}









final class hashTableObj
{





final public function clear() {}








final public function get($key) {}









final public function nextkey($previousKey) {}







final public function remove($key) {}








final public function set($key, $value) {}
}




final class imageObj
{





public $width;






public $height;






public $resolution;






public $resolutionfactor;




public $imagepath;




public $imageurl;






















final public function pasteImage(imageObj $srcImg, $transparentColorHex, $dstX, $dstY, $angle) {}

















final public function saveImage($filename, mapObj $oMap) {}








final public function saveWebImage() {}
}

final class labelcacheMemberObj
{





public $classindex;






public $featuresize;






public $layerindex;






public $markerid;






public $numstyles;






public $shapeindex;






public $status;






public $text;






public $tileindex;
}

final class labelcacheObj
{






final public function freeCache() {}
}




final class labelObj
{



public $align;




public $angle;




public $anglemode;




public $antialias;




public $autominfeaturesize;






public $backgroundcolor;






public $backgroundshadowcolor;






public $backgroundshadowsizex;






public $backgroundshadowsizey;




public $buffer;




public $color;




public $encoding;




public $font;




public $force;




public $maxlength;




public $maxsize;




public $mindistance;




public $minfeaturesize;




public $minlength;




public $minsize;




public $numstyles;




public $offsetx;




public $offsety;




public $outlinecolor;




public $outlinewidth;




public $partials;




public $position;




public $priority;




public $repeatdistance;




public $shadowcolor;




public $shadowsizex;




public $shadowsizey;




public $size;




public $wrap;

final public function __construct() {}







final public function convertToString() {}








final public function deleteStyle($index) {}








final public function free() {}












final public function getBinding($labelbinding) {}






final public function getExpressionString() {}








final public function getStyle($index) {}






final public function getTextString() {}











final public function moveStyleDown($index) {}











final public function moveStyleUp($index) {}










final public function removeBinding($labelbinding) {}








final public function set($property_name, $new_value) {}













final public function setBinding($labelbinding, $value) {}







final public function setExpression($expression) {}







final public function setText($text) {}







final public function updateFromString($snippet) {}
}









final class layerObj
{



public $annotate;




public $bindvals;




public $classgroup;




public $classitem;




public $cluster;




public $connection;






public $connectiontype;




public $data;




public $debug;






public $dump;




public $filteritem;




public $footer;






public $grid;




public $group;




public $header;






public $index;




public $labelcache;




public $labelitem;




public $labelmaxscaledenom;




public $labelminscaledenom;




public $labelrequires;




public $mask;




public $maxfeatures;




public $maxscaledenom;




public $metadata;




public $minscaledenom;




public $name;




public $num_processing;






public $numclasses;




public $offsite;




public $opacity;




public $projection;




public $postlabelcache;




public $requires;




public $sizeunits;




public $startindex;






public $status;




public $styleitem;




public $symbolscaledenom;




public $template;




public $tileindex;




public $tileitem;




public $tolerance;




public $toleranceunits;




public $transform;




public $type;








final public function ms_newLayerObj(mapObj $map, layerObj $layer) {}








final public function addFeature(shapeObj $shape) {}














final public function applySLD($sldxml, $namedlayer) {}













final public function applySLDURL($sldurl, $namedlayer) {}






final public function clearProcessing() {}






final public function close() {}







final public function convertToString() {}








final public function draw(imageObj $image) {}











final public function drawQuery(imageObj $image) {}








final public function free() {}







final public function generateSLD() {}







final public function getClass($classIndex) {}












final public function getClassIndex($shape, $classgroup, $numclasses) {}












final public function getExtent() {}







final public function getFilterString() {}







final public function getGridIntersectionCoordinates() {}







final public function getItems() {}










final public function getMetaData($name) {}






final public function getNumResults() {}







final public function getProcessing() {}







final public function getProjection() {}









final public function getResult($index) {}






final public function getResultsBounds() {}



















final public function getShape(resultObj $result) {}


















final public function getWMSFeatureInfoURL($clickX, $clickY, $featureCount, $infoFormat) {}







final public function isVisible() {}











final public function moveclassdown($index) {}











final public function moveclassup($index) {}







final public function open() {}


















final public function nextShape() {}




















final public function queryByAttributes($qitem, $qstring, $mode) {}













final public function queryByFeatures($slayer) {}






















final public function queryByPoint(pointObj $point, $mode, $buffer) {}















final public function queryByRect(rectObj $rect) {}












final public function queryByShape(shapeObj $shape) {}










final public function removeClass($index) {}







final public function removeMetaData($name) {}








final public function set($property_name, $new_value) {}














final public function setConnectionType($connectiontype, $plugin_library) {}







final public function setFilter($expression) {}















final public function setMetaData($name, $value) {}









final public function setProjection($proj_params) {}










final public function setWKTProjection($proj_params) {}



















final public function updateFromString($snippet) {}
}




final class legendObj
{



public $height;




public $imagecolor;




public $keysizex;




public $keysizey;




public $keyspacingx;




public $keyspacingy;




public $label;






public $outlinecolor;






public $position;






public $postlabelcache;






public $status;




public $template;




public $width;







final public function convertToString() {}








final public function free() {}








final public function set($property_name, $new_value) {}







final public function updateFromString($snippet) {}
}

final class lineObj
{





public $numpoints;

final public function __construct() {}






final public function ms_newLineObj() {}







final public function add(pointObj $point) {}












final public function addXY($x, $y, $m) {}













final public function addXYZ($x, $y, $z, $m) {}







final public function point($i) {}









final public function project(projectionObj $in, projectionObj $out) {}
}

final class mapObj
{



public $cellsize;




public $debug;






public $defresolution;






public $extent;






public $fontsetfilename;






public $height;




public $imagecolor;




public $keysizex;




public $keysizey;




public $keyspacingx;




public $keyspacingy;







public $labelcache;




public $legend;




public $mappath;




public $maxsize;




public $metadata;




public $name;






public $numlayers;




public $outputformat;






public $numoutputformats;




public $projection;




public $querymap;




public $reference;






public $resolution;




public $scalebar;






public $scaledenom;




public $shapepath;




public $status;






public $symbolsetfilename;






public $units;




public $web;






public $width;














final public function __construct($map_file_name, $new_map_path) {}








final public function ms_newMapObjFromString($map_file_string, $new_map_path) {}










final public function applyconfigoptions() {}









final public function applySLD($sldxml) {}










final public function applySLDURL($sldurl) {}









final public function convertToString() {}






final public function draw() {}







final public function drawLabelCache(imageObj $image) {}






final public function drawLegend() {}






final public function drawQuery() {}






final public function drawReferenceMap() {}






final public function drawScaleBar() {}










final public function embedLegend(imageObj $image) {}










final public function embedScalebar(imageObj $image) {}











final public function free() {}







final public function generateSLD() {}







final public function getAllGroupNames() {}







final public function getAllLayerNames() {}








final public function getColorbyIndex($iCloIndex) {}








final public function getConfigOption($key) {}













final public function getLabel($index) {}







final public function getLayer($index) {}








final public function getLayerByName($layer_name) {}







final public function getLayersDrawingOrder() {}








final public function getLayersIndexByGroup($groupname) {}










final public function getMetaData($name) {}






final public function getNumSymbols() {}







final public function getProjection() {}







final public function getSymbolByName($symbol_name) {}













final public function getSymbolObjectById($symbolid) {}
















final public function loadMapContext($filename, $unique_layer_name) {}













final public function loadOWSParameters(OwsrequestObj $request, $version) {}








final public function moveLayerDown($layerindex) {}








final public function moveLayerUp($layerindex) {}









final public function offsetExtent($x, $y) {}
















final public function owsDispatch(OwsrequestObj $request) {}






final public function prepareImage() {}






final public function prepareQuery() {}









final public function processLegendTemplate(array $params) {}













final public function processQueryTemplate(array $params, $generateimages) {}























final public function processTemplate(array $params, $generateimages) {}












final public function queryByFeatures($slayer) {}













final public function queryByIndex($layerindex, $tileindex, $shapeindex, $addtoquery) {}






















final public function queryByPoint(pointObj $point, $mode, $buffer) {}















final public function queryByRect(rectObj $rect) {}












final public function queryByShape(shapeObj $shape) {}









final public function removeLayer($nIndex) {}








final public function removeMetaData($name) {}









final public function save($filename) {}










final public function saveMapContext($filename) {}













final public function saveQuery($filename, $results) {}












final public function scaleExtent($zoomfactor, $minscaledenom, $maxscaledenom) {}













final public function selectOutputFormat($type) {}








final public function appendOutputFormat(outputFormatObj $outputFormat) {}








final public function removeOutputFormat($name) {}







final public function getOutputFormat($index) {}








final public function set($property_name, $new_value) {}








final public function setCenter(pointObj $center) {}








final public function setConfigOption($key, $value) {}











final public function setExtent($minx, $miny, $maxx, $maxy) {}











final public function setFontSet($fileName) {}









final public function setMetaData($name, $value) {}














final public function setProjection($proj_params, $bSetUnitsAndExtents) {}











final public function setRotation($rotation_angle) {}












final public function setSize($width, $height) {}







final public function setSymbolSet($fileName) {}











final public function setWKTProjection($proj_params, $bSetUnitsAndExtents) {}






















final public function zoomPoint($nZoomFactor, pointObj $oPixelPos, $nImageWidth, $nImageHeight, rectObj $oGeorefExt) {}
















final public function zoomRectangle(rectObj $oPixelExt, $nImageWidth, $nImageHeight, rectObj $oGeorefExt) {}
























final public function zoomScale($nScaleDenom, pointObj $oPixelPos, $nImageWidth, $nImageHeight, rectObj $oGeorefExt, rectObj $oMaxGeorefExt) {}
}






final class outputformatObj
{



public $driver;




public $extension;






public $imagemode;




public $mimetype;




public $name;




public $renderer;




public $transparent;








final public function getOption($property_name) {}








final public function set($property_name, $new_value) {}










final public function setOption($property_name, $new_value) {}








final public function validate() {}
}

final class OwsrequestObj
{





public $numparams;






public $type;





final public function __construct() {}













final public function addParameter($name, $value) {}








final public function getName($index) {}








final public function getValue($index) {}







final public function getValueByName($name) {}








final public function loadParams() {}










final public function setParameter($name, $value) {}
}

final class pointObj
{



public $x;




public $y;






public $z;






public $m;

final public function __construct() {}






final public function ms_newPointObj() {}









final public function distanceToLine(pointObj $p1, pointObj $p2) {}







final public function distanceToPoint(pointObj $poPoint) {}







final public function distanceToShape(shapeObj $shape) {}














final public function draw(mapObj $map, layerObj $layer, imageObj $img, $class_index, $text) {}









final public function project(projectionObj $in, projectionObj $out) {}












final public function setXY($x, $y, $m) {}













final public function setXYZ($x, $y, $z, $m) {}
}

final class projectionObj
{
















final public function __construct($projectionString) {}







final public function ms_newProjectionObj($projectionString) {}






final public function getUnits() {}
}





final class querymapObj
{



public $color;




public $height;




public $width;






public $style;







final public function convertToString() {}







final public function free() {}








final public function set($property_name, $new_value) {}








final public function updateFromString($snippet) {}
}





final class rectObj
{



public $minx;




public $miny;




public $maxx;




public $maxy;




final public function __construct() {}






final public function ms_newRectObj() {}














final public function draw(mapObj $map, layerObj $layer, imageObj $img, $class_index, $text) {}








final public function fit($width, $height) {}









final public function project(projectionObj $in, projectionObj $out) {}








final public function set($property_name, $new_value) {}










final public function setextent($minx, $miny, $maxx, $maxy) {}
}




final class referenceMapObj
{



public $color;




public $height;




public $extent;




public $image;




public $marker;




public $markername;




public $markersize;




public $maxboxsize;




public $minboxsize;




public $outlinecolor;




public $status;




public $width;







final public function convertToString() {}








final public function free() {}








final public function set($property_name, $new_value) {}








final public function updateFromString($snippet) {}
}

final class resultObj
{





public $classindex;






public $resultindex;






public $shapeindex;






public $tileindex;






final public function __construct($shapeindex) {}
}




final class scalebarObj
{



public $align;




public $backgroundcolor;




public $color;




public $height;




public $imagecolor;




public $intervals;




public $label;




public $outlinecolor;






public $position;




public $postlabelcache;






public $status;




public $style;




public $units;




public $width;







final public function convertToString() {}








final public function free() {}








final public function set($property_name, $new_value) {}










final public function setImageColor($red, $green, $blue) {}







final public function updateFromString($snippet) {}
}

final class shapefileObj
{





public $bounds;






public $numshapes;






public $source;






public $type;












final public function __construct($filename, $type) {}








final public function ms_newShapefileObj($filename, $type) {}







final public function addPoint(pointObj $point) {}







final public function addShape(shapeObj $shape) {}













final public function free() {}







final public function getExtent($i) {}







final public function getPoint($i) {}







final public function getShape($i) {}








final public function getTransformed(mapObj $map, $i) {}
}

final class shapeObj
{





public $bounds;




public $classindex;




public $index;






public $numlines;






public $numvalues;






public $tileindex;




public $text;






public $type;






public $values;








final public function __construct($type) {}







final public function ms_shapeObjFromWkt($wkt) {}







final public function add(lineObj $line) {}











final public function boundary() {}










final public function containsShape(shapeObj $shape2) {}








final public function convexhull() {}







final public function contains(pointObj $point) {}









final public function crosses(shapeObj $shape) {}









final public function difference(shapeObj $shape) {}









final public function disjoint(shapeObj $shape) {}










final public function draw(mapObj $map, layerObj $layer, imageObj $img) {}









final public function equals(shapeObj $shape) {}







final public function free() {}







final public function getArea() {}







final public function getCentroid() {}







final public function getLabelPoint() {}











final public function getLength() {}








final public function getPointUsingMeasure($m) {}








final public function getValue(layerObj $layer, $filedname) {}









final public function intersection(shapeObj $shape) {}







final public function intersects(shapeObj $shape) {}







final public function line($i) {}









final public function overlaps(shapeObj $shape) {}









final public function project(projectionObj $in, projectionObj $out) {}








final public function set($property_name, $new_value) {}








final public function setBounds() {}









final public function simplify($tolerance) {}









final public function symdifference(shapeObj $shape) {}









final public function topologyPreservingSimplify($tolerance) {}









final public function touches(shapeObj $shape) {}






final public function toWkt() {}










final public function union(shapeObj $shape) {}










final public function within(shapeObj $shape2) {}
}




final class styleObj
{



public $angle;




public $antialias;




public $backgroundcolor;




public $color;




public $maxsize;




public $maxvalue;




public $maxwidth;




public $minsize;




public $minvalue;




public $minwidth;




public $offsetx;




public $offsety;






public $opacity;




public $outlinecolor;




public $rangeitem;




public $size;




public $symbol;




public $symbolname;




public $width;








final public function __construct(labelObj $label, styleObj $style) {}








final public function ms_newStyleObj(classObj $class, styleObj $style) {}







final public function convertToString() {}








final public function free() {}











final public function getBinding($stylebinding) {}




final public function getGeomTransform() {}










final public function removeBinding($stylebinding) {}








final public function set($property_name, $new_value) {}













final public function setBinding($stylebinding, $value) {}





final public function setGeomTransform($value) {}







final public function updateFromString($snippet) {}
}

final class symbolObj
{



public $antialias;




public $character;




public $filled;




public $font;






public $imagepath;







public $inmapfile;






public $patternlength;




public $position;




public $name;






public $numpoints;




public $sizex;




public $sizey;




public $transparent;




public $transparentcolor;












final public function __construct(mapObj $map, $symbolname) {}








final public function ms_newSymbolObj(mapObj $map, $symbolname) {}








final public function free() {}







final public function getPatternArray() {}








final public function getPointsArray() {}








final public function set($property_name, $new_value) {}








final public function setImagePath($filename) {}








final public function setPattern(array $int) {}















final public function setPoints(array $double) {}
}




final class webObj
{



public $browseformat;






public $empty;






public $error;






public $extent;




public $footer;




public $header;




public $imagepath;




public $imageurl;




public $legendformat;




public $log;




public $maxscaledenom;




public $maxtemplate;




public $metadata;




public $minscaledenom;




public $mintemplate;




public $queryformat;




public $template;




public $temppath;







final public function convertToString() {}








final public function free() {}








final public function set($property_name, $new_value) {}








final public function updateFromString($snippet) {}
}
