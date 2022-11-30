<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\PasswordException;
function password_hash(string $password, $algo, array $options = null) : string
{
    \error_clear_last();
    if ($options !== null) {
        $result = \password_hash($password, $algo, $options);
    } else {
        $result = \password_hash($password, $algo);
    }
    if ($result === \false) {
        throw PasswordException::createFromPhpError();
    }
    return $result;
}
