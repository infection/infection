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

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector;
use Rector\CodeQuality\Rector\ClassConstFetch\VariableConstFetchToClassConstFetchRector;
use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector;
use Rector\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector;
use Rector\Instanceof_\Rector\Ternary\FlipNegatedTernaryInstanceofRector;
use Rector\Php73\Rector\String_\SensitiveHereNowDocRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\PHPUnit\CodeQuality\Rector\ClassMethod\AddInstanceofAssertForNullableInstanceRector;
use Rector\PHPUnit\CodeQuality\Rector\ClassMethod\DataProviderArrayItemsNewLinedRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertCompareOnCountableWithMethodToAssertCountRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEmptyNullableObjectToAssertInstanceofRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\MergeWithCallableAndWillReturnRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector;
use Rector\TypeDeclaration\Rector\Closure\ClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\ClassMethodArrayDocblockParamFromLocalCallsRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\DocblockVarArrayFromGetterReturnRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\DocblockVarArrayFromPropertyDefaultsRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\DocblockVarFromParamDocblockInConstructorRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockBasedOnArrayMapRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromAssignsParamToParamReferenceRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromDataProviderRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromDimFetchAccessRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForArrayDimAssignedObjectRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForCommonObjectDenominatorRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForJsonArrayRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\DocblockGetterReturnArrayFromPropertyDocblockVarRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests/phpunit',
    ])
    ->withSkipPath(
        __DIR__ . '/tests/phpunit/TestingUtility/PhpParser/NodeDumper/NodeDumper.php',
    )
    ->withCache(
        cacheClass: FileCacheStorage::class,
        cacheDirectory: __DIR__ . '/var/cache/rector',
    )
    ->withPhpSets(php82: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        phpunitCodeQuality: true,
        typeDeclarations: true,
    )
    ->withRules([
        AddParamArrayDocblockFromAssignsParamToParamReferenceRector::class,
        AddParamArrayDocblockFromDataProviderRector::class,
        AddReturnDocblockForArrayDimAssignedObjectRector::class,
        AddReturnDocblockForCommonObjectDenominatorRector::class,
        AddReturnDocblockForJsonArrayRector::class,
        AddParamArrayDocblockBasedOnArrayMapRector::class,
        AddParamArrayDocblockFromDimFetchAccessRector::class,
        ClassMethodArrayDocblockParamFromLocalCallsRector::class,
        DisallowedEmptyRuleFixerRector::class,
        DocblockGetterReturnArrayFromPropertyDocblockVarRector::class,
        DocblockVarArrayFromGetterReturnRector::class,
        DocblockVarArrayFromPropertyDefaultsRector::class,
        DocblockVarFromParamDocblockInConstructorRector::class,
        FlipNegatedTernaryInstanceofRector::class,
        PrivatizeFinalClassMethodRector::class,
        PrivatizeFinalClassPropertyRector::class,
        TypedPropertyFromAssignsRector::class,
    ])
    ->withConfiguredRule(
        ClassPropertyAssignToConstructorPromotionRector::class,
        [
            'inline_public' => false,
            'rename_property' => true,
            'allow_model_based_classes' => true,
        ],
    )
    ->withSkip([
        AbsolutizeRequireAndIncludePathRector::class,
        AddArrowFunctionReturnTypeRector::class,
        AddInstanceofAssertForNullableInstanceRector::class,
        AddParamArrayDocblockFromDataProviderRector::class => [
            __DIR__ . '/tests/phpunit/Framework/Iterable/GeneratorFactory/GeneratorFactoryTest.php',
        ],
        AddReturnTypeDeclarationBasedOnParentClassMethodRector::class => [
            __DIR__ . '/tests/phpunit/Fixtures/Console/FakeOutputSymfony5.php',
        ],
        AssertCompareOnCountableWithMethodToAssertCountRector::class,
        AssertEmptyNullableObjectToAssertInstanceofRector::class,
        AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector::class,
        ClosureReturnTypeRector::class,
        DataProviderArrayItemsNewLinedRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        LocallyCalledStaticMethodToNonStaticRector::class,
        MergeWithCallableAndWillReturnRector::class => [
            __DIR__ . '/tests/phpunit/Mutation/FileMutationGeneratorTest.php',
        ],
        ReadOnlyPropertyRector::class => [
            // property can't be readonly as it's returned by reference and may be updated
            __DIR__ . '/src/TestFramework/Tracing/Trace/TestLocations.php',
        ],
        RemoveAlwaysTrueIfConditionRector::class => [
            __DIR__ . '/tests/phpunit/Fixtures/',
        ],
        RemoveDuplicatedCaseInSwitchRector::class => [
            __DIR__ . '/tests/phpunit/Fixtures/',
        ],
        RemovePhpVersionIdCheckRector::class => true,
        RemoveUnreachableStatementRector::class => [
            __DIR__ . '/tests/phpunit/TestFramework/Coverage/JUnit/JUnitTestExecutionInfoAdderTest.php',
        ],
        RemoveUnusedConstructorParamRector::class => [
            __DIR__ . '/tests/phpunit/Fixtures/',
        ],
        RemoveUnusedPrivateMethodParameterRector::class => [
            __DIR__ . '/tests/phpunit/Fixtures/',
        ],
        RemoveUnusedPublicMethodParameterRector::class => [
            __DIR__ . '/src/StaticAnalysis/StaticAnalysisToolTypes.php',
            __DIR__ . '/tests/phpunit/Fixtures/',
        ],
        SensitiveHereNowDocRector::class,
        SimplifyDeMorganBinaryRector::class,
        SimplifyIfElseToTernaryRector::class,
        SimplifyIfReturnBoolRector::class => [
            __DIR__ . '/src/Process/OriginalPhpProcess.php',
        ],
        SimplifyUselessVariableRector::class => [
            __DIR__ . '/src/StaticAnalysis/StaticAnalysisToolTypes.php',
        ],
        VariableConstFetchToClassConstFetchRector::class,
    ]);
