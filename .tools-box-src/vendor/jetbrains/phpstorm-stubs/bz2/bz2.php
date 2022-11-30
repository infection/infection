<?php

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Pure;















#[Pure]
function bzopen($file, string $mode) {}















function bzread($bz, int $length = 1024): string|false {}


















function bzwrite($bz, string $data, ?int $length): int|false {}










function bzflush($bz): bool {}










function bzclose($bz): bool {}










#[Pure]
#[LanguageLevelTypeAware(['8.1' => 'int', '8.0' => 'int|false'], default: 'int')]
function bzerrno($bz) {}










#[Pure]
#[LanguageLevelTypeAware(['8.1' => 'string', '8.0' => 'string|false'], default: 'string')]
function bzerrstr($bz) {}












#[Pure]
#[LanguageLevelTypeAware(['8.1' => 'array', '8.0' => 'array|false'], default: 'array')]
function bzerror($bz) {}























#[Pure]
function bzcompress(
string $data,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.0')] int $blocksize,
#[PhpStormStubsElementAvailable(from: '7.1')] int $block_size = 4,
int $work_factor = 0
): string|int {}


















#[Pure]
function bzdecompress(string $data, bool $use_less_memory = false): string|int|false {}
