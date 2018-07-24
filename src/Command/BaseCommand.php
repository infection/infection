<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Command;

use Infection\Console\Application;
use Pimple\Psr11\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 *
 * @method Application getApplication()
 */
abstract class BaseCommand extends Command
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function getContainer(): Container
    {
        if ($this->container === null) {
            $this->container = new Container($this->getApplication()->getContainer());
        }

        return $this->container;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->input = $input;
        $this->output = $output;
    }
}
