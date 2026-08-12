# Project Skills — Hotel Delta (WordPress / Vite theme)

Набір скілів під стек проєкту: WordPress (класичний редактор, без Gutenberg), ACF Pro + `acf-json`, Yoast SEO, фронтенд на JS/SCSS зі збіркою Vite у `build/`, верстка з Figma.

**Стан проєкту:** тема — чистий каркас. Дизайн будується з нуля за макетом Figma: бренд-палітра вже заведена в `src/styles/partials/_vars.scss`, а секцій, CPT і ACF-полів ще немає — створюються по ходу верстки.

| Скіл | Коли спрацьовує |
|---|---|
| **section-builder** | Верстаєш нову секцію з Figma → ACF layout + PHP part + SCSS + JS (4 артефакти з одним slug). |
| **page-constructor** | Каркас «конструктора типових сторінок»: Flexible Content, рендер-луп, шаблони, розбивка functions.php. |
| **custom-post-types** | Реєстрація CPT і таксономій. Правило: лише кастомні, без дефолтних Posts/Categories. |
| **acf-fields** | ACF Pro через acf-json: save/load point, неймінг, безпечне читання полів, repeater/flexible. |
| **seo-optimization** | On-page + технічне SEO з Yoast: семантика, schema, перформанс, зображення. |
| **seo-for-ai** | GEO/AEO — щоб GPT/Claude/Perplexity/AI Overviews знаходили й цитували: llms.txt, JSON-LD, доступ краулерам, answer-first. |
| **code-testing** | Тестування/лінтинг PHP (phpcs/WPCS, phpstan, `php -l`), JS (eslint, vitest), SCSS (stylelint), smoke-перевірка білда. |
| **performance-optimization** | Швидкість і 90+ у Lighthouse/Core Web Vitals: critical CSS, умовні ассети, зображення/шрифти, кеш, прибирання зайвого. |

## Координати проєкту
- **Тема:** `wp-content/themes/Delta-2` (Theme Name: Hotel Delta).
- **Локальний сайт:** http://hotel-delta.local/ (Local by Flywheel).
- **Префікс функцій:** `delta_`; **text domain:** `delta`.
- **Мова інтерфейсу й контенту:** українська.

## Архітектурні засади (спільні для всіх скілів)
- **Масштабованість через конструктор:** сторінка = набір секцій (ACF Flexible Content `page_sections`), що збираються в класичному редакторі. Жодного Gutenberg.
- **Одна секція = 4 артефакти з однаковим slug** (ACF layout / `template-parts/sections/{slug}.php` / `src/styles/sections/_{slug}.scss` / `src/js/sections/{slug}.js`).
- **Лише кастомні пост-тайпи й таксономії** (реєстрація — `functions-parts/_post-types-registration.php`, зараз порожня).
- **Контент — тільки через ACF**, ніякого хардкоду в PHP.
- **PHP розбитий на частини:** `functions.php` лише підключає `functions-parts/*`.
- **Фронтенд-конвенції** (Vite, SCSS `@use '../core' as *`, `manifest.json`, аліаси) — описані в `../../README.md` теми. Збірку (`vite-config/`) не чіпати.

Скіли посилаються один на одного через `[[name]]`.
