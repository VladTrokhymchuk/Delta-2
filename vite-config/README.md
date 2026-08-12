# vite-config/

Вся кастомна логіка збірки. `vite.config.js` у корені проекту — тонкий: лише зв'язує ці модулі.

## Швидкий старт

**Щоб щось зібралось — просто кинь файл у `src/`:**

| Куди кладеш | Що отримуєш | Тип |
|---|---|---|
| `src/js/*.js` | `build/js/*.min.js` | entry |
| `src/js/pages/**/*.js` | `build/js/pages/**/*.min.js` | entry |
| `src/styles/pages/**/*.scss` | `build/css/pages/**/*.min.css` | entry |
| `src/img/**` | `build/img/**` | copy (крім `sprite/`) |
| `src/img/sprite/*.svg` | вбудовано в JS як SVG-sprite | sprite |
| `src/fonts/**` | `build/fonts/**` | copy |
| `src/static/**` | `build/**` | copy |

**НЕ entry-поінти** (це модулі — імпортуй їх з entry):
- `src/js/modules/`, `src/js/utils/`, будь-які підпапки `src/js/` окрім `pages/`
- `src/styles/partials/`, `src/styles/modules/`, `src/styles/core.scss`

Контракт — єдине джерело істини: [`conventions.js`](conventions.js).

## NPM-скрипти

| Команда | Що робить |
|---|---|
| `npm run build` | Повний production-білд + `manifest.json` |
| `npm run dev` | Watch-режим, unminified, з sourcemaps |
| `npm run serve` | Dev-сервер (HMR, порт 3000). Рестартує при нових entry-файлах |
| `npm run css` | Тільки CSS + статика (для часткового релізу) |
| `npm run js` | Тільки JS + статика |
| `npm run clean` | Видаляє папку `build/` |
| `npm run preview` | Локально запускає готовий `build/` |

## Файли модуля

```
vite-config/
├─ conventions.js                  — контракт "куди кидати файли" + спільні константи
├─ utils.js                        — walk (рекурсивний пошук), toPosix
├─ discover-entries.js             — findJsEntries, findScssPageEntries
├─ scss-page-virtual-plugin.js     — віртуальні JS-обгортки для автосканованих SCSS-сторінок
├─ mirror-css-hierarchy-plugin.js  — переносить папкову ієрархію entry на CSS-ассети
├─ drop-scss-wrappers-plugin.js    — drop wrapper JS + режими css-only / js-only cleanup
├─ copy-assets-plugin.js           — рекурсивне копіювання img/fonts/static
├─ auto-restart-plugin.js          — рестарт dev-сервера при створенні нового entry
├─ manifest-plugin.js              — емітить manifest.json (entry → вихідні файли)
└─ index.js                        — barrel-експорт, єдина точка імпорту для vite.config.js
```

### Хто за що відповідає

**`conventions.js`** — правила іменування і константи. Змінюючи префікси тут, міняєш їх централізовано.

**`utils.js`** — рекурсивний обхід директорій (`walk`) і нормалізація шляхів під POSIX (`toPosix`). Без специфіки збірки.

**`discover-entries.js`**
- `findJsEntries(SRC)` — сканує `src/js/*.js` (верхній рівень) і `src/js/pages/**/*.js` (рекурсивно). Інші підпапки `src/js/` (modules, utils, …) — не entry.
- `findScssPageEntries(SRC, takenKeys)` — рекурсивно сканує `src/styles/pages/**/*.scss`. Якщо JS-entry з таким же іменем вже є (наприклад `src/js/pages/shop/cart.js`), SCSS пропускається — очікується, що JS сам імпортує свій SCSS.

**`scss-page-virtual-plugin.js`** — для автосканованих SCSS-сторінок створює на льоту JS-обгортку виду `import './page-404.scss';`. Без цього Rollup не знає з чим працювати — він очікує JS на вході.

**`mirror-css-hierarchy-plugin.js`** — Rollup за замовчуванням емітить CSS плоско як `css/<basename>.min.css`. Цей плагін перейменовує ассети так, щоб папкова ієрархія entry-поінта дзеркалилась у CSS: entry `js/pages/shop/cart` → `css/pages/shop/cart.min.css`.

**`drop-scss-wrappers-plugin.js`** — містить три плагіни:
- `dropScssPageWrappersPlugin` — прибирає порожні JS-обгортки від SCSS-сторінок (нам потрібен тільки CSS, JS-обгортка — технічна деталь)
- `cssOnlyCleanupPlugin` — режим `npm run css`: прибирає все JS
- `jsOnlyCleanupPlugin` — режим `npm run js`: прибирає все CSS

Всі три з `enforce: 'post'` — запускаються в кінці, коли Vite вже згенерував внутрішні артефакти.

**`copy-assets-plugin.js`** — `src/img`, `src/fonts`, `src/static` → `build/` з повним збереженням вкладеності. `src/img/sprite/` виключено (йде в SVG-sprite окремим плагіном).

**`auto-restart-plugin.js`** — Rollup фіксує набір entry на старті сервера, тому новий файл не підхоплювався б без рестарту. Цей плагін слухає файлову систему і автоматично перезапускає `npm run serve` коли з'являється/зникає entry-eligible файл.

**`manifest-plugin.js`** — емітить `build/manifest.json` з мапою `logical-name → { js, css }`. Потрібен для шаблонних движків (WordPress, Laravel Blade тощо), щоб підключати саме ті файли, які дійсно згенерувались. Приклад для WP:

```php
$manifest = json_decode(file_get_contents(get_template_directory() . '/build/manifest.json'), true);
$base = get_template_directory_uri() . '/build/';

wp_enqueue_style ('theme-app', $base . $manifest['app']['css']);
wp_enqueue_script('theme-app', $base . $manifest['app']['js'], [], null, true);

// на сторінці page-404.php:
if (!empty($manifest['pages/page-404']['css'])) {
    wp_enqueue_style('page-404', $base . $manifest['pages/page-404']['css']);
}
```

**`index.js`** — barrel. `vite.config.js` імпортує тільки звідси — якщо знадобиться рефакторинг внутрішньої структури, зовнішня поверхня не зміниться.

## Як додати нову поведінку

1. Створи `vite-config/<назва>-plugin.js` у форматі інших плагінів.
2. Додай експорт у `index.js`.
3. Підключи у `vite.config.js` в масиві `plugins`.

Не міняй `vite.config.js` для дрібних змін — завжди думай чи не можна зробити це через новий плагін. Конфіг має залишатись тонким.

## Підтримка старих браузерів

Редагуй поле `browserslist` у кореневому [`package.json`](../package.json). Автоматично використається:
- esbuild (`build.target`) — для трансформації JS
- autoprefixer (через [`postcss.config.js`](../postcss.config.js)) — для CSS-префіксів

Конфіг збірки не чіпай.

## Known quirks

- **`url()` warnings** ("didn't resolve at build time") — SCSS посилається на шрифти/картинки, яких фізично немає в `src/fonts/` або `src/img/`. Vite лишає URL як є, файл очікується на рантаймі. Коли покладеш реальні ассети — попередження зникнуть.
- **Dev-сервер і нові entry** — `npm run serve` рестартує автоматично. `npm run dev` (watch-білд) — теж; якщо раптом не підхопив, перезапусти вручну.
