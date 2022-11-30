<?php

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Pure;


















#[Pure]
function bcadd(string $num1, string $num2, ?int $scale = null): string {}


















#[Pure]
function bcsub(string $num1, string $num2, ?int $scale = null): string {}


















#[Pure]
function bcmul(string $num1, string $num2, ?int $scale = null): string {}



















#[Pure]
#[PhpStormStubsElementAvailable(to: '7.4')]
function bcdiv(string $num1, string $num2, ?int $scale = 0): ?string {}



















#[Pure]
#[PhpStormStubsElementAvailable('8.0')]
function bcdiv(string $num1, string $num2, ?int $scale = 0): string {}



















#[Pure]
#[PhpStormStubsElementAvailable(to: '7.4')]
function bcmod(string $num1, string $num2, ?int $scale = 0): ?string {}



















#[Pure]
#[PhpStormStubsElementAvailable('8.0')]
function bcmod(string $num1, string $num2, ?int $scale = 0): string {}




















#[Pure]
function bcpow(string $num, string $exponent, ?int $scale = null): string {}











#[Pure]
#[LanguageLevelTypeAware(["8.0" => "string"], default: "?string")]
function bcsqrt(string $num, ?int $scale) {}







#[LanguageLevelTypeAware(['7.3' => 'int'], default: 'bool')]
function bcscale(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.2')] int $scale,
#[PhpStormStubsElementAvailable(from: '7.3')] #[LanguageLevelTypeAware(['8.0' => 'int|null'], default: 'int')] $scale = null
) {}



















#[Pure]
function bccomp(string $num1, string $num2, ?int $scale = null): int {}























#[Pure]
#[LanguageLevelTypeAware(["8.0" => "string"], default: "?string")]
function bcpowmod(string $num, string $exponent, string $modulus, ?int $scale = null) {}
