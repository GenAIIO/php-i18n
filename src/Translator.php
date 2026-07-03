<?php

namespace GenAI\I18n;

/**
 * Reflection-free message translator (Spring's MessageSource, baked). It holds
 * the compiled catalogs — array(locale => array(key => message)) — looks a key up
 * in the current locale, falls back to the fallback locale, then to the key
 * itself, and interpolates :name / {name} placeholders.
 *
 * The current locale is mutable (LocaleInterceptor sets it per request). A static
 * "shared" instance backs the global __() helper so templates stay clean.
 *
 * Runtime class (PHP 5.3-safe).
 */
class Translator
{
    private static $shared;

    private $messages;
    private $locale;
    private $fallback;

    public function __construct($messages, $locale = 'en', $fallback = 'en')
    {
        $this->messages = is_array($messages) ? $messages : array();
        $this->locale   = $locale;
        $this->fallback = $fallback;
    }

    /** Register the instance the global __() helper should use. */
    public static function useShared(Translator $translator)
    {
        self::$shared = $translator;
    }

    /** The shared instance, or null if i18n hasn't been wired. */
    public static function shared()
    {
        return self::$shared;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Translate $key, optionally in $locale (else the current one). $params are
     * interpolated as :name and {name}. Unknown keys fall back, then return the key.
     */
    public function trans($key, $params = array(), $locale = null)
    {
        $loc = ($locale !== null) ? $locale : $this->locale;

        $msg = $this->lookup($loc, $key);
        if ($msg === null) {
            $msg = $this->lookup($this->fallback, $key);
        }
        if ($msg === null) {
            $msg = $key;
        }

        if (!empty($params)) {
            foreach ($params as $name => $value) {
                $msg = str_replace(array(':' . $name, '{' . $name . '}'), array($value, $value), $msg);
            }
        }

        return $msg;
    }

    /**
     * Pluralized translate: the message holds "singular|plural" forms. Picks by
     * $count (1 => first form, else second), and interpolates :count.
     */
    public function choice($key, $count, $params = array(), $locale = null)
    {
        $msg   = $this->trans($key, $params, $locale);
        $parts = explode('|', $msg);
        if (count($parts) > 1) {
            $msg = ((int) $count === 1) ? $parts[0] : $parts[1];
        }

        return str_replace(array(':count', '{count}'), array($count, $count), $msg);
    }

    private function lookup($locale, $key)
    {
        if (isset($this->messages[$locale]) && isset($this->messages[$locale][$key])) {
            return $this->messages[$locale][$key];
        }
        return null;
    }
}
