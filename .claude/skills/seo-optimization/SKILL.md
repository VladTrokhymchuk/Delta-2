---
name: seo-optimization
description: >-
  On-page & technical SEO with Yoast SEO — semantic HTML, single H1, meta/OpenGraph,
  JSON-LD schema, XML sitemap, image alt & lazy-load, internal linking, Core Web Vitals/
  performance. Use for SEO, "сео оптимізація", Yoast, мета-теги, schema, structured data,
  перформанс, sitemap, canonical, хлібні крихти.
---

# SEO-оптимізація (Yoast + технічне SEO)

SEO-движок — **Yoast SEO** (мета-теги, canonical, OG, sitemap, breadcrumbs). Тема НЕ дублює того, що робить Yoast — вона дає чисту семантику й коректний markup. Для оптимізації під AI — окремий скіл [[seo-for-ai]]: він покриває дві осі — GEO/AEO (щоб AI-движки цитували) і agentic browsing (щоб AI-агент керував сайтом). Важливо: **семантика + a11y + низький CLS з цього скіла одночасно живлять agentic browsing** — той самий markup, що добрий для SEO, робить сайт придатним для AI-агентів.

## Розподіл відповідальності
- **Yoast робить:** `<title>`, meta description, canonical, OpenGraph/Twitter, `robots`, XML sitemap, default schema (Organization/WebSite/WebPage), breadcrumbs.
- **Тема робить:** семантичну розмітку, ієрархію заголовків, alt/lazy зображень, внутрішні посилання, продуктивність, додаткову JSON-LD під CPT.

`add_theme_support('title-tag')` уже стоїть (`parts/configure_menu.php`) — без нього Yoast не виводить `<title>`.

## Семантика та заголовки (правила розмітки секцій)
- **Один `<h1>` на сторінку** — зазвичай у hero (див. [[section-builder]]). Решта секцій — `<h2>`, підрозділи `<h3>`. Не перестрибувати рівні.
- Семантичні теги: `<header> <main> <section> <article> <nav> <footer>`. Кожна секція — `<section>`.
- Списки/таблиці справжніми тегами, не `<div>`.
- Landmark-доступність = SEO: `<nav aria-label>`, кнопки/посилання за призначенням.

## Зображення
- реальний `alt` з контексту (з ACF або заголовка), не назва файлу;
- `loading="lazy"` усім, КРІМ first-screen (там `fetchpriority="high"`);
- `width`/`height` або aspect-ratio проти CLS;
- сучасні формати; ассети йдуть через `src/img/` → `build/img/` (README теми).

## Yoast: інтеграція в темі
- Хлібні крихти Yoast у шаблонах:
  ```php
  if (function_exists('yoast_breadcrumb')) {
    yoast_breadcrumb('<nav class="breadcrumbs" aria-label="Хлібні крихти">', '</nav>');
  }
  ```
- НЕ виводь власні `<title>`/meta/canonical/OG — це робить Yoast.
- Для кожного нового CPT увімкни його в Yoast (Search Appearance) і задай шаблони title/description.
- Якщо CPT не має бути в індексі (службовий довідник) — `noindex` у Yoast для цього типу.

## Додаткова JSON-LD під сутності
Yoast дає базову схему; під конкретні сутності додай свою у `functions-parts/` (окремий файл `_seo.php`, підключений із `functions.php`) через хук `wp_head` — або `wpseo_schema_graph` для інтеграції з графом Yoast.

Для готелю доречні типи: **Hotel / LodgingBusiness** (адреса, телефон, зірки, зручності), **HotelRoom**, **Offer**, **FAQPage**, **BreadcrumbList**, **Article** для новин.

```php
add_action('wp_head', function () {
  if (!is_singular('room')) return;
  $schema = [
    '@context'    => 'https://schema.org',
    '@type'       => 'HotelRoom',
    'name'        => get_the_title(),
    'description' => wp_strip_all_tags(get_the_excerpt()),
    'url'         => get_permalink(),
  ];
  echo '<script type="application/ld+json">'
     . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
     . '</script>';
});
```
Схема має відповідати видимому контенту — інакше це порушення правил і ризик санкцій.

## Технічна продуктивність (Core Web Vitals)
- CSS/JS — мінімізовані через `npm run build`; підключай **умовно** per-page через `manifest.json`, не вантаж усе всюди (див. `_assets.php`).
- `critical.scss` — критичний CSS інлайном у `<head>` (вже реалізовано); решта — у футері.
- JS у футері (`wp_enqueue_script(..., true)`), модулі ініціалізуй умовно (`[data-section]`, див. [[section-builder]]).
- `font-display: swap` і preload first-screen шрифту; деталі — [[performance-optimization]].
- мінімум сторонніх скриптів; defer аналітики.

## Чого НЕ робити
- ❌ дублювати title/meta/OG, які генерує Yoast;
- ❌ кілька `<h1>` або порушена ієрархія заголовків;
- ❌ `alt`=ім'я файлу або порожній на змістовних зображеннях;
- ❌ вантажити всі page-bundle на кожній сторінці.
