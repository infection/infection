<?php



namespace {
use FFI\CData;
use FFI\CType;
use FFI\ParserException;








class FFI
{















public static function cdef(string $code = '', ?string $lib = null): FFI {}
























public static function load(string $filename): ?FFI {}




































public static function scope(string $name): FFI {}










public static function new($type, bool $owned = true, bool $persistent = false): ?CData {}







public static function free(CData $ptr): void {}














public static function cast($type, $ptr): ?CData {}













public static function type(string $type): ?CType {}








public static function typeof(CData $ptr): CType {}









public static function arrayType(CType $type, array $dimensions): CType {}











public static function addr(CData $ptr): CData {}







public static function sizeof($ptr): int {}







public static function alignof($ptr): int {}









public static function memcpy(CData $to, $from, int $size): void {}









public static function memcmp($ptr1, $ptr2, int $size): int {}









public static function memset(CData $ptr, int $value, int $size): void {}










public static function string(CData $ptr, ?int $size = null): string {}







public static function isNull(CData $ptr): bool {}
}
}

namespace FFI {





class Exception extends \Error {}






class ParserException extends Exception {}

/**
@mixin
@mixin
@mixin

























*/
class CData
{







private function offsetExists(int $offset) {}








private function offsetGet(int $offset) {}








private function offsetSet(int $offset, $value) {}







private function offsetUnset(int $offset) {}







private function count(): int {}
}






class CType
{



public const TYPE_VOID = 0;




public const TYPE_FLOAT = 1;




public const TYPE_DOUBLE = 2;







public const TYPE_LONGDOUBLE = 3;




public const TYPE_UINT8 = 4;




public const TYPE_SINT8 = 5;




public const TYPE_UINT16 = 6;




public const TYPE_SINT16 = 7;




public const TYPE_UINT32 = 8;




public const TYPE_SINT32 = 9;




public const TYPE_UINT64 = 10;




public const TYPE_SINT64 = 11;




public const TYPE_ENUM = 12;




public const TYPE_BOOL = 13;




public const TYPE_CHAR = 14;




public const TYPE_POINTER = 15;




public const TYPE_FUNC = 16;




public const TYPE_ARRAY = 17;




public const TYPE_STRUCT = 18;




public const ATTR_CONST = 1;




public const ATTR_INCOMPLETE_TAG = 2;




public const ATTR_VARIADIC = 4;




public const ATTR_INCOMPLETE_ARRAY = 8;




public const ATTR_VLA = 16;




public const ATTR_UNION = 32;




public const ATTR_PACKED = 64;




public const ATTR_MS_STRUCT = 128;




public const ATTR_GCC_STRUCT = 256;




public const ABI_DEFAULT = 0;




public const ABI_CDECL = 1;




public const ABI_FASTCALL = 2;




public const ABI_THISCALL = 3;




public const ABI_STDCALL = 4;




public const ABI_PASCAL = 5;




public const ABI_REGISTER = 6;




public const ABI_MS = 7;




public const ABI_SYSV = 8;




public const ABI_VECTORCALL = 9;







public function getName(): string {}




























public function getKind(): int {}







public function getSize(): int {}







public function getAlignment(): int {}







public function getAttributes(): int {}












public function getEnumKind(): int {}








public function getArrayElementType(): CType {}








public function getArrayLength(): int {}








public function getPointerType(): CType {}








public function getStructFieldNames(): array {}











public function getStructFieldOffset(string $name): int {}









public function getStructFieldType(string $name): CType {}





















public function getFuncABI(): int {}








public function getFuncReturnType(): CType {}








public function getFuncParameterCount(): int {}









public function getFuncParameterType(int $index): CType {}
}
}
