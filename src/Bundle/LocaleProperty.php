<?php

namespace GenAI\I18n\Bundle;

use GenAI\Property\AbstractProperty;
use GenAI\Property\Attribute\Property;
use GenAI\Property\Util\Map;

/**
 * Localization config ([i18n] group):
 *   default   - locale used when nothing else is resolved (default "en")
 *   fallback  - locale to borrow missing keys from (default = default)
 *   available - comma-separated locales the app offers (default = just the default)
 *   param     - query key for switching, e.g. ?lang=vi (default "lang")
 *
 * Optional — sensible defaults apply when the section is absent.
 *
 * Runtime class (PHP 5.3-safe).
 */
#[Property(group: 'i18n', optional: true)]
class LocaleProperty extends AbstractProperty
{
    private $default;
    private $fallback;
    private $available;
    private $param;

    public function bindData(Map $data)
    {
        $this->default   = $data->get('default');
        $this->fallback  = $data->get('fallback');
        $this->available = $data->get('available');
        $this->param     = $data->get('param');
    }

    public function getDefault()
    {
        return ($this->default !== null && $this->default !== '') ? $this->default : 'en';
    }

    public function getFallback()
    {
        return ($this->fallback !== null && $this->fallback !== '') ? $this->fallback : $this->getDefault();
    }

    public function getParam()
    {
        return ($this->param !== null && $this->param !== '') ? $this->param : 'lang';
    }

    /** @return array list of available locale codes */
    public function getAvailable()
    {
        if ($this->available === null || $this->available === '') {
            return array($this->getDefault());
        }
        $out = array();
        foreach (explode(',', $this->available) as $code) {
            $code = trim($code);
            if ($code !== '') {
                $out[] = $code;
            }
        }
        return $out ? $out : array($this->getDefault());
    }
}
