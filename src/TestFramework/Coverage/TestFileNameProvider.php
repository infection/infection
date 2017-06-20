<?php

declare(strict_types=1);

namespace Infection\TestFramework\Coverage;


interface TestFileNameProvider
{
    /**
     * Provides file name of the test file that contains passed as a parameter test class
     *
     * Example:
     *      param:  '\NameSpace\Sub\TestClass'
     *      return: '/path/to/NameSpace/Sub/TestClass.php'
     *
     * @param string $fullyQualifiedClassName
     * @return string
     */
    public function getFileNameByClass(string $fullyQualifiedClassName): string;
}