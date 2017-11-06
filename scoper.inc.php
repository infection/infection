<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    // By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
    // directory. You can however define which files should be scoped by defining a collection of Finders in the
    // following configuration key.
    //
    // For more see: https://github.com/humbug/php-scoper#finders-and-paths
    'finders' => [
        Finder::create()->files()->in(['src', 'app', 'bin']),
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName('/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock/')
            ->exclude([
                'doc',
                'test',
                'test_old',
                'tests',
                'Tests',
                'vendor-bin',
            ])
            ->in('vendor'),
        Finder::create()->append([
            'composer.json',
        ]),
    ],

    // When scoping PHP files, there will be scenarios where some of the code being scoped indirectly references the
    // original namespace. These will include, for example, strings or string manipulations. PHP-Scoper has limited
    // support for prefixing such strings. To circumvent that, you can define patchers to manipulate the file to your
    // heart contents.
    //
    // For more see: https://github.com/humbug/php-scoper#patchers
    'patchers' => [
        function (string $filePath, string $prefix, string $content): string {
            // Change the content here.

            return $content;
        },
    ],

    // By default, PHP-Scoper only prefixes code where the namespace is non-global. In other words, non-namespaced
    // code is not prefixed. This leaves the majority of classes, functions and constants in PHP - and most extensions,
    // untouched.
    //
    // This is not necessarily a desirable outcome for vendor dependencies which are also not namespaced. To ensure
    // they are isolated, you can configure the following which can be a list of strings or callables taking a string
    // (the class name) as an argument and return a boolean (true meaning the class is going to prefixed).
    //
    // For more, see https://github.com/humbug/php-scoper#global-namespace-whitelisting
    'global_namespace_whitelist' => [
        'AppKernel',
    ],

    // PHP-Scoper's goal is to make sure that all code for a project lies in a distinct PHP namespace. However, you
    // may want to share a common API between the bundled code of your PHAR and the consumer code. For example if
    // you have a PHPUnit PHAR with isolated code, you still want the PHAR to be able to understand the
    // PHPUnit\Framework\TestCase class.
    //
    // A way to achieve this is by specifying a list of classes to not prefix with the following configuration key. Note
    // that this does not work with functions or constants neither with classes belonging to the global namespace.
    //
    // Fore more see https://github.com/humbug/php-scoper#whitelist
    'whitelist' => [
        'PHPUnit\Framework\TestCase',
    ],
];
