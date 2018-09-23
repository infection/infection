<?php
/**
 * Created by PhpStorm.
 * User: maksrafalko
 * Date: 9/24/18
 * Time: 12:07 AM
 */

namespace Infection\TestFramework;

use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
interface HasExtraNodeVisitors
{
    /**
     * @return NodeVisitorAbstract[]
     */
    public function getMutationsCollectionNodeVisitors(): array;
}