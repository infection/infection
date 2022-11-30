<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Description;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Context as TypeContext;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
use function preg_match;
final class Deprecated extends BaseTag implements Factory\StaticMethod
{
    protected $name = 'deprecated';
    public const REGEX_VECTOR = '(?:
        # Normal release vectors.
        \\d\\S*
        |
        # VCS version vectors. Per PHPCS, they are expected to
        # follow the form of the VCS name, followed by ":", followed
        # by the version vector itself.
        # By convention, popular VCSes like CVS, SVN and GIT use "$"
        # around the actual version vector.
        [^\\s\\:]+\\:\\s*\\$[^\\$]+\\$
    )';
    private $version;
    public function __construct(?string $version = null, ?Description $description = null)
    {
        Assert::nullOrNotEmpty($version);
        $this->version = $version;
        $this->description = $description;
    }
    public static function create(?string $body, ?DescriptionFactory $descriptionFactory = null, ?TypeContext $context = null) : self
    {
        if (empty($body)) {
            return new static();
        }
        $matches = [];
        if (!preg_match('/^(' . self::REGEX_VECTOR . ')\\s*(.+)?$/sux', $body, $matches)) {
            return new static(null, $descriptionFactory !== null ? $descriptionFactory->create($body, $context) : null);
        }
        Assert::notNull($descriptionFactory);
        return new static($matches[1], $descriptionFactory->create($matches[2] ?? '', $context));
    }
    public function getVersion() : ?string
    {
        return $this->version;
    }
    public function __toString() : string
    {
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }
        $version = (string) $this->version;
        return $version . ($description !== '' ? ($version !== '' ? ' ' : '') . $description : '');
    }
}
