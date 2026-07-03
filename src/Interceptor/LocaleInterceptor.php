<?php

namespace GenAI\I18n\Interceptor;

use GenAI\I18n\Bundle\LocaleProperty;
use GenAI\I18n\Translator;
use GenAI\Session\Session;
use GenAI\Web\Interceptor\Interceptor;
use GenAI\Web\Interceptor\RequestHandler;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Resolves the request locale and sets it on the Translator, before controllers
 * run. Resolution order (Spring's LocaleChangeInterceptor + LocaleResolver):
 *
 *   1. ?lang=xx  (only if it's an available locale) — and remembered in the session
 *   2. the locale stored in the session from a previous switch
 *   3. the best match from the Accept-Language header
 *   4. the configured default
 *
 * Like CsrfInterceptor, this base is NOT itself an #[Intercept] — an app enables
 * it with a thin subclass, ideally early so the locale is set before rendering:
 *
 *   #[Intercept(order: -20)]
 *   class LocaleGuard extends \GenAI\I18n\Interceptor\LocaleInterceptor {}
 *
 * Runtime class (PHP 5.3-safe).
 */
class LocaleInterceptor implements Interceptor
{
    private $translator;
    private $config;
    private $session;

    public function __construct(Translator $translator, LocaleProperty $config, Session $session)
    {
        $this->translator = $translator;
        $this->config     = $config;
        $this->session    = $session;
    }

    public function intercept(ServerRequestInterface $request, RequestHandler $next)
    {
        $available = $this->config->getAvailable();
        $locale    = $this->resolve($request, $available);

        $this->translator->setLocale($locale);

        return null; // never stops the request
    }

    private function resolve(ServerRequestInterface $request, $available)
    {
        // 1. explicit ?lang= switch (persisted)
        $query = $request->getQueryParams();
        $param = $this->config->getParam();
        if (isset($query[$param]) && in_array($query[$param], $available, true)) {
            $this->session->set('locale', $query[$param]);
            return $query[$param];
        }

        // 2. previously chosen, from the session
        $saved = $this->session->get('locale');
        if ($saved !== null && in_array($saved, $available, true)) {
            return $saved;
        }

        // 3. Accept-Language
        $fromHeader = $this->fromAcceptLanguage($request->getHeaderLine('Accept-Language'), $available);
        if ($fromHeader !== null) {
            return $fromHeader;
        }

        // 4. default
        return $this->config->getDefault();
    }

    /** First available locale whose primary subtag matches the header, else null. */
    private function fromAcceptLanguage($header, $available)
    {
        if ($header === '' || $header === null) {
            return null;
        }
        foreach (explode(',', $header) as $part) {
            $tag  = trim($part);
            $semi = strpos($tag, ';');
            if ($semi !== false) {
                $tag = substr($tag, 0, $semi);
            }
            $tag     = strtolower(trim($tag));
            $primary = strpos($tag, '-') !== false ? substr($tag, 0, strpos($tag, '-')) : $tag;
            foreach ($available as $code) {
                if (strtolower($code) === $tag || strtolower($code) === $primary) {
                    return $code;
                }
            }
        }
        return null;
    }
}
