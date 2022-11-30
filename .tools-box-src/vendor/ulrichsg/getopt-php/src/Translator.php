<?php

namespace _HumbugBoxb47773b41c19\GetOpt;

class Translator
{
    const PATH_TEMPLATE = '%s/../resources/localization/%s.php';
    protected $languageFile;
    protected $translations;
    protected static $fallbackTranslator;
    public function __construct($language = 'en')
    {
        if (!$this->setLanguage($language)) {
            throw new \InvalidArgumentException(\sprintf('$language %s not available', $language));
        }
        if (!self::$fallbackTranslator && (\func_num_args() < 2 || \func_get_arg(1) !== \true)) {
            self::$fallbackTranslator = new self('en', \true);
        }
    }
    public function translate($key)
    {
        if ($this->translations === null) {
            $this->loadTranslations();
        }
        if (!isset($this->translations[$key])) {
            return $this !== self::$fallbackTranslator ? self::$fallbackTranslator->translate($key) : $key;
        }
        return $this->translations[$key];
    }
    public function setLanguage($language)
    {
        $languageFile = \file_exists($language) ? $language : \sprintf(static::PATH_TEMPLATE, __DIR__, $language);
        if (!\file_exists($languageFile)) {
            return \false;
        }
        if ($this->languageFile != $languageFile) {
            $this->translations = null;
        }
        $this->languageFile = $languageFile;
        return \true;
    }
    protected function loadTranslations()
    {
        $this->translations = (include $this->languageFile);
    }
}
