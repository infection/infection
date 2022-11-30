<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\String\Slugger;

use _HumbugBox9658796bb9f0\Symfony\Component\String\AbstractUnicodeString;
use _HumbugBox9658796bb9f0\Symfony\Component\String\UnicodeString;
use _HumbugBox9658796bb9f0\Symfony\Contracts\Translation\LocaleAwareInterface;
if (!\interface_exists(LocaleAwareInterface::class)) {
    throw new \LogicException('You cannot use the "Symfony\\Component\\String\\Slugger\\AsciiSlugger" as the "symfony/translation-contracts" package is not installed. Try running "composer require symfony/translation-contracts".');
}
class AsciiSlugger implements SluggerInterface, LocaleAwareInterface
{
    private const LOCALE_TO_TRANSLITERATOR_ID = ['am' => 'Amharic-Latin', 'ar' => 'Arabic-Latin', 'az' => 'Azerbaijani-Latin', 'be' => 'Belarusian-Latin', 'bg' => 'Bulgarian-Latin', 'bn' => 'Bengali-Latin', 'de' => 'de-ASCII', 'el' => 'Greek-Latin', 'fa' => 'Persian-Latin', 'he' => 'Hebrew-Latin', 'hy' => 'Armenian-Latin', 'ka' => 'Georgian-Latin', 'kk' => 'Kazakh-Latin', 'ky' => 'Kirghiz-Latin', 'ko' => 'Korean-Latin', 'mk' => 'Macedonian-Latin', 'mn' => 'Mongolian-Latin', 'or' => 'Oriya-Latin', 'ps' => 'Pashto-Latin', 'ru' => 'Russian-Latin', 'sr' => 'Serbian-Latin', 'sr_Cyrl' => 'Serbian-Latin', 'th' => 'Thai-Latin', 'tk' => 'Turkmen-Latin', 'uk' => 'Ukrainian-Latin', 'uz' => 'Uzbek-Latin', 'zh' => 'Han-Latin'];
    private $defaultLocale;
    private $symbolsMap = ['en' => ['@' => 'at', '&' => 'and']];
    private $transliterators = [];
    public function __construct(string $defaultLocale = null, $symbolsMap = null)
    {
        if (null !== $symbolsMap && !\is_array($symbolsMap) && !$symbolsMap instanceof \Closure) {
            throw new \TypeError(\sprintf('Argument 2 passed to "%s()" must be array, Closure or null, "%s" given.', __METHOD__, \gettype($symbolsMap)));
        }
        $this->defaultLocale = $defaultLocale;
        $this->symbolsMap = $symbolsMap ?? $this->symbolsMap;
    }
    public function setLocale($locale)
    {
        $this->defaultLocale = $locale;
    }
    public function getLocale()
    {
        return $this->defaultLocale;
    }
    public function slug(string $string, string $separator = '-', string $locale = null) : AbstractUnicodeString
    {
        $locale = $locale ?? $this->defaultLocale;
        $transliterator = [];
        if ($locale && ('de' === $locale || 0 === \strpos($locale, 'de_'))) {
            $transliterator = ['de-ASCII'];
        } elseif (\function_exists('transliterator_transliterate') && $locale) {
            $transliterator = (array) $this->createTransliterator($locale);
        }
        if ($this->symbolsMap instanceof \Closure) {
            $symbolsMap = $this->symbolsMap;
            \array_unshift($transliterator, static function ($s) use($symbolsMap, $locale) {
                return $symbolsMap($s, $locale);
            });
        }
        $unicodeString = (new UnicodeString($string))->ascii($transliterator);
        if (\is_array($this->symbolsMap)) {
            $map = null;
            if (isset($this->symbolsMap[$locale])) {
                $map = $this->symbolsMap[$locale];
            } else {
                $parent = self::getParentLocale($locale);
                if ($parent && isset($this->symbolsMap[$parent])) {
                    $map = $this->symbolsMap[$parent];
                }
            }
            if ($map) {
                foreach ($map as $char => $replace) {
                    $unicodeString = $unicodeString->replace($char, ' ' . $replace . ' ');
                }
            }
        }
        return $unicodeString->replaceMatches('/[^A-Za-z0-9]++/', $separator)->trim($separator);
    }
    private function createTransliterator(string $locale) : ?\Transliterator
    {
        if (\array_key_exists($locale, $this->transliterators)) {
            return $this->transliterators[$locale];
        }
        if ($id = self::LOCALE_TO_TRANSLITERATOR_ID[$locale] ?? null) {
            return $this->transliterators[$locale] = \Transliterator::create($id . '/BGN') ?? \Transliterator::create($id);
        }
        if (!($parent = self::getParentLocale($locale))) {
            return $this->transliterators[$locale] = null;
        }
        if ($id = self::LOCALE_TO_TRANSLITERATOR_ID[$parent] ?? null) {
            $transliterator = \Transliterator::create($id . '/BGN') ?? \Transliterator::create($id);
        }
        return $this->transliterators[$locale] = $this->transliterators[$parent] = $transliterator ?? null;
    }
    private static function getParentLocale(?string $locale) : ?string
    {
        if (!$locale) {
            return null;
        }
        if (\false === ($str = \strrchr($locale, '_'))) {
            return null;
        }
        return \substr($locale, 0, -\strlen($str));
    }
}
