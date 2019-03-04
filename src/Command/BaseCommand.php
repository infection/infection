<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
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

namespace Infection\Command;

use Infection\Console\Application;
use Infection\Events\LoadPluginsFinished;
use Infection\Plugin\PluginInterface;
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
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Container
     */
    private $container;

    /** @var array */
    private $loadedPlugins = [];

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

        /** @var array $plugins */
        $plugins = $input->getOption('plugins');

        if ([''] === $plugins && $this->getContainer()->has('infection.config')) {
            $plugins = $this->getContainer()->get('infection.config')->getPlugins();
        }

        $this->loadPlugins($plugins);
        $this->getContainer()->get('test.framework.types');
    }

    private function loadPlugins(array $plugins): void
    {
        foreach ($plugins as $className) {
            if (!class_exists($className)) {
                throw new \RuntimeException('Plugin Not Found: ' . $className);
            } elseif (!is_subclass_of($className, PluginInterface::class)) {
                throw new \LogicException('Invalid Plugin: ' . $className);
            }

            /** @var \Infection\Plugin\PluginInterface $plugin */
            $plugin = new $className($this->getContainer());
            $plugin->initialize();

            $this->loadedPlugins[] = $plugin;
        }

        $this->getContainer()->get('dispatcher')->dispatch(new LoadPluginsFinished());
    }
}
