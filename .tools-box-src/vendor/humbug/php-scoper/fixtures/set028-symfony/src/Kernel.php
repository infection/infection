<?php

namespace _HumbugBoxb47773b41c19\App;

use _HumbugBoxb47773b41c19\Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use _HumbugBoxb47773b41c19\Symfony\Component\Config\Loader\LoaderInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Config\Resource\FileResource;
use _HumbugBoxb47773b41c19\Symfony\Component\DependencyInjection\ContainerBuilder;
use _HumbugBoxb47773b41c19\Symfony\Component\HttpKernel\Kernel as BaseKernel;
use _HumbugBoxb47773b41c19\Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
class Kernel extends BaseKernel
{
    use MicroKernelTrait;
    const CONFIG_EXTS = '.{php,xml,yaml,yml}';
    public function getCacheDir() : string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }
    public function getLogDir() : string
    {
        return $this->getProjectDir() . '/var/log';
    }
    public function registerBundles() : iterable
    {
        $contents = (require $this->getProjectDir() . '/config/bundles.php');
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                (yield new $class());
            }
        }
    }
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader) : void
    {
        $container->addResource(new FileResource($this->getProjectDir() . '/config/bundles.php'));
        $container->setParameter('container.autowiring.strict_mode', \true);
        $container->setParameter('container.dumper.inline_class_loader', \true);
        $confDir = $this->getProjectDir() . '/config';
        $loader->load($confDir . '/{packages}/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{packages}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}_' . $this->environment . self::CONFIG_EXTS, 'glob');
    }
    protected function configureRoutes(RoutingConfigurator $routes) : void
    {
        $confDir = $this->getProjectDir() . '/config';
        $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}' . self::CONFIG_EXTS, '/', 'glob');
    }
}
