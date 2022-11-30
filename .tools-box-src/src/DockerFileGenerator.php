<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box;

use function array_column;
use function array_filter;
use function basename;
use _HumbugBoxb47773b41c19\Composer\Semver\Semver;
use function implode;
use function _HumbugBoxb47773b41c19\Safe\sprintf;
use function str_replace;
use UnexpectedValueException;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class DockerFileGenerator
{
    private const FILE_TEMPLATE = <<<'Dockerfile'
FROM php:__BASE_PHP_IMAGE_TOKEN__

RUN $(php -r '$extensionInstalled = array_map("strtolower", \get_loaded_extensions(false));$requiredExtensions = __PHP_EXTENSIONS_TOKEN__;$extensionsToInstall = array_diff($requiredExtensions, $extensionInstalled);if ([] !== $extensionsToInstall) {echo \sprintf("docker-php-ext-install %s", implode(" ", $extensionsToInstall));}echo "echo \"No extensions\"";')

COPY __PHAR_FILE_PATH_TOKEN__ /__PHAR_FILE_NAME_TOKEN__

ENTRYPOINT ["/__PHAR_FILE_NAME_TOKEN__"]

Dockerfile;
    private const PHP_DOCKER_IMAGES = ['8.1.0' => '8.1-cli-alpine', '8.0.0' => '8.0-cli-alpine', '7.4.0' => '7.4-cli-alpine', '7.3.0' => '7.3-cli-alpine', '7.2.0' => '7.2-cli-alpine', '7.1.0' => '7.1-cli-alpine', '7.0.0' => '7-cli-alpine'];
    private string $image;
    private array $extensions;
    public static function createForRequirements(array $requirements, string $sourcePhar) : self
    {
        return new self(self::retrievePhpImageName($requirements), self::retrievePhpExtensions($requirements), $sourcePhar);
    }
    public function __construct(string $image, array $extensions, private readonly string $sourcePhar)
    {
        Assert::inArray($image, self::PHP_DOCKER_IMAGES);
        Assert::allString($extensions);
        $this->image = $image;
        $this->extensions = $extensions;
    }
    public function generateStub() : string
    {
        $contents = self::FILE_TEMPLATE;
        $contents = str_replace('__BASE_PHP_IMAGE_TOKEN__', $this->image, $contents);
        $contents = str_replace('__PHP_EXTENSIONS_TOKEN__', [] === $this->extensions ? '[]' : sprintf('["%s"]', implode('", "', $this->extensions)), $contents);
        $contents = str_replace('__PHAR_FILE_PATH_TOKEN__', $this->sourcePhar, $contents);
        $contents = str_replace('__PHAR_FILE_NAME_TOKEN__', basename($this->sourcePhar), $contents);
        return $contents;
    }
    private static function retrievePhpImageName(array $requirements) : string
    {
        $conditions = array_column(array_filter($requirements, static fn(array $requirement): bool => 'php' === $requirement['type']), 'condition');
        foreach (self::PHP_DOCKER_IMAGES as $php => $image) {
            foreach ($conditions as $condition) {
                if (\false === Semver::satisfies($php, $condition)) {
                    continue 2;
                }
            }
            return $image;
        }
        throw new UnexpectedValueException(sprintf('Could not find a suitable Docker base image for the PHP constraint(s) "%s". Images available: "%s"', implode('", "', $conditions), implode('", "', self::PHP_DOCKER_IMAGES)));
    }
    private static function retrievePhpExtensions(array $requirements) : array
    {
        return array_column(array_filter($requirements, static fn(array $requirement): bool => 'extension' === $requirement['type']), 'condition');
    }
}
