<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

use function end;
use function explode;
use function is_a;
use function _HumbugBox9658796bb9f0\Safe\array_flip;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class MutatorFactory
{
    /**
     * @param array<class-string<Mutator<\PhpParser\Node>&ConfigurableMutator<\PhpParser\Node>>, mixed[]> $resolvedMutators
     *
     * @return array<string, Mutator<\PhpParser\Node>>
     */
    public function create(array $resolvedMutators, bool $useNoopMutators) : array
    {
        $mutators = [];
        $knownMutatorClassNames = array_flip(ProfileList::ALL_MUTATORS);
        foreach ($resolvedMutators as $mutatorClassName => $config) {
            Assert::keyExists($knownMutatorClassNames, $mutatorClassName, sprintf('Unknown mutator "%s"', $mutatorClassName));
            Assert::isArray($config, sprintf('Expected config of the mutator "%s" to be an array. Got "%%s" instead', $mutatorClassName));
            $settings = (array) ($config['settings'] ?? []);
            $ignored = $config['ignore'] ?? [];
            $mutator = is_a($mutatorClassName, ConfigurableMutator::class, \true) ? self::getConfigurableMutator($mutatorClassName, $settings) : new $mutatorClassName();
            if ($ignored !== []) {
                $mutator = new IgnoreMutator(new IgnoreConfig($ignored), $mutator);
            }
            if ($useNoopMutators) {
                $mutator = new NoopMutator($mutator);
            }
            $mutators[$mutator->getName()] = $mutator;
        }
        return $mutators;
    }
    public static function getMutatorNameForClassName(string $className) : string
    {
        $parts = explode('\\', $className);
        return end($parts);
    }
    /**
     * @param class-string<ConfigurableMutator<\PhpParser\Node>> $mutatorClassName
     * @param mixed[] $settings
     *
     * @return ConfigurableMutator<\PhpParser\Node>
     */
    private static function getConfigurableMutator(string $mutatorClassName, array $settings) : ConfigurableMutator
    {
        $configClassName = $mutatorClassName::getConfigClassName();
        return new $mutatorClassName(new $configClassName($settings));
    }
}
