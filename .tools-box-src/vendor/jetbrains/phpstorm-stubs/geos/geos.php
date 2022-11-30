<?php




define('GEOSBUF_CAP_ROUND', 1);

define('GEOSBUF_CAP_FLAT', 2);

define('GEOSBUF_CAP_SQUARE', 3);

define('GEOSBUF_JOIN_ROUND', 1);

define('GEOSBUF_JOIN_MITRE', 2);

define('GEOSBUF_JOIN_BEVEL', 3);

define('GEOS_POINT', 0);

define('GEOS_LINESTRING', 1);

define('GEOS_LINEARRING', 2);

define('GEOS_POLYGON', 3);

define('GEOS_MULTIPOINT', 4);

define('GEOS_MULTILINESTRING', 5);

define('GEOS_MULTIPOLYGON', 6);

define('GEOS_GEOMETRYCOLLECTION', 7);

define('GEOSVALID_ALLOW_SELFTOUCHING_RING_FORMING_HOLE', 1);

define('GEOSRELATE_BNR_MOD2', 1);

define('GEOSRELATE_BNR_OGC', 1);

define('GEOSRELATE_BNR_ENDPOINT', 2);

define('GEOSRELATE_BNR_MULTIVALENT_ENDPOINT', 3);

define('GEOSRELATE_BNR_MONOVALENT_ENDPOINT', 4);




function GEOSVersion(): string {}







function GEOSSharedPaths(GEOSGeometry $geom1, GEOSGeometry $geom2): GEOSGeometry {}






function GEOSLineMerge(GEOSGeometry $geom): array {}







function GEOSRelateMatch(string $matrix, string $pattern): bool {}






















function GEOSPolygonize(GEOSGeometry $geom): array {}





class GEOSWKTReader
{



public function __construct() {}






public function read(string $wkt): GEOSGeometry {}
}





class GEOSWKTWriter
{



public function __construct() {}






public function write(GEOSGeometry $geom): string {}




public function setTrim(bool $trim): void {}




public function setRoundingPrecision(int $precision): void {}





public function setOutputDimension(int $dimension): void {}




public function getOutputDimension(): int {}




public function setOld3D(bool $old3d): void {}
}





class GEOSGeometry
{



public function __construct() {}





public function __toString(): string {}






public function project(GEOSGeometry $geom): GEOSGeometry {}







public function interpolate(float $distance, bool $normalized = false): GEOSGeometry {}



























public function buffer(float $distance, array $styleArray = [
'quad_segs' => 8,
'endcap' => GEOSBUF_CAP_ROUND,
'join' => GEOSBUF_JOIN_ROUND,
'mitre_limit' => 5.0,
'single_sided' => false
]): GEOSGeometry {}



















public function offsetCurve(float $distance, array $styleArray = [
'quad_segs' => 8,
'join' => GEOSBUF_JOIN_ROUND,
'mitre_limit' => 5.0
]): GEOSGeometry {}





public function envelope(): GEOSGeometry {}






public function intersection(GEOSGeometry $geom): GEOSGeometry {}





public function convexHull(): GEOSGeometry {}






public function difference(GEOSGeometry $geom): GEOSGeometry {}






public function symDifference(GEOSGeometry $geom): GEOSGeometry {}





public function boundary(): GEOSGeometry {}






public function union(GEOSGeometry $geom = null): GEOSGeometry {}





public function pointOnSurface(): GEOSGeometry {}





public function centroid(): GEOSGeometry {}







public function relate(GEOSGeometry $geom, string $pattern = null) {}







public function relateBoundaryNodeRule(GEOSGeometry $geom, int $rule = GEOSRELATE_BNR_OGC): string {}







public function simplify(float $tolerance, bool $preserveTopology = false): GEOSGeometry {}





public function normalize(): GEOSGeometry {}







public function setPrecision(float $gridSize, int $flags = 0): GEOSGeometry {}




public function getPrecision(): float {}





public function extractUniquePoints(): GEOSGeometry {}






public function disjoint(GEOSGeometry $geom): bool {}






public function touches(GEOSGeometry $geom): bool {}






public function intersects(GEOSGeometry $geom): bool {}






public function crosses(GEOSGeometry $geom): bool {}






public function within(GEOSGeometry $geom): bool {}






public function contains(GEOSGeometry $geom): bool {}






public function overlaps(GEOSGeometry $geom): bool {}






public function covers(GEOSGeometry $geom): bool {}






public function coveredBy(GEOSGeometry $geom): bool {}






public function equals(GEOSGeometry $geom): bool {}







public function equalsExact(GEOSGeometry $geom, float $tolerance = 0): bool {}





public function isEmpty(): bool {}





public function checkValidity(): array {}





public function isSimple(): bool {}





public function isRing(): bool {}





public function hasZ(): bool {}





public function isClosed(): bool {}





public function typeName(): string {}





public function typeId(): int {}




public function getSRID(): int {}





public function setSRID(int $srid): void {}





public function numGeometries(): int {}






public function geometryN(int $n): GEOSGeometry {}





public function numInteriorRings(): int {}





public function numPoints(): int {}





public function getX(): float {}





public function getY(): float {}






public function interiorRingN(int $n): GEOSGeometry {}





public function exteriorRing(): GEOSGeometry {}





public function numCoordinates(): int {}





public function dimension(): int {}





public function coordinateDimension(): int {}






public function pointN(int $n): GEOSGeometry {}





public function startPoint(): GEOSGeometry {}





public function endPoint(): GEOSGeometry {}





public function area(): float {}





public function length(): float {}






public function distance(GEOSGeometry $geom): float {}






public function hausdorffDistance(GEOSGeometry $geom): float {}






public function snapTo(GEOSGeometry $geom, float $tolerance): GEOSGeometry {}





public function node(): GEOSGeometry {}








public function delaunayTriangulation(float $tolerance = 0.0, bool $onlyEdges = false): GEOSGeometry {}









public function voronoiDiagram(float $tolerance = 0.0, bool $onlyEdges = false, GEOSGeometry $extent = null): GEOSGeometry {}









public function clipByRect(float $xmin, float $ymin, float $xmax, float $ymax): GEOSGeometry {}
}





class GEOSWKBWriter
{



public function __construct() {}




public function getOutputDimension(): int {}





public function setOutputDimension(int $dimension): void {}




public function getByteOrder(): int {}





public function setByteOrder(int $byteOrder): void {}




public function getIncludeSRID(): int {}





public function setIncludeSRID(int $srid): void {}






public function write(GEOSGeometry $geom): string {}






public function writeHEX(GEOSGeometry $geom): string {}
}





class GEOSWKBReader
{



public function __construct() {}






public function read(string $wkb): GEOSGeometry {}






public function readHEX(string $wkb): GEOSGeometry {}
}
