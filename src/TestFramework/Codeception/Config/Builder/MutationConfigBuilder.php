<?php

declare(strict_types=1);


namespace Infection\TestFramework\Codeception\Config\Builder;

use Infection\Mutant\MutantInterface;
use Infection\TestFramework\Codeception\Config\MutationYamlConfiguration;
use Infection\TestFramework\Config\MutationConfigBuilder as ConfigBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
final class MutationConfigBuilder extends ConfigBuilder
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var array
     */
    private $originalConfigContentParsed;

    public function __construct(Filesystem $filesystem, string $tmpDir, string $projectDir, array $originalConfigContentParsed)
    {
        $this->tmpDir = $tmpDir;
        $this->projectDir = $projectDir;
        $this->originalConfigContentParsed = $originalConfigContentParsed;
        $this->filesystem = $filesystem;
    }

    public function build(MutantInterface $mutant): string
    {
        $mutationHash = $mutant->getMutation()->getHash();

        $interceptorFilePath = sprintf(
            '%s/interceptor.codeception.%s.php',
            $this->tmpDir,
            $mutationHash
        );

        file_put_contents($interceptorFilePath, $this->createCustomBootstrapWithInterceptor($mutant));

        $yamlConfiguration = new MutationYamlConfiguration(
            $this->tmpDir,
            $this->projectDir,
            $this->originalConfigContentParsed,
            $mutationHash,
            $interceptorFilePath
        );

        $newYaml = $yamlConfiguration->getYaml();

        $path = $this->buildPath($mutant);

        file_put_contents($path, $newYaml);

        return $path;
    }

    private function createCustomBootstrapWithInterceptor(MutantInterface $mutant): string
    {
        $originalFilePath = $mutant->getMutation()->getOriginalFilePath();
        $mutatedFilePath = $mutant->getMutatedFilePath();

        $originalBootstrap = $this->getOriginalBootstrapFilePath();
        $bootstrapPlaceholder = $originalBootstrap ? "require_once '{$originalBootstrap}';" : '';

        $interceptorPath = \dirname(__DIR__, 4) . '/StreamWrapper/IncludeInterceptor.php';

        $customBootstrap = <<<AUTOLOAD
<?php

%s
%s

AUTOLOAD;

        return sprintf(
            $customBootstrap,
            $bootstrapPlaceholder,
            $this->getInterceptorFileContent($interceptorPath, $originalFilePath, $mutatedFilePath)
        );
    }

    private function buildPath(MutantInterface $mutant): string
    {
        $fileName = sprintf('codeceptionConfiguration.%s.infection.yaml', $mutant->getMutation()->getHash());

        return $this->tmpDir . '/' . $fileName;
    }

    private function getOriginalBootstrapFilePath(): ?string
    {
        if (!\array_key_exists('bootstrap', $this->originalConfigContentParsed)) {
            return null;
        }

        if ($this->filesystem->isAbsolutePath($this->originalConfigContentParsed['bootstrap'])) {
            return $this->originalConfigContentParsed['bootstrap'];
        }

        return sprintf(
            '%s/%s/%s',
            $this->projectDir,
            $this->originalConfigContentParsed['paths']['tests'] ?? 'tests',
            $this->originalConfigContentParsed['bootstrap']
        );
    }
}
