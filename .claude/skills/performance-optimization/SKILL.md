---
name: performance-optimization
description: >-
  Make the site fast and score high on every metric — Lighthouse/PageSpeed 90+, Core Web
  Vitals (LCP, CLS, INP), accessibility & best-practices. Covers critical CSS, conditional
  asset loading via manifest, image/font optimization, lazy-loading, caching, render-blocking
  removal, third-party deferral. Use for "оптимізація", "швидкість сайту", performance,
  Lighthouse, PageSpeed, Core Web Vitals, LCP/CLS/INP, "високий рейтинг", швидкий сайт.
---

# Оптимізація продуктивності (Lighthouse / Core Web Vitals)

Мета — 90+ у Lighthouse за всіма категоріями та «good» по Core Web Vitals. Доповнює технічну частину [[seo-optimization]]; тут — глибше про швидкість і метрики.

## Цільові метрики
| Метрика | Ціль | Головний важіль |
|---|---|---|
| **LCP** (завантаження) | < 2.5s | hero-зображення/шрифт: preload, `fetchpriority=high`, без lazy на first-screen |
| **CLS** (зсув) | < 0.1 | width/height або aspect-ratio для всіх медіа; зарезервоване місце під шрифти/банери |
| **INP** (відгук) | < 200ms | менше/легше JS, без важких синхронних обробників, debounce |
| Performance / SEO / A11y / Best Practices | ≥ 90 | усе нижче |

## 1. CSS — критичний vs відкладений
- `src/styles/critical.scss` → `build/css/critical.min.css` **уже інлайниться в `<head>`** (`my_assets()` у [functions-parts/_assets.php](../../../functions-parts/_assets.php)) — це прибирає render-blocking. Тримай у ньому ЛИШЕ first-screen: шрифти, reset, база, типографіка, шапка, hero.
- Решту CSS вантаж **умовно й per-page** через `build/manifest.json`: головний `main` + тільки потрібний `pages/page-*` за `is_page_template()`/`is_singular()`.
- Не вантаж усі сторінкові стилі на кожній сторінці.

## 2. JS — менше і пізніше
- увесь JS у футері: `wp_enqueue_script(..., [], null, true)` (вже так);
- модулі секцій ініціалізуй **умовно** через `[data-section]` (узгоджено з [[section-builder]]) — не виконуй код для відсутніх секцій;
- сторінковий JS — окремий bundle (`src/js/pages/*`), підключай умовно через manifest;
- важкі бібліотеки (`gsap`, `swiper`, `matter-js`, `@fancyapps/ui`, `parallax-js`) — лише там, де треба: перевіряй `delta_has_section()` у `_assets.php` або динамічний `import()` для нижче-екранного інтерактиву;
- жодного блокуючого inline-JS.

## 3. Зображення (зазвичай головний резерв LCP/ваги)
Для готелю це критично — сайт майже цілком складається з фото інтер'єрів.
- сучасні формати (WebP/AVIF); відповідні розміри, не «4000px у контейнер 400px»;
- **first-screen:** `fetchpriority="high"`, БЕЗ `loading="lazy"`, + preload у `<head>`;
- усі інші: `loading="lazy"` + `decoding="async"`;
- завжди `width`/`height` (проти CLS) — з ACF image array (див. [[acf-fields]]);
- `srcset`/`sizes` для адаптиву (`wp_get_attachment_image` дає його автоматично);
- галереї номерів — лайтбокс із ліниво довантаженими повнорозмірними фото, а не всі одразу.

## 4. Шрифти
Базу вже зроблено: Spectral (500/600) і Manrope (400/600/700) self-hosted у `src/fonts/` → `build/fonts/`, з `font-display: swap` і `unicode-range` по сабсетах (latin, latin-ext, cyrillic, cyrillic-ext) — див. `_fonts.scss`. Preload кириличних Spectral 600 + Manrope 400 робить `delta_preload_fonts()` у [_assets.php](../../../functions-parts/_assets.php).

Що тримати в голові далі:
- **не додавай ваг «про запас»** — кожна це +4 файли; візуальну вагу краще брати з наявних;
- якщо перший екран змінить набір накреслень — онови список у `delta_preload_fonts()`, і не преload'ь більше 2–3 файлів;
- ніякого Google Fonts CDN (зайвий домен, preconnect, гірший LCP).

## 5. Кешування та доставка
- довгі `Cache-Control`/`Expires` для `build/` (хешовані імена з Vite дозволяють immutable-кеш);
- gzip/brotli на сервері;
- сторінковий кеш (хостинговий або плагін) — але не кешуй сторінки з формою бронювання/календарем доступності;
- мінімум плагінів; вимкни їх CSS/JS там, де не використовуються (`wp_dequeue_*` у `parts/remove_action.php`).

## 6. Прибрати зайве з WordPress head
Частина вже зроблена в [functions-parts/parts/remove_action.php](../../../functions-parts/parts/remove_action.php) і `headache.php` (generator, emoji-tinymce, block-library CSS, глобальні стилі, feeds, xmlrpc). Що можна додати за потреби:
```php
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('wp_head', 'print_emoji_detection_script', 7);
add_filter('emoji_svg_url', '__return_false');
```

## 7. Сторонні скрипти
- аналітику/піксели — `defer`/async, бажано після взаємодії або `requestIdleCallback`;
- віджети бронювання / карти — вантаж по кліку («показати карту»), а не одразу;
- кожен сторонній скрипт б'є по INP/Best Practices — мінімізуй кількість.

## Як вимірювати
```bash
npm run build          # завжди тестуй продакшн-білд, не dev
npx lighthouse http://hotel-delta.local/ --view --preset=desktop
npx lighthouse http://hotel-delta.local/ --view   # mobile (за замовч.)
```
Також: PageSpeed Insights (польові дані CrUX — після релізу на прод), DevTools → Performance/Coverage (знайти невикористаний CSS/JS). Для прогону в реальному браузері — скіл `/run`.

## Чек-ліст 90+
- [ ] critical CSS інлайн, решта умовно per-page
- [ ] JS у футері, умовна ініціалізація по `[data-section]`
- [ ] hero-зображення: preload + fetchpriority, без lazy
- [ ] усі медіа з width/height (CLS = 0)
- [ ] woff2 + font-display swap + preload first-screen
- [ ] прибрано emoji/generator/block-library
- [ ] сторонні скрипти deferred
- [ ] тестовано на ПРОД-білді

## Чого НЕ робити
- ❌ міряти Lighthouse на dev-білді (без мінімізації);
- ❌ `loading="lazy"` на LCP-зображенні;
- ❌ вантажити всі bundle на кожній сторінці;
- ❌ медіа без розмірів (ламає CLS);
- ❌ важкі бібліотеки заради дрібного ефекту.
