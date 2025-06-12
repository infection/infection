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

namespace Infection\TestFramework\PhpUnit\Config;

use Infection\TestFramework\SafeDOMXPath;
use function Safe\preg_match;

/**
 * @internal
 */
final class XmlConfigurationVersionProvider
{
    private const LAST_LEGACY_VERSION = '9.2';
    private const NEXT_MAINSTREAM_VERSION = '9.3';

    public function provide(SafeDOMXPath $xPath): string
    {
        // <coverage>
        if ($xPath->query('/phpunit/coverage')->length > 0) {
            return self::NEXT_MAINSTREAM_VERSION;
        }

        // <logging><log type="*">
        if ($xPath->query('/phpunit/logging/log')->length > 0) {
            return self::LAST_LEGACY_VERSION;
        }

        // <logging><*> where <*> isn't <log>
        if ($xPath->query('/phpunit/logging/*[name(.) != "log"]')->length > 0) {
            return self::NEXT_MAINSTREAM_VERSION;
        }

        // <filter><whitelist>
        if ($xPath->query('/phpunit/filter')->length > 0) {
            return self::LAST_LEGACY_VERSION;
        }

        foreach ([
            'disableCodeCoverageIgnore', // <phpunit disableCodeCoverageIgnore="true">
            'ignoreDeprecatedCodeUnitsFromCodeCoverage', // <phpunit ignoreDeprecatedCodeUnitsFromCodeCoverage="true">
        ] as $legacyAttribute) {
            if ($xPath->query("/phpunit[@{$legacyAttribute}]")->length > 0) {
                return self::LAST_LEGACY_VERSION;
            }
        }

        $schemaUri = $this->getSchemaURI($xPath);

        if ($schemaUri === null) {
            // Best guess it is a legacy version: config upgrader will add a path to XSD
            return self::LAST_LEGACY_VERSION;
        }

        /*
         * We're looking for these:
         *
         * vendor/phpunit/phpunit/schema/9.2.xsd
         * http://schema.phpunit.de/6.0/phpunit.xsd
         * https://schema.phpunit.de/9.3/phpunit.xsd
         */
        $match = [];

        if (preg_match('#(\d+\.\d)(/phpunit)?\.xsd$#', $schemaUri, $match) === 1) {
            return $match[1];
        }

        // Without any clues we assume it's a legacy version: it's most prevalent
        return self::LAST_LEGACY_VERSION;
    }

    private function getSchemaURI(SafeDOMXPath $xPath): ?string
    {
        if ($xPath->query('namespace::xsi')->length === 0) {
            return null;
        }

        $schema = $xPath->query('/phpunit/@xsi:noNamespaceSchemaLocation');

        if ($schema->length === 0) {
            return null;
        }

        return $schema[0]->nodeValue;
    }
}
