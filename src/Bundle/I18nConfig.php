<?php

namespace GenAI\I18n\Bundle;

use GenAI\Di\Bean;
use GenAI\Di\Configuration;
use GenAI\I18n\I18nFactory;
use GenAI\I18n\Translator;

/**
 * Auto-configuration: provides the Translator bean from the compiled catalogs and
 * [i18n] config. Scanned automatically (extra.genai.scan), so installing the
 * package wires the Translator with no app code.
 *
 * Runtime class (PHP 5.3-safe); the attributes are comments on 5.3.
 */
#[Configuration]
class I18nConfig
{
    #[Bean(Translator::class)]
    public function translator(LocaleProperty $config)
    {
        return I18nFactory::build($config);
    }
}
