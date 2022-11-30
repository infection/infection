<?php







class SVM
{

/**
@const
*/
public const C_SVC = 0;

/**
@const
*/
public const NU_SVC = 1;

/**
@const
*/
public const ONE_CLASS = 2;

/**
@const
*/
public const EPSILON_SVR = 3;

/**
@const
*/
public const NU_SVR = 4;

/**
@const
*/
public const KERNEL_LINEAR = 0;

/**
@const
*/
public const KERNEL_POLY = 1;

/**
@const
*/
public const KERNEL_RBF = 2;

/**
@const
*/
public const KERNEL_SIGMOID = 3;

/**
@const
*/
public const KERNEL_PRECOMPUTED = 4;

/**
@const
*/
public const OPT_TYPE = 101;

/**
@const
*/
public const OPT_KERNEL_TYPE = 102;

/**
@const
*/
public const OPT_DEGREE = 103;

/**
@const
*/
public const OPT_SHRINKING = 104;

/**
@const
*/
public const OPT_PROPABILITY = 105;

/**
@const
*/
public const OPT_GAMMA = 201;

/**
@const
*/
public const OPT_NU = 202;

/**
@const
*/
public const OPT_EPS = 203;

/**
@const
*/
public const OPT_P = 204;

/**
@const
*/
public const OPT_COEF_ZERO = 205;

/**
@const
*/
public const OPT_C = 206;

/**
@const
*/
public const OPT_CACHE_SIZE = 207;









public function __construct() {}










public function crossvalidate(array $problem, int $number_of_folds): float {}








public function getOptions(): array {}










public function setOptions(array $params): bool {}











public function train(array $problem, array $weights = null): SVMModel {}
}
