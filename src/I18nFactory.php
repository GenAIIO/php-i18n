<?php

namespace GenAI\I18n;

use GenAI\I18n\Bundle\LocaleProperty;

/**
 * Builds the Translator from the compiled Cache\Messages and [i18n] config, and
 * registers it as the shared instance for the global __() helper.
 *
 * Runtime class (PHP 5.3-safe).
 */
class I18nFactory
{
    public static function build(LocaleProperty $cfg)
    {
        $messages = array();
        if (class_exists('Cache\\Messages')) {
            $messages = \Cache\Messages::all();
        }

        $translator = new Translator($messages, $cfg->getDefault(), $cfg->getFallback());
        Translator::useShared($translator);   // back the global __() helper

        return $translator;
    }
}
