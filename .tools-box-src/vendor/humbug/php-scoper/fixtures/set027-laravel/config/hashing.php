<?php

namespace _HumbugBoxb47773b41c19;

return ['driver' => 'bcrypt', 'bcrypt' => ['rounds' => env('BCRYPT_ROUNDS', 10)], 'argon' => ['memory' => 1024, 'threads' => 2, 'time' => 2]];
