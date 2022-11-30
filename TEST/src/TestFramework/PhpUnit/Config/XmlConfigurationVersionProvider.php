<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Config;

use _HumbugBox9658796bb9f0\Infection\TestFramework\SafeDOMXPath;
use function _HumbugBox9658796bb9f0\Safe\preg_match;
final class XmlConfigurationVersionProvider
{
    private const LAST_LEGACY_VERSION = '9.2';
    private const NEXT_MAINSTREAM_VERSION = '9.3';
    public function provide(SafeDOMXPath $xPath) : string
    {
        if ($xPath->query('/phpunit/coverage')->length > 0) {
            return self::NEXT_MAINSTREAM_VERSION;
        }
        if ($xPath->query('/phpunit/logging/log')->length > 0) {
            return self::LAST_LEGACY_VERSION;
        }
        if ($xPath->query('/phpunit/logging/*[name(.) != "log"]')->length > 0) {
            return self::NEXT_MAINSTREAM_VERSION;
        }
        if ($xPath->query('/phpunit/filter')->length > 0) {
            return self::LAST_LEGACY_VERSION;
        }
        foreach (['disableCodeCoverageIgnore', 'ignoreDeprecatedCodeUnitsFromCodeCoverage'] as $legacyAttribute) {
            if ($xPath->query("/phpunit[@{$legacyAttribute}]")->length > 0) {
                return self::LAST_LEGACY_VERSION;
            }
        }
        $schemaUri = $this->getSchemaURI($xPath);
        if ($schemaUri === null) {
            return self::LAST_LEGACY_VERSION;
        }
        $match = [];
        if (preg_match('#(\\d+\\.\\d)(/phpunit)?\\.xsd$#', $schemaUri, $match) === 1) {
            return $match[1];
        }
        return self::LAST_LEGACY_VERSION;
    }
    private function getSchemaURI(SafeDOMXPath $xPath) : ?string
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
