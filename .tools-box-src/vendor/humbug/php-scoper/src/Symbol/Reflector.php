<?php

/**
@noinspection */
declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol;

use _HumbugBoxb47773b41c19\JetBrains\PHPStormStub\PhpStormStubsMap;
use function array_keys;
use function array_merge;
final class Reflector
{
    private const MISSING_CLASSES = ['MongoInsertBatch', 'MongoDeleteBatch'];
    private const MISSING_FUNCTIONS = ['tideways_xhprof_enable', 'tideways_xhprof_disable', 'bson_encode', 'bson_decode'];
    private const MISSING_CONSTANTS = ['STDIN', 'STDOUT', 'STDERR', 'true', 'TRUE', 'false', 'FALSE', 'null', 'NULL', 'T_NAME_QUALIFIED', 'T_NAME_FULLY_QUALIFIED', 'T_NAME_RELATIVE', 'T_MATCH', 'T_NULLSAFE_OBJECT_OPERATOR', 'T_ATTRIBUTE', 'T_ENUM', 'T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG', 'T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG', 'T_READONLY', 'TIDEWAYS_XHPROF_FLAGS_MEMORY', 'TIDEWAYS_XHPROF_FLAGS_MEMORY_MU', 'TIDEWAYS_XHPROF_FLAGS_MEMORY_PMU', 'TIDEWAYS_XHPROF_FLAGS_CPU', 'TIDEWAYS_XHPROF_FLAGS_NO_BUILTINS', 'TIDEWAYS_XHPROF_FLAGS_MEMORY_ALLOC', 'TIDEWAYS_XHPROF_FLAGS_MEMORY_ALLOC_AS_MU', 'MONGODB_VERSION', 'MONGODB_STABILITY'];
    public static function createWithPhpStormStubs() : self
    {
        return new self(self::createSymbolList(array_keys(PhpStormStubsMap::CLASSES), self::MISSING_CLASSES), self::createSymbolList(array_keys(PhpStormStubsMap::FUNCTIONS), self::MISSING_FUNCTIONS), self::createConstantSymbolList(array_keys(PhpStormStubsMap::CONSTANTS), self::MISSING_CONSTANTS));
    }
    public static function createEmpty() : self
    {
        return new self(SymbolRegistry::create(), SymbolRegistry::create(), SymbolRegistry::createForConstants());
    }
    private function __construct(private SymbolRegistry $classes, private SymbolRegistry $functions, private SymbolRegistry $constants)
    {
    }
    public function withAdditionalSymbols(SymbolRegistry $classNames, SymbolRegistry $functionNames, SymbolRegistry $constantNames) : self
    {
        return new self($this->classes->merge($classNames), $this->functions->merge($functionNames), $this->constants->merge($constantNames));
    }
    public function isClassInternal(string $name) : bool
    {
        return $this->classes->matches($name);
    }
    public function isFunctionInternal(string $name) : bool
    {
        return $this->functions->matches($name);
    }
    public function isConstantInternal(string $name) : bool
    {
        return $this->constants->matches($name);
    }
    private static function createSymbolList(array ...$sources) : SymbolRegistry
    {
        return SymbolRegistry::create(array_merge(...$sources));
    }
    private static function createConstantSymbolList(array ...$sources) : SymbolRegistry
    {
        return SymbolRegistry::createForConstants(array_merge(...$sources));
    }
}
