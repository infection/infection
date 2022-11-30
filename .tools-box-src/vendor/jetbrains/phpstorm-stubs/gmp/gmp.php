<?php


use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Pure;




















#[Pure]
function gmp_init(string|int $num, int $base = 0): GMP {}









#[Pure]
function gmp_intval(GMP|string|int $num): int {}










function gmp_random_seed(GMP|string|int $seed): void {}














#[Pure]
function gmp_strval(GMP|string|int $num, int $base = 10): string {}
















#[Pure]
function gmp_add(GMP|string|int $num1, GMP|string|int $num2): GMP {}
















#[Pure]
function gmp_sub(GMP|string|int $num1, GMP|string|int $num2): GMP {}
















#[Pure]
function gmp_mul(GMP|string|int $num1, GMP|string|int $num2): GMP {}























#[Pure]
function gmp_div_qr(GMP|string|int $num1, GMP|string|int $num2, int $rounding_mode = GMP_ROUND_ZERO): array {}






















#[Pure]
function gmp_div_q(GMP|string|int $num1, GMP|string|int $num2, int $rounding_mode = GMP_ROUND_ZERO): GMP {}




















#[Pure]
function gmp_div_r(GMP|string|int $num1, GMP|string|int $num2, int $rounding_mode = GMP_ROUND_ZERO): GMP {}






















#[Pure]
function gmp_div(GMP|string|int $num1, GMP|string|int $num2, int $rounding_mode = GMP_ROUND_ZERO): GMP {}













#[Pure]
function gmp_mod(GMP|string|int $num1, GMP|string|int $num2): GMP {}
















#[Pure]
function gmp_divexact(GMP|string|int $num1, GMP|string|int $num2): GMP {}








#[Pure]
function gmp_neg(GMP|string|int $num): GMP {}








#[Pure]
function gmp_abs(GMP|string|int $num): GMP {}











#[Pure]
function gmp_fact(GMP|string|int $num): GMP {}








#[Pure]
function gmp_sqrt(GMP|string|int $num): GMP {}














#[Pure]
function gmp_sqrtrem(GMP|string|int $num): array {}















#[Pure]
function gmp_pow(GMP|string|int $num, int $exponent): GMP {}





















#[Pure]
function gmp_powm(GMP|string|int $num, GMP|string|int $exponent, GMP|string|int $modulus): GMP {}












#[Pure]
function gmp_perfect_square(GMP|string|int $num): bool {}






















#[Pure]
function gmp_prob_prime(GMP|string|int $num, int $repetitions = 10): int {}









function gmp_random_bits(int $bits): GMP {}








function gmp_random_range(GMP|string|int $min, GMP|string|int $max): GMP {}











#[Pure]
function gmp_gcd(GMP|string|int $num1, GMP|string|int $num2): GMP {}










#[Pure]
function gmp_gcdext(GMP|string|int $num1, GMP|string|int $num2): array {}










#[Pure]
function gmp_invert(GMP|string|int $num1, GMP|string|int $num2): GMP|false {}













#[Pure]
function gmp_jacobi(GMP|string|int $num1, GMP|string|int $num2): int {}













#[Pure]
function gmp_legendre(GMP|string|int $num1, GMP|string|int $num2): int {}












#[Pure]
function gmp_cmp(GMP|string|int $num1, GMP|string|int $num2): int {}










#[Pure]
function gmp_sign(GMP|string|int $num): int {}

/**
@removed










*/
#[Deprecated(reason: "Use see gmp_random_bits() or see gmp_random_range() instead", since: "7.2")]
function gmp_random($limiter = 20) {}










#[Pure]
function gmp_and(GMP|string|int $num1, GMP|string|int $num2): GMP {}










#[Pure]
function gmp_or(GMP|string|int $num1, GMP|string|int $num2): GMP {}








#[Pure]
function gmp_com(GMP|string|int $num): GMP {}










#[Pure]
function gmp_xor(GMP|string|int $num1, GMP|string|int $num2): GMP {}


















function gmp_setbit(GMP $num, int $index, bool $value = true): void {}










function gmp_clrbit(GMP $num, int $index): void {}















#[Pure]
function gmp_scan0(GMP|string|int $num1, int $start): int {}















#[Pure]
function gmp_scan1(GMP|string|int $num1, int $start): int {}











#[Pure]
function gmp_testbit(GMP|string|int $num, int $index): bool {}








#[Pure]
function gmp_popcount(GMP|string|int $num): int {}
















#[Pure]
function gmp_hamdist(GMP|string|int $num1, GMP|string|int $num2): int {}











#[Pure]
function gmp_import(string $data, int $word_size = 1, int $flags = GMP_MSW_FIRST|GMP_NATIVE_ENDIAN): GMP {}











#[Pure]
function gmp_export(GMP|string|int $num, int $word_size = 1, int $flags = GMP_MSW_FIRST|GMP_NATIVE_ENDIAN): string {}










#[Pure]
function gmp_root(GMP|string|int $num, int $nth): GMP {}











#[Pure]
function gmp_rootrem(GMP|string|int $num, int $nth): array {}









#[Pure]
function gmp_nextprime(GMP|string|int $num): GMP {}












#[Pure]
function gmp_binomial(GMP|string|int $n, int $k): GMP {}












#[Pure]
function gmp_kronecker(GMP|string|int $num1, GMP|string|int $num2): int {}












#[Pure]
function gmp_lcm(GMP|string|int $num1, GMP|string|int $num2): GMP {}











#[Pure]
function gmp_perfect_power(GMP|string|int $num): bool {}

define('GMP_ROUND_ZERO', 0);
define('GMP_ROUND_PLUSINF', 1);
define('GMP_ROUND_MINUSINF', 2);
define('GMP_MSW_FIRST', 1);
define('GMP_LSW_FIRST', 2);
define('GMP_LITTLE_ENDIAN', 4);
define('GMP_BIG_ENDIAN', 8);
define('GMP_NATIVE_ENDIAN', 16);





define('GMP_VERSION', "6.2.1");

define('GMP_MPIR_VERSION', '3.0.0');

class GMP implements Serializable
{





public function serialize() {}

public function __serialize(): array {}









public function unserialize($serialized) {}

public function __unserialize(array $data): void {}
}

