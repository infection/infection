<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config\Path;

use Infection\Finder\Locator;

class PathReplacer
{
    /**
     * @var Locator
     */
    private $locator;

    /**
     * @var string|null
     */
    private $customPhpUnitConfigDir;

    public function __construct(Locator $fileLocator, string $customPhpUnitConfigDir = null)
    {
        $this->locator = $fileLocator;
        $this->customPhpUnitConfigDir = $customPhpUnitConfigDir;
    }

    /**
     * @param \DOMNode & \DOMElement $domElement
     */
    public function replaceInNode(\DOMNode $domElement)
    {
        if (strpos($domElement->nodeValue, '*') === false) {
            $domElement->nodeValue = $this->locator->locate($domElement->nodeValue, $this->customPhpUnitConfigDir);
        } else {
            $directories = $this->locator->locateDirectories($domElement->nodeValue, $this->customPhpUnitConfigDir);

            $domDocument = $domElement->ownerDocument;

            $parentNode = $domElement->parentNode;
            $domElement->parentNode->removeChild($domElement);

            foreach ($directories as $directory) {
                $parentNode->appendChild($domDocument->createElement($domElement->tagName, $directory));
            }
        }
    }
}
