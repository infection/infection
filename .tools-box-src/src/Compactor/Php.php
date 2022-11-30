<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Compactor;

use function array_pop;
use function array_slice;
use function array_splice;
use function count;
use function is_int;
use _HumbugBoxb47773b41c19\KevinGH\Box\Annotation\DocblockAnnotationParser;
use function ltrim;
use PhpToken;
use function preg_replace;
use RuntimeException;
use function str_repeat;
use function substr;
use function substr_count;
use const T_COMMENT;
use const T_DOC_COMMENT;
use const T_WHITESPACE;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class Php extends FileExtensionCompactor
{
    public function __construct(private DocblockAnnotationParser $annotationParser, array $extensions = ['php'])
    {
        parent::__construct($extensions);
    }
    protected function compactContent(string $contents) : string
    {
        $output = '';
        $tokens = PhpToken::tokenize($contents);
        $tokenCount = count($tokens);
        for ($index = 0; $index < $tokenCount; ++$index) {
            $token = $tokens[$index];
            $tokenText = $token->text;
            if ($token->is([T_COMMENT, T_DOC_COMMENT])) {
                if (\str_starts_with($tokenText, '#[')) {
                    $retokenized = $this->retokenizeAttribute($tokens, $index);
                    if (null !== $retokenized) {
                        array_splice($tokens, $index, 1, $retokenized);
                        $tokenCount = count($tokens);
                    }
                    $attributeCloser = self::findAttributeCloser($tokens, $index);
                    if (is_int($attributeCloser)) {
                        $output .= '#[';
                    } else {
                        $output .= str_repeat("\n", substr_count($tokenText, "\n"));
                    }
                } elseif (\str_contains($tokenText, '@')) {
                    try {
                        $output .= $this->compactAnnotations($tokenText);
                    } catch (RuntimeException) {
                        $output .= $tokenText;
                    }
                } else {
                    $output .= str_repeat("\n", substr_count($tokenText, "\n"));
                }
            } elseif ($token->is(T_WHITESPACE)) {
                $whitespace = $tokenText;
                $previousIndex = $index - 1;
                $nextToken = $tokens[$index + 1] ?? null;
                if (null !== $nextToken && $nextToken->is(T_WHITESPACE)) {
                    $whitespace .= $nextToken->text;
                    ++$index;
                }
                $whitespace = preg_replace('{[ \\t]+}', ' ', $whitespace);
                $whitespace = preg_replace('{(?:\\r\\n|\\r|\\n)}', "\n", $whitespace);
                $previousToken = $tokens[$previousIndex];
                if ($previousToken->is(T_COMMENT) && \str_contains($previousToken->text, "\n")) {
                    $whitespace = ltrim($whitespace, ' ');
                }
                $whitespace = preg_replace('{\\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $tokenText;
            }
        }
        return $output;
    }
    private function compactAnnotations(string $docblock) : string
    {
        $breaksNbr = substr_count($docblock, "\n");
        $annotations = $this->annotationParser->parse($docblock);
        if ([] === $annotations) {
            return str_repeat("\n", $breaksNbr);
        }
        $compactedDocblock = '/**';
        foreach ($annotations as $annotation) {
            $compactedDocblock .= "\n" . $annotation;
        }
        $breaksNbr -= count($annotations);
        if ($breaksNbr > 0) {
            $compactedDocblock .= str_repeat("\n", $breaksNbr - 1);
            $compactedDocblock .= "\n*/";
        } else {
            $compactedDocblock .= ' */';
        }
        return $compactedDocblock;
    }
    private static function findAttributeCloser(array $tokens, int $opener) : ?int
    {
        $tokenCount = count($tokens);
        $brackets = [$opener];
        $closer = null;
        for ($i = $opener + 1; $i < $tokenCount; ++$i) {
            $tokenText = $tokens[$i]->text;
            if ('[' === $tokenText) {
                $brackets[] = $i;
                continue;
            }
            if (']' === $tokenText) {
                array_pop($brackets);
                if (0 === count($brackets)) {
                    $closer = $i;
                    break;
                }
            }
        }
        return $closer;
    }
    private function retokenizeAttribute(array &$tokens, int $opener) : ?array
    {
        Assert::keyExists($tokens, $opener);
        $token = $tokens[$opener];
        $attributeBody = substr($token->text, 2);
        $subTokens = PhpToken::tokenize('<?php ' . $attributeBody);
        array_splice($subTokens, 0, 1, ['#[']);
        $closer = self::findAttributeCloser($subTokens, 0);
        if (null === $closer) {
            foreach (array_slice($tokens, $opener + 1) as $token) {
                $attributeBody .= $token->text;
            }
            $subTokens = PhpToken::tokenize('<?php ' . $attributeBody);
            array_splice($subTokens, 0, 1, ['#[']);
            $closer = self::findAttributeCloser($subTokens, 0);
            if (null !== $closer) {
                array_splice($tokens, $opener + 1, count($tokens), array_slice($subTokens, $closer + 1));
                $subTokens = array_slice($subTokens, 0, $closer + 1);
            }
        }
        if (null === $closer) {
            return null;
        }
        return $subTokens;
    }
}
\class_alias('_HumbugBoxb47773b41c19\\KevinGH\\Box\\Compactor\\Php', 'KevinGH\\Box\\Compactor\\Php', \false);
