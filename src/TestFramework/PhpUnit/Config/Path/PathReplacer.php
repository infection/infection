<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config\Path;

use Infection\Finder\Locator;

class PathReplacer
{
    /**
     * @var Locator
     */
    private $locator;

    public function __construct(Locator $fileLocator)
    {
        $this->locator = $fileLocator;
    }

    public function replaceInNode(\DOMNode $domElement)
    {
        if (strpos($domElement->nodeValue, '*') === false) {
            $domElement->nodeValue = $this->locator->locate($domElement->nodeValue);
        } else {
            $directories = $this->locator->locateDirectories($domElement->nodeValue);

            $domDocument = $domElement->ownerDocument;

            $parentNode = $domElement->parentNode;
            $domElement->parentNode->removeChild($domElement);

            foreach ($directories as $directory) {
                $parentNode->appendChild($domDocument->createElement($domElement->tagName, $directory));
            }
        }
    }
}