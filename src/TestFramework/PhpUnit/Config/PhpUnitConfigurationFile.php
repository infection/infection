<?php
/**
 * Created by PhpStorm.
 * User: borN_free
 * Date: 14/04/2017
 * Time: 21:01
 */
declare(strict_types=1);


namespace Infection\TestFramework\PhpUnit\Config;


class PhpUnitConfigurationFile
{
    private $path;

    public function __construct(string $path)
    {

        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}