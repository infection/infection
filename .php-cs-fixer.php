<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$header = \trim(\sprintf(
    'This code is licensed under the BSD 3-Clause License.%s',
    \substr(
        \file_get_contents('LICENSE'),
        \strlen('BSD 3-Clause License'),
    ),
));

$finder = Finder::create()
    ->in(__DIR__)
    ->exclude([
        '.box_dump',
        '.ci',
        '.composer',
        '.github',
        '.tools',
        'build',
        'devTools',
        'resources',
        'tests/autoloaded',
        'tests/benchmark/MutationGenerator/sources',
        'tests/benchmark/Tracing/coverage',
        'tests/benchmark/Tracing/sources',
        'tests/benchmark/Tracing/benchmark-source',
        'tests/e2e',
        'tests/phpunit/Fixtures',
        'var',
    ])
    ->ignoreDotFiles(false)
    ->name('*php')
    ->append([
        __DIR__ . '/bin/infection',
        __DIR__ . '/bin/infection-debug',
        __FILE__,
    ])
;

return (new Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP7x1Migration' => true,
        '@PHP7x1Migration:risky' => true,
        '@PHPUnit6x0Migration:risky' => true,
        '@PHPUnit7x5Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_indentation' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'break',
                'continue',
                'declare',
                'do',
                'exit',
                'for',
                'foreach',
                'goto',
                'if',
                'include',
                'include_once',
                'require',
                'require_once',
                'return',
                'switch',
                'throw',
                'try',
                'while',
                'yield',
                'yield_from',
            ],
        ],
        'blank_line_between_import_groups' => false,
        'compact_nullable_type_declaration' => true,
        'concat_space' => ['spacing' => 'one'],
        'class_attributes_separation' => true,
        'fully_qualified_strict_types' => [
            'import_symbols' => true,
        ],
        // TODO: enable
        'get_class_to_class_keyword' => false,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header' => $header,
            'location' => 'after_open',
            'separate' => 'bottom',
        ],
        'list_syntax' => [
            'syntax' => 'short',
        ],
        'logical_operators' => true,
        'modernize_strpos' => true,
        'native_constant_invocation' => true,
        'native_function_invocation' => [
            'include' => ['@internal'],
        ],
        'no_alternative_syntax' => true,
        'no_superfluous_phpdoc_tags' => true,
        'no_trailing_whitespace_in_string' => false,
        'no_unset_cast' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'operator_linebreak' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'ordered_interfaces' => true,
        'phpdoc_align' => [
            'align' => 'left',
        ],
        // This rule is buggy and does not only apply to phpdoc annotation...
        'phpdoc_annotation_without_dot' => false,
        // Allow inline Psalm suppress statements
        'phpdoc_to_comment' => false,
        'php_unit_dedicate_assert' => true,
        'php_unit_method_casing' => [
            'case' => 'snake_case',
        ],
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_strict' => false,
        'phpdoc_order_by_value' => [
            'annotations' => ['covers'],
        ],
        'php_unit_test_annotation' => [
            'style' => 'prefix',
        ],
        'php_unit_test_case_static_method_calls' => [
            'call_type' => 'this',
        ],
        'phpdoc_no_empty_return' => true,
        'phpdoc_order' => true,
        'phpdoc_summary' => false,
        'phpdoc_separation' => false,
        'heredoc_indentation' => true,
        'self_static_accessor' => true,
        'single_line_throw' => false,
        'static_lambda' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => ['arguments', 'arrays', 'match', 'parameters'],
        ],
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
        'blank_line_after_opening_tag' => false,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/var/cache/php-cs-fixer')
;
