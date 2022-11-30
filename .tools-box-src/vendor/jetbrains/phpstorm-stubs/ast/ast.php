<?php

namespace ast {

class Metadata
{

public $flags;


public $flagsCombinable;


public $kind;


public $name;
}


class Node
{

public $children;


public $endLineno;


public $flags;


public $kind;


public $lineno;










public function __construct(?int $kind = null, ?int $flags = null, ?array $children = null, ?int $lineno = null) {}
}





function get_kind_name(int $kind): string {}







function get_metadata(): array {}






function get_supported_versions(bool $exclude_deprecated = false): array {}




function kind_uses_flags(int $kind): bool {}










function parse_code(string $code, int $version, string $filename = 'string code'): Node {}









function parse_file(string $filename, int $version): Node {}

const AST_ARG_LIST = 128;

const AST_ARRAY = 129;

const AST_ARRAY_ELEM = 526;

const AST_ARROW_FUNC = 71;

const AST_ASSIGN = 518;

const AST_ASSIGN_OP = 520;

const AST_ASSIGN_REF = 519;

const AST_ATTRIBUTE = 547;

const AST_ATTRIBUTE_GROUP = 146;

const AST_ATTRIBUTE_LIST = 145;

const AST_BINARY_OP = 521;

const AST_BREAK = 286;

const AST_CALL = 516;

const AST_CAST = 261;

const AST_CATCH = 773;

const AST_CATCH_LIST = 135;

const AST_CLASS = 70;

const AST_CLASS_CONST = 517;

const AST_CLASS_CONST_DECL = 140;

const AST_CLASS_CONST_GROUP = 546;

const AST_CLASS_NAME = 276;

const AST_CLONE = 266;

const AST_CLOSURE = 68;

const AST_CLOSURE_USES = 137;

const AST_CLOSURE_VAR = 2049;

const AST_CONDITIONAL = 771;

const AST_CONST = 257;

const AST_CONST_DECL = 139;

const AST_CONST_ELEM = 776;

const AST_CONTINUE = 287;

const AST_DECLARE = 538;

const AST_DIM = 512;

const AST_DO_WHILE = 534;

const AST_ECHO = 283;

const AST_EMPTY = 262;

const AST_ENCAPS_LIST = 130;

const AST_EXIT = 267;

const AST_EXPR_LIST = 131;

const AST_FOR = 1024;

const AST_FOREACH = 1025;

const AST_FUNC_DECL = 67;

const AST_GLOBAL = 277;

const AST_GOTO = 285;

const AST_GROUP_USE = 545;

const AST_HALT_COMPILER = 282;

const AST_IF = 133;

const AST_IF_ELEM = 535;

const AST_INCLUDE_OR_EVAL = 269;

const AST_INSTANCEOF = 528;

const AST_ISSET = 263;

const AST_LABEL = 280;

const AST_LIST = 255;

const AST_MAGIC_CONST = 0;

const AST_MATCH = 548;

const AST_MATCH_ARM = 549;

const AST_MATCH_ARM_LIST = 147;

const AST_METHOD = 69;

const AST_METHOD_CALL = 768;

const AST_METHOD_REFERENCE = 541;

const AST_NAME = 2048;

const AST_NAMED_ARG = 550;

const AST_NAMESPACE = 542;

const AST_NAME_LIST = 141;

const AST_NEW = 527;

const AST_NULLABLE_TYPE = 2050;

const AST_NULLSAFE_METHOD_CALL = 769;

const AST_NULLSAFE_PROP = 514;

const AST_PARAM = 1280;

const AST_PARAM_LIST = 136;

const AST_POST_DEC = 274;

const AST_POST_INC = 273;

const AST_PRE_DEC = 272;

const AST_PRE_INC = 271;

const AST_PRINT = 268;

const AST_PROP = 513;

const AST_PROP_DECL = 138;

const AST_PROP_ELEM = 775;

const AST_PROP_GROUP = 774;

const AST_REF = 281;

const AST_RETURN = 279;

const AST_SHELL_EXEC = 265;

const AST_STATIC = 532;

const AST_STATIC_CALL = 770;

const AST_STATIC_PROP = 515;

const AST_STMT_LIST = 132;

const AST_SWITCH = 536;

const AST_SWITCH_CASE = 537;

const AST_SWITCH_LIST = 134;

const AST_THROW = 284;

const AST_TRAIT_ADAPTATIONS = 142;

const AST_TRAIT_ALIAS = 544;

const AST_TRAIT_PRECEDENCE = 540;

const AST_TRY = 772;

const AST_TYPE = 1;

const AST_TYPE_UNION = 144;

const AST_UNARY_OP = 270;

const AST_UNPACK = 258;

const AST_UNSET = 278;

const AST_USE = 143;

const AST_USE_ELEM = 543;

const AST_USE_TRAIT = 539;

const AST_VAR = 256;

const AST_WHILE = 533;

const AST_YIELD = 529;

const AST_YIELD_FROM = 275;
}

namespace ast\flags {

const ARRAY_ELEM_REF = 1;

const ARRAY_SYNTAX_LIST = 1;

const ARRAY_SYNTAX_LONG = 2;

const ARRAY_SYNTAX_SHORT = 3;

const BINARY_ADD = 1;

const BINARY_BITWISE_AND = 10;

const BINARY_BITWISE_OR = 9;

const BINARY_BITWISE_XOR = 11;

const BINARY_BOOL_AND = 259;

const BINARY_BOOL_OR = 258;

const BINARY_BOOL_XOR = 15;

const BINARY_COALESCE = 260;

const BINARY_CONCAT = 8;

const BINARY_DIV = 4;

const BINARY_IS_EQUAL = 18;

const BINARY_IS_GREATER = 256;

const BINARY_IS_GREATER_OR_EQUAL = 257;

const BINARY_IS_IDENTICAL = 16;

const BINARY_IS_NOT_EQUAL = 19;

const BINARY_IS_NOT_IDENTICAL = 17;

const BINARY_IS_SMALLER = 20;

const BINARY_IS_SMALLER_OR_EQUAL = 21;

const BINARY_MOD = 5;

const BINARY_MUL = 3;

const BINARY_POW = 12;

const BINARY_SHIFT_LEFT = 6;

const BINARY_SHIFT_RIGHT = 7;

const BINARY_SPACESHIP = 170;

const BINARY_SUB = 2;

const CLASS_ABSTRACT = 64;

const CLASS_ANONYMOUS = 4;

const CLASS_FINAL = 32;

const CLASS_INTERFACE = 1;

const CLASS_TRAIT = 2;

const CLOSURE_USE_REF = 1;

const DIM_ALTERNATIVE_SYNTAX = 2;

const EXEC_EVAL = 1;

const EXEC_INCLUDE = 2;

const EXEC_INCLUDE_ONCE = 4;

const EXEC_REQUIRE = 8;

const EXEC_REQUIRE_ONCE = 16;

const FUNC_GENERATOR = 16777216;

const FUNC_RETURNS_REF = 4096;

const MAGIC_CLASS = 378;

const MAGIC_DIR = 377;

const MAGIC_FILE = 376;

const MAGIC_FUNCTION = 381;

const MAGIC_LINE = 375;

const MAGIC_METHOD = 380;

const MAGIC_NAMESPACE = 382;

const MAGIC_TRAIT = 379;

const MODIFIER_ABSTRACT = 64;

const MODIFIER_FINAL = 32;

const MODIFIER_PRIVATE = 4;

const MODIFIER_PROTECTED = 2;

const MODIFIER_PUBLIC = 1;

const MODIFIER_STATIC = 16;

const NAME_FQ = 0;

const NAME_NOT_FQ = 1;

const NAME_RELATIVE = 2;

const PARAM_MODIFIER_PRIVATE = 4;

const PARAM_MODIFIER_PROTECTED = 2;

const PARAM_MODIFIER_PUBLIC = 1;

const PARAM_REF = 8;

const PARAM_VARIADIC = 16;

const PARENTHESIZED_CONDITIONAL = 1;

const RETURNS_REF = 4096;

const TYPE_ARRAY = 7;

const TYPE_BOOL = 17;

const TYPE_CALLABLE = 12;

const TYPE_DOUBLE = 5;

const TYPE_FALSE = 2;

const TYPE_ITERABLE = 13;

const TYPE_LONG = 4;

const TYPE_MIXED = 16;

const TYPE_NULL = 1;

const TYPE_OBJECT = 8;

const TYPE_STATIC = 15;

const TYPE_STRING = 6;

const TYPE_VOID = 14;

const UNARY_BITWISE_NOT = 13;

const UNARY_BOOL_NOT = 14;

const UNARY_MINUS = 262;

const UNARY_PLUS = 261;

const UNARY_SILENCE = 260;

const USE_CONST = 4;

const USE_FUNCTION = 2;

const USE_NORMAL = 1;
}
