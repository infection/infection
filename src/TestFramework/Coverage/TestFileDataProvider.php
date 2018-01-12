<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Coverage;

interface TestFileDataProvider
{
    /**
     * Provides 1) file name of the test file that contains passed as a parameter test class
     *          2) Time test was executed with
     *
     * Example for file name:
     *      param:  '\NameSpace\Sub\TestClass'
     *      return: '/path/to/NameSpace/Sub/TestClass.php'
     *
     * @param string $fullyQualifiedClassName
     *
     * @return array file path and time
     */
    public function getTestFileInfo(string $fullyQualifiedClassName): array;
}
