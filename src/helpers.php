<?php

use GenAI\I18n\Translator;

/**
 * Global translation helpers, autoloaded via composer "files". They delegate to
 * the shared Translator and degrade gracefully (return the key) when i18n isn't
 * wired — so templates can call __() unconditionally.
 *
 * PHP 5.3-safe.
 */

if (!function_exists('__')) {
    /**
     * @param string $key
     * @param array  $params  :name / {name} placeholders
     * @return string
     */
    function __($key, $params = array())
    {
        $t = Translator::shared();
        return ($t !== null) ? $t->trans($key, $params) : $key;
    }
}

if (!function_exists('locale')) {
    /** Current locale code (e.g. "en"), or "en" if i18n isn't wired. */
    function locale()
    {
        $t = Translator::shared();
        return ($t !== null) ? $t->getLocale() : 'en';
    }
}

if (!function_exists('__n')) {
    /**
     * Pluralized translate — message holds "singular|plural".
     *
     * @param string $key
     * @param int    $count
     * @param array  $params
     * @return string
     */
    function __n($key, $count, $params = array())
    {
        $t = Translator::shared();
        return ($t !== null) ? $t->choice($key, $count, $params) : $key;
    }
}
