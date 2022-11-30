<?php

namespace _HumbugBox9658796bb9f0\Symfony\Contracts\Service;

use _HumbugBox9658796bb9f0\Psr\Container\ContainerInterface;
use _HumbugBox9658796bb9f0\Symfony\Contracts\Service\Attribute\SubscribedService;
trait ServiceSubscriberTrait
{
    protected $container;
    public static function getSubscribedServices() : array
    {
        static $services;
        if (null !== $services) {
            return $services;
        }
        $services = \method_exists(\get_parent_class(self::class) ?: '', __FUNCTION__) ? parent::getSubscribedServices() : [];
        $attributeOptIn = \false;
        if (\PHP_VERSION_ID >= 80000) {
            foreach ((new \ReflectionClass(self::class))->getMethods() as $method) {
                if (self::class !== $method->getDeclaringClass()->name) {
                    continue;
                }
                if (!($attribute = $method->getAttributes(SubscribedService::class)[0] ?? null)) {
                    continue;
                }
                if ($method->isStatic() || $method->isAbstract() || $method->isGenerator() || $method->isInternal() || $method->getNumberOfRequiredParameters()) {
                    throw new \LogicException(\sprintf('Cannot use "%s" on method "%s::%s()" (can only be used on non-static, non-abstract methods with no parameters).', SubscribedService::class, self::class, $method->name));
                }
                if (!($returnType = $method->getReturnType())) {
                    throw new \LogicException(\sprintf('Cannot use "%s" on methods without a return type in "%s::%s()".', SubscribedService::class, $method->name, self::class));
                }
                $serviceId = $returnType instanceof \ReflectionNamedType ? $returnType->getName() : (string) $returnType;
                if ($returnType->allowsNull()) {
                    $serviceId = '?' . $serviceId;
                }
                $services[$attribute->newInstance()->key ?? self::class . '::' . $method->name] = $serviceId;
                $attributeOptIn = \true;
            }
        }
        if (!$attributeOptIn) {
            foreach ((new \ReflectionClass(self::class))->getMethods() as $method) {
                if ($method->isStatic() || $method->isAbstract() || $method->isGenerator() || $method->isInternal() || $method->getNumberOfRequiredParameters()) {
                    continue;
                }
                if (self::class !== $method->getDeclaringClass()->name) {
                    continue;
                }
                if (!($returnType = $method->getReturnType()) instanceof \ReflectionNamedType) {
                    continue;
                }
                if ($returnType->isBuiltin()) {
                    continue;
                }
                if (\PHP_VERSION_ID >= 80000) {
                    trigger_deprecation('symfony/service-contracts', '2.5', 'Using "%s" in "%s" without using the "%s" attribute on any method is deprecated.', ServiceSubscriberTrait::class, self::class, SubscribedService::class);
                }
                $services[self::class . '::' . $method->name] = '?' . ($returnType instanceof \ReflectionNamedType ? $returnType->getName() : $returnType);
            }
        }
        return $services;
    }
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        if (\method_exists(\get_parent_class(self::class) ?: '', __FUNCTION__)) {
            return parent::setContainer($container);
        }
        return null;
    }
}
