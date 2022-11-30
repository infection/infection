<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\XmlReport;

use DOMDocument;
use _HumbugBox9658796bb9f0\Infection\CannotBeInstantiated;
use _HumbugBox9658796bb9f0\Infection\TestFramework\SafeDOMXPath;
use function _HumbugBox9658796bb9f0\Safe\preg_replace;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class XPathFactory
{
    use CannotBeInstantiated;
    public static function createXPath(string $coverageContent) : SafeDOMXPath
    {
        $document = new DOMDocument();
        $success = @$document->loadXML(self::removeNamespace($coverageContent));
        Assert::true($success);
        return new SafeDOMXPath($document);
    }
    private static function removeNamespace(string $xml) : string
    {
        $cleanedXml = preg_replace('/xmlns=\\".*?\\"/', '', $xml);
        Assert::string($cleanedXml);
        return $cleanedXml;
    }
}
