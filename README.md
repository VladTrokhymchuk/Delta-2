# Hotel Delta — WordPress-тема

Тема готелю «Дельта» (локально: http://hotel-delta.local/). Збірка фронтенду на **Vite**: швидкий watch-білд, продакшн-мінімізація, авто-підхоплення нових entry-файлів (JS + page-SCSS), кросбраузерність через browserslist. Вся кастомна логіка збірки винесена в [`vite-config/`](vite-config/README.md) — головний `vite.config.js` лишається тонким.

**Архітектура теми** (конструктор сторінок на ACF Flexible Content, CPT, SEO) описана в скілах — [`.claude/skills/`](.claude/skills/README.md). Цей файл — про збірку фронтенду.

**Стан:** каркас. Дизайн верстається з нуля за макетом Figma: бренд-палітра (`_vars.scss`), шрифти (`_fonts.scss`) і типографічна сходинка (`_typography.scss`) уже з макета; секцій і CPT ще немає.

---

## Вимоги

- **Node.js** ≥ 18
- **npm** ≥ 9

## Встановлення

```bash
npm install
```

## Команди

| Команда | Що робить |
|---|---|
| `npm run dev` | Watch-режим (`vite build --watch --mode development`). Інкрементальний перебілд у `build/` при кожному збереженні. Без мінімізації, з sourcemaps. |
| `npm run build` | Повна продакшн-збірка: мінімізація JS+CSS, tree-shaking, очищення `build/`, генерація `manifest.json`. |
| `npm run js` | Збирає лише JS-бандли (мінімізовано). |
| `npm run css` | Збирає лише CSS-файли (мінімізовано). |
| `npm run serve` | Dev-сервер Vite з HMR на `http://localhost:3000`. Авто-рестарт при нових entry-файлах. |
| `npm run preview` | Локальний перегляд готового `build/`. |
| `npm run clean` | Видаляє папку `build/`. |

---

## Структура проєкту

```
Delta-2/
├── src/
│   ├── fonts/                     # .woff2 — копіюються в build/fonts/
│   ├── img/                       # усе копіюється в build/img/ (зберігає вкладеність)
│   │   ├── icons/, content/, sprite/
│   │   └── <будь-яка-папка>/      # нова папка — копіюється автоматично
│   ├── js/
│   │   ├── app.js                 # головний entry
│   │   ├── sections/              # модулі секцій конструктора (guard по [data-section])
│   │   ├── modules/               # власні JS-модулі (header, popup, …)
│   │   ├── utils/                 # чисті хелпери (їх і покривають тестами)
│   │   ├── libs/                  # сторонні/власні бібліотеки
│   │   └── pages/                 # *.js — кожен файл = окремий bundle
│   └── styles/
│       ├── _core.scss             # @forward для vars + mixins + functions
│       ├── main.scss              # головний CSS-entry (foundation + секції)
│       ├── critical.scss          # critical CSS — інлайниться в <head>
│       ├── partials/              # foundation
│       │   ├── _vars.scss         # кольори + breakpoints + grid (ЄДИНЕ МІСЦЕ)
│       │   ├── _mixins.scss       # size, minw/maxw, adaptiv-value
│       │   ├── _functions.scss    # rem(), color(), encodecolor(), checkbox-bg()
│       │   ├── _fonts.scss        # @font-face
│       │   ├── _reset.scss
│       │   ├── _base.scss         # html/body/container + :root CSS vars
│       │   ├── _grids.scss
│       │   ├── _typography.scss
│       │   ├── _sup-classes.scss  # utility-класи
│       │   ├── _forms.scss
│       │   ├── _checkbox.scss
│       │   └── _bttn.scss
│       ├── sections/              # стилі секцій конструктора + _index.scss (@forward)
│       ├── modules/               # компоненти (header, footer, card, …)
│       └── pages/                 # *.scss — кожен файл = окремий CSS-entry
├── build/                         # згенерована збірка (підключається з WP-теми)
├── functions-parts/               # PHP теми (див. .claude/skills/page-constructor)
├── template-parts/sections/       # шаблони секцій конструктора
├── acf-json/                      # групи полів ACF (версіонуються)
├── vite-config/                   # кастомні плагіни — див. vite-config/README.md
├── vite.config.js
├── postcss.config.js
└── package.json
```

---

## Результат збірки

```
build/
├── css/
│   ├── main.min.css
│   ├── critical.min.css
│   └── pages/
│       ├── page-404.min.css
│       └── …                     # дзеркалить src/styles/pages/
├── js/
│   ├── app.min.js
│   ├── pages/                    # окремий bundle на кожен src/js/pages/*.js
│   └── chunks/                   # спільний код (auto code-splitting)
├── img/                          # повна копія src/img/
├── fonts/
└── manifest.json                 # мапа entry → файли (для WP enqueue)
```

---

## Авто-підхоплення (нічого не налаштовувати)

Конфіг сканує `src/` на кожному старті. Додайте файл — він потрапить у збірку.

| Додали | Отримаєте у `build/` |
|---|---|
| `src/js/app.js` | `build/js/app.min.js` |
| `src/js/pages/shop.js` | `build/js/pages/shop.min.js` |
| `src/styles/pages/page-shop.scss` | `build/css/pages/page-shop.min.css` |
| `src/img/products/foto.jpg` | `build/img/products/foto.jpg` |
| Нова папка `src/img/banners/` | `build/img/banners/` (повна копія) |

**Page-SCSS стають entry автоматично** — не треба імпортувати з JS. Якщо ж є парний JS із тим самим ім'ям (наприклад `src/js/pages/shop.js`), то SCSS очікується підключеним з нього (щоб уникнути дубля).

**Інші SCSS** (`partials/`, `sections/`, `modules/`, `_core.scss`) — підключаються через `@use`/`@forward` з entry-файлів (`main.scss`, `critical.scss`). Vite не сканує їх автоматично — це стандартна SCSS-конвенція.

Детальніше про внутрішню механіку — [`vite-config/README.md`](vite-config/README.md).

---

## Система кольорів

Єдине джерело правди — SCSS-мапа `$colors` у [src/styles/partials/_vars.scss](src/styles/partials/_vars.scss).

```scss
Бренд-палітра з Figma «1. Color Architecture» — п'ять бренд-кольорів, з яких виведені всі решта:

| Токен | HEX | Роль за макетом |
|---|---|---|
| `$forest` → `--primary` | `#163324` | Forest Green — айдентика, шапка, головні кнопки |
| `$gold` → `--secondary` | `#CCA86E` | Warm Gold — рамки, акценти, підсвітка серифів |
| `$copper` → `--accent` | `#B0733C` | Amber/Copper — фокус-теги, CTA |
| `$linen` → `--bg-alt` | `#F4EFE6` | Warm Linen — вторинні фони й панелі |
| `$charcoal` → `--text` | `#222B25` | Charcoal Green — основний текст |

Семантичні: `--success #2E7D32`, `--warning #EF6C00`, `--error #C62828`, `--info #00695C`.

Похідні (hover-стани й нейтралі) виведені з бренд-кольорів і зафіксовані як hex:
`--primary-dark #10251A`, `--primary-light #25573D`, `--secondary-dark #BF9148`,
`--accent-dark #925F32`, `--text-muted #61665F`, `--border #DBD7CF`.

**Два обмеження за контрастом** (розрахунки — у шапці `_vars.scss`):
- білий текст на `--accent` дає 3.9:1 → для дрібного тексту бери `--accent-dark` (5.4:1);
- Warm Gold на світлому фоні — 2.2:1, тобто лише лінії та декор, не текст.
```

### Як використовувати

**CSS custom properties** — основний спосіб (підтримує runtime-зміну, зручно для dark mode):

```scss
.card {
  background: var(--bg-alt);
  color: var(--text);
  border: 1px solid var(--border);
}

.btn-primary {
  background: var(--accent);
  color: var(--text-inverse);

  &:hover { background: var(--accent-dark); }
}
```

CSS-змінні автоматично генеруються в `:root` через `@each` у [_base.scss](src/styles/partials/_base.scss). Додали ключ у мапу — одразу доступно як `var(--<ключ>)`.

**SCSS-функція `color('name')`** — для compile-time операцій (`rgba()`, `mix()`, `darken()`):

```scss
.overlay {
  background: rgba(color('black'), 0.5);
  box-shadow: 0 4px 12px rgba(color('accent'), 0.3);
}
```

**Шорткат-змінні** для найпоширеніших: `$accent`, `$text`, `$bg`, `$black`, `$white`.

### Додати новий колір

Один рядок у `$colors` — і він одразу доступний і як `var(--my-color)`, і як `color('my-color')`.

---

## SCSS: інструменти та конвенції

### `@use` замість `@import`

Проєкт використовує сучасний `@use` / `@forward`. Глобального scope немає — кожен файл явно декларує залежності.

**Правило**: у новому файлі `modules/` або `pages/`, якщо потрібні змінні/міксини/функції, перший рядок:

```scss
@use '../core' as *;
```

Після цього доступні: `$accent`, `$tablet`, `@include minw()`, `rem()`, `color()` тощо.

### Міксини ([_mixins.scss](src/styles/partials/_mixins.scss))

| Міксин | Приклад |
|---|---|
| `minw($bp)` / `maxw($bp)` | `@include minw($tablet) { ... }` |
| `minh($bp)` / `maxh($bp)` | аналогічно по висоті |
| `size($w, $h?)` | `@include size(40px)` → квадрат; `@include size(40px, 20px)` |
| `hide-text` | приховати текст (image replacement) |
| `adaptiv-value($prop, $start, $min, $type)` | плавна зміна значення між брейкпоінтами |

### Функції ([_functions.scss](src/styles/partials/_functions.scss))

| Функція | Що робить |
|---|---|
| `rem($value)` | `rem(16)` → `1.6rem` (base 10px: `html { font-size: 10px }`) |
| `color($name)` | доступ до кольору з мапи `$colors` |
| `encodecolor($color)` | кодує HEX для data-URI |
| `checkbox-bg(w, h, fill, opacity)` | data-URI птичка для чекбоксу |

### Брейкпоінти

```scss
$fhd:    1900px;
$lg:     1400px;
$pc:     $containerWidth * 1px;  // 1320px = 1280 контенту + 2×20 полів
$sm-pc:  992px;
$tablet: 768px;
```

---

## Робота з JavaScript

### Головний entry

`src/js/app.js` — завантажує спільні модулі (header, попапи, анімації) та init-функції секцій із `src/js/sections/`. Кожна init-функція сама перевіряє наявність своєї секції (`[data-section]`), тож зайвий код не виконується.

> **Підкреслення в іменах — не стиль, а вимога.** Кожен `.js` без `_` стає окремим entry (див. «Авто-підхоплення»), і `import` із `app.js` перетворюється на ESM-import між бандлами. `app.min.js` підключається класичним `<script>`, тому сторінка впаде з «Cannot use import statement outside a module».
> Спільні модулі й модулі секцій → `_header.js`, `_hero.js`. Без `_` — тільки `src/js/pages/*.js`, вони підключаються окремим тегом.

### Окремі сторінки

Додайте файл у `src/js/pages/` — він автоматично стане окремим bundle:

```js
// src/js/pages/shop.js
import '@styles/pages/page-shop.scss';
import { initFilters } from '@js/modules/shop-filters.js';

initFilters();
```

### Бібліотеки з node_modules

Імпорт напряму — Vite резолвить і оптимізує:

```js
import { gsap } from 'gsap';
import Swiper from 'swiper/bundle';
import 'swiper/css/bundle';
import { Fancybox } from '@fancyapps/ui';
import '@fancyapps/ui/dist/fancybox/fancybox.css';
import Matter from 'matter-js';
```

### Аліаси

| Аліас | Вказує на |
|---|---|
| `@` | `src/` |
| `@js` | `src/js/` |
| `@styles` | `src/styles/` |
| `@img` | `src/img/` |

```js
import { slider } from '@js/modules/slider.js';
import '@styles/modules/modal.scss';
```

---

## Зображення та шрифти

Усе з `src/img/` та `src/fonts/` копіюється у `build/` зі збереженням вкладеності. Для шрифтів — **.woff2**.

### Шрифти проєкту

Дві родини з макета, **self-hosted** (без Google Fonts CDN), сабсети `latin`, `latin-ext`, `cyrillic`, `cyrillic-ext` — 20 файлів, 276 KB, ліцензія OFL:

| Родина | Ваги | Де застосовується |
|---|---|---|
| **Spectral** (serif) | 400, 500, 600 | H1–H3; 400 — слоган у підвалі |
| **Manrope** (sans) | 400, 600, 700 | H4, body, кнопки, overline |

`@font-face` згенеровані в [_fonts.scss](src/styles/partials/_fonts.scss) з `unicode-range` — браузер тягне лише потрібний сабсет. Кириличні файли Spectral 600 і Manrope 400 додатково преload'яться (`delta_preload_fonts()` у `_assets.php`).

### Типографічна сходинка

| Роль | Родина / вага | Розмір | Leading | Колір |
|---|---|---|---|---|
| H1 | Spectral 600 | 40px | 1.2 | `--primary` |
| H2 | Spectral 500 | 32px | 1.2 | `--primary` |
| H3 | Spectral 500 | 24px | 1.3 | `--primary` |
| H4 | Manrope 700 | 16px | 1.4 | `--text` |
| Body | Manrope 400 | 14px | 1.5 | `--text` |
| `.overline` | Manrope 600 | 12px | 1.2 | `--secondary-deep` `#8A6E3B`, uppercase, tracking 2px |

`--secondary-deep` — це макетний `#947640`, затемнений до AA-контрасту (4.3:1 → 4.8:1 на білому), бо overline набирається 12px.

Класи-двійники `.h1`–`.h4` — коли візуальний рівень не збігається з семантичним (тег обираємо за структурою документа, вигляд — класом).

### Сітка

Контент — **1280px** (`$maxWidthContainer`), бокові поля контейнера — 20px. Поля додані **зверху** до `max-width` (`$containerWidth = 1320`), тому на фреймі 1440 з макета відступ від краю до контенту виходить рівно 80px, а сам контент лишається 1280. Один клас `.container` — і в шапці, і в підвалі, і в секціях; окремих падінгів у модулях не задаємо.

Радіус за замовчуванням — `$radius: 6px` (кнопки, поля, логотип у шапці).

### UI-кіт: жива звірка з макетом

[src/static/ui-kit.html](src/static/ui-kit.html) → `build/ui-kit.html`, відкривається без WordPress:
**http://hotel-delta.local/wp-content/themes/Delta-2/build/ui-kit.html**

Сторінка підключає ту саму зібрану `main.min.css`, що й сайт, і показує палітру, типографічну сходинку та стани кнопок і полів. Змінив токен у `partials/` — перевір тут, перш ніж верстати секцію.

**Кнопки:** `.bttn` (Primary), `.bttn--secondary` (рамка Warm Gold), `.bttn--ghost` (лише текст), `.bttn--accent` (CTA), `.bttn--light` (на темному), `[disabled]` → Sage.
**Поля:** default / `:focus` / `.field--error` (або `.wpcf7-not-valid` від CF7), підпис — `.field__label`.
**Шапка:** та сама розмітка, що в `header.php`, разом із бандлом `app.min.js` — бургер-меню перевіряється звуженням вікна.

### Шапка сайту

Розмітка — [header.php](header.php), хелпери — [functions-parts/parts/header.php](functions-parts/parts/header.php), стилі — [_header.scss](src/styles/modules/_header.scss), JS — [_header.js](src/js/modules/_header.js).

Контент редагується в **Налаштування теми** (ACF Options, група `acf-json/group_theme_settings.json`): `header_logo`, `header_brand`, `header_tagline`, `header_button`. Кожне поле має фолбек із макета, тож шапка коректна й до заповнення.

Меню — **Зовнішній вигляд → Меню**, локація `header_menu`. Поки меню не призначене, виводиться плейсхолдер із пунктами макета (і підказка адміну з посиланням на налаштування).

### Підвал

Розмітка — [footer.php](footer.php), хелпери — [functions-parts/parts/footer.php](functions-parts/parts/footer.php), стилі — [_footer.scss](src/styles/modules/_footer.scss).

Увесь контент, **окрім колонки «Навігація»**, редагується в ACF Options → вкладка «Підвал»:

| Поле | Що це |
|---|---|
| `footer_about` | опис під назвою (2–3 рядки) |
| `footer_nav_title` | заголовок колонки меню (дефолт «Навігація») |
| `footer_socials_title` | заголовок колонки соцмереж |
| `footer_socials` | repeater `network` + `url` — назва мережі стає текстом посилання |
| `footer_copyright` | копірайт; підтримує `%year%` |
| `footer_slogan` | золотий курсивний рядок праворуч |

Колонка «Навігація» — WP-меню локації `footer_menu`. Соцмережі без жодного заповненого рядка колонку не малюють; порожній `footer_about` / `footer_slogan` теж просто не виводяться.

Розміри з макета: висота 420 = 80 (падінг) + 191 (ряд колонок) + 64 (gap) + 45 (нижня смуга) + 40 (падінг знизу). Бокові 80px дає `.container`, тож власних горизонтальних падінгів у футері немає. Текст на темному — Warm Linen `#F4EFE6` (`--bg-alt`), лінія над копірайтом — Warm Gold.

---

## Підключення у WordPress-темі

Це вже зроблено у [functions-parts/_assets.php](functions-parts/_assets.php): `get_manifest()` кешує розбір `build/manifest.json`, `get_asset($key, $type)` віддає URL. Шляхи до файлів ніде не хардкодяться.

Поточні ключі manifest: `critical` (інлайниться в `<head>`), `main`, `app`, `pages/page-404`.

Новий сторінковий ассет підключається умовно:

```php
if (is_page_template('page-rooms.php') && ($css = get_asset('pages/page-rooms', 'css'))) {
    wp_enqueue_style('page-rooms', $css, ['main'], null);
}
```

Ліба, потрібна лише одній секції конструктора, — через `delta_has_section()`:

```php
if (delta_has_section('rooms-slider')) {
    wp_enqueue_script('swiper', get_stylesheet_directory_uri() . '/build/js/libs/swiper-bundle.min.js', [], null, true);
}
```

---

## Кросбраузерність

Цільові браузери — секція `browserslist` у `package.json`:

```json
"browserslist": [
  "> 0.5%",
  "last 2 versions",
  "Firefox ESR",
  "not dead"
]
```

Одночасно налаштовує **esbuild** (JS target) і **autoprefixer** (CSS префікси). Конфіг збірки не чіпайте — достатньо змінити `browserslist`.

---

## Typical workflow

1. `npm run dev` — запустили watch.
2. Редагуєте файли у `src/`.
3. Vite інкрементально перебілдує у `build/` (тільки залежні chunks).
4. WP підхоплює свіжі файли через `manifest.json`.
5. Перед продом: `npm run build` — повна мінімізація.

---

## Troubleshooting

**Page-SCSS не компілюється в окремий CSS.**
Перевірте що файл лежить у `src/styles/pages/`. Якщо є парний JS з тим самим ім'ям у `src/js/pages/` — імпортуйте SCSS з нього.

**Новий entry-файл не підхопився у watch.**
`npm run serve` рестартується автоматично. `npm run dev` — теж; якщо раптом ні, перезапустіть вручну.

**Помилка `Колір 'X' не знайдено в $colors`.**
Ви викликали `color('X')` з неіснуючим ключем. Додайте його в мапу `$colors` у [_vars.scss](src/styles/partials/_vars.scss) або виправте друкарську помилку.

**`url()` warning при білді.**
SCSS посилається на файл, якого фізично немає в `src/`. Vite лишає URL як є — файл очікується на рантаймі. Коли покладете реальний ассет — попередження зникне.

---

## Технології

- [Vite 5](https://vitejs.dev/) — бандлер
- [Sass](https://sass-lang.com/) — препроцесор (modern API, `@use`/`@forward`)
- [PostCSS + Autoprefixer](https://postcss.org/) — префікси
- [esbuild](https://esbuild.github.io/) — мінімізація JS

## Ліцензія

MIT © Vlad Trokhymchuk
