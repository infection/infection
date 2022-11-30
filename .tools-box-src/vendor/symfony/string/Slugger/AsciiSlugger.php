<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\String\Slugger;

use _HumbugBoxb47773b41c19\Symfony\Component\String\AbstractUnicodeString;
use _HumbugBoxb47773b41c19\Symfony\Component\String\UnicodeString;
use _HumbugBoxb47773b41c19\Symfony\Contracts\Translation\LocaleAwareInterface;
if (!\interface_exists(LocaleAwareInterface::class)) {
    throw new \LogicException('You cannot use the "Symfony\\Component\\String\\Slugger\\AsciiSlugger" as the "symfony/translation-contracts" package is not installed. Try running "composer require symfony/translation-contracts".');
}
class AsciiSlugger implements SluggerInterface, LocaleAwareInterface
{
    private const LOCALE_TO_TRANSLITERATOR_ID = ['am' => 'Amharic-Latin', 'ar' => 'Arabic-Latin', 'az' => 'Azerbaijani-Latin', 'be' => 'Belarusian-Latin', 'bg' => 'Bulgarian-Latin', 'bn' => 'Bengali-Latin', 'de' => 'de-ASCII', 'el' => 'Greek-Latin', 'fa' => 'Persian-Latin', 'he' => 'Hebrew-Latin', 'hy' => 'Armenian-Latin', 'ka' => 'Georgian-Latin', 'kk' => 'Kazakh-Latin', 'ky' => 'Kirghiz-Latin', 'ko' => 'Korean-Latin', 'mk' => 'Macedonian-Latin', 'mn' => 'Mongolian-Latin', 'or' => 'Oriya-Latin', 'ps' => 'Pashto-Latin', 'ru' => 'Russian-Latin', 'sr' => 'Serbian-Latin', 'sr_Cyrl' => 'Serbian-Latin', 'th' => 'Thai-Latin', 'tk' => 'Turkmen-Latin', 'uk' => 'Ukrainian-Latin', 'uz' => 'Uzbek-Latin', 'zh' => 'Han-Latin'];
    private ?string $defaultLocale;
    private \Closure|array $symbolsMap = ['en' => ['@' => 'at', '&' => 'and']];
    private array $transliterators = [];
    public function __construct(string $defaultLocale = null, array|\Closure $symbolsMap = null)
    {
        $this->defaultLocale = $defaultLocale;
        $this->symbolsMap = $symbolsMap ?? $this->symbolsMap;
    }
    public function setLocale(string $locale)
    {
        $this->defaultLocale = $locale;
    }
    public function getLocale() : string
    {
        return $this->defaultLocale;
    }
    public function slug(string $string, string $separator = '-', string $locale = null) : AbstractUnicodeString
    {
        $locale ??= $this->defaultLocale;
        $transliterator = [];
        if ($locale && ('de' === $locale || \str_starts_with($locale, 'de_'))) {
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
