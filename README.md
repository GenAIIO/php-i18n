# genai/i18n

Localization for the GenAI stack — **Spring-style message keys**, compiled the
same way everything else here is: per-locale `.ini` catalogs are baked at build
time into a reflection-free `Cache\Messages`, read at runtime by a `Translator`.
PHP 5.3.29-safe at runtime.

## How it works

```
config/messages/en.ini          config/messages/vi.ini
  login.cta  = "Log in"            login.cta  = "Đăng nhập"
  hello.user = "Hi, :name!"        hello.user = "Chào :name!"
```

Build (`composer compile`) → **`Cache\Messages::all()`** = `array(locale => array(key => message))`.
Runtime → the `Translator` bean (auto-wired) looks up the current locale, falls
back to the fallback locale, then to the key itself, and interpolates `:name` /
`{name}` placeholders.

## Use it

**1. Configure** (`app.ini`, optional):
```ini
[i18n]
default   = en
fallback  = en
available = en,vi
param     = lang        ; ?lang=vi switches
```

**2. Translate** — anywhere, via the global helper (degrades to the key if unwired):
```php
echo __('login.cta');                       // "Log in"
echo __('hello.user', array('name' => $n)); // "Hi, Linh!"
echo __n('games.count', $n);                // "1 game|:count games"
```
In templates: `<?php echo $this->e(__('login.cta')); ?>`. For non-view code you can
also inject the `Translator` bean and call `->trans()` / `->choice()`.

**3. Switch locales per request** — enable the interceptor with a thin subclass
(early, so the locale is set before rendering):
```php
#[Intercept(order: -20)]
class LocaleGuard extends \GenAI\I18n\Interceptor\LocaleInterceptor {}
```
It resolves the locale in this order: `?lang=` (then remembers it in the session) →
session → `Accept-Language` → default. `<html lang="<?php echo $translator->getLocale(); ?>">`.

## Notes

- **Catalogs** live in `config/messages/<locale>.ini`, flat dotted keys. Add a
  locale = add a file; add `?lang=` support = list it in `available`.
- **Pluralization** is the simple `"singular|plural"` convention via `choice()` /
  `__n()` (enough for languages without complex plural rules, like Vietnamese).
- **SEO:** for separately-indexable locales, prefer URL-prefixed routes
  (`/vi/…`) + `hreflang`. The session/`?lang=` approach here is simpler but serves
  one URL per page.
- **Fallback** never throws — a missing key renders as the key, so a half-translated
  catalog degrades gracefully.

## Layers

- `Translator` + `Cache\Messages` — standalone; no web stack needed.
- `I18nConfig` bundle (`genai/di`) auto-wires the `Translator` bean.
- `LocaleInterceptor` needs `genai/web` + `genai/session` (both `suggest`, loaded
  only if you enable it).
