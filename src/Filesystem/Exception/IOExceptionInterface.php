<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Filesystem\Exception;

interface IOExceptionInterface
{
    /**
     * Returns the associated path for the exception.
     *
     * @return string The path
     */
    public function getPath();
}
