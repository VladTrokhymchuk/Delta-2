---
name: section-builder
description: >-
  Build a Figma section as a reusable ACF Flexible Content block (layout) end-to-end:
  ACF layout in acf-json, PHP template part, SCSS module, JS module, and wiring into
  the page constructor. Use whenever coding/верстаючи a new section from a Figma link
  ("додай секцію", "build block", "верстай блок", hero/features/cta/steps/faq sections).
---

# Section Builder — секція з Figma як ACF-блок

Кожна секція проєкту — це **layout у ACF Flexible Content** (`page_sections`), а не окремий шаблон. Це дає масштабованість: контент-менеджер збирає сторінку з готових секцій у класичному редакторі. Дивись також [[page-constructor]] та [[acf-fields]].

Дизайн Hotel Delta будується з нуля, тому секцій поки немає — перша секція створює й саму групу `group_page_builder` в `acf-json/`.

## Залізне правило: одна секція = 4 артефакти з однаковим slug

Для секції зі slug `hero` створюєш РІВНО ці файли:

| Артефакт | Шлях | Призначення |
|---|---|---|
| ACF layout | `acf-json/group_page_builder.json` → layout `hero` | поля контенту |
| PHP template part | `template-parts/sections/hero.php` | розмітка |
| SCSS | `src/styles/sections/_hero.scss` | стилі |
| JS (за потреби) | `src/js/sections/hero.js` | інтерактив |

Slug — lowercase-kebab, стабільний (не перейменовувати після створення — зламає контент у БД).

## Алгоритм

### 1. Розбери Figma
Перед кодом зафіксуй: назву секції, список полів (заголовок, текст, кнопки, повторювані елементи → repeater, зображення), брейкпоінти. **Збережи посилання на Figma-ноду** — воно йде в шапку PHP і SCSS як коментар.

Токени дизайну беруться з `src/styles/partials/`: кольори бренд-палітри вже заведені у `_vars.scss` (`--primary` Forest Green, `--secondary` Warm Gold, `--accent` Copper, `--bg-alt` Linen, `--text` Charcoal), шрифти в `_fonts.scss` — ще плейсхолдер. Нових хексів у секціях не з'являється: якщо в макеті колір, якого немає в мапі, — додай його у `$colors`, а не інлайном.

### 2. ACF layout (через acf-json)
Додай новий layout у Flexible Content `page_sections`. Дотримуйся [[acf-fields]]:
- `name` layout = slug (`hero`)
- поля з префіксом-сенсом: `hero_title`, `hero_subtitle`, `hero_buttons` (repeater), `hero_image`
- repeater для будь-чого, що повторюється (картки номерів, переваги, FAQ)
- редагуй у адмінці ACF → JSON синхронізується сам у `acf-json/`; або акуратно правити JSON руками (зберігай `key`, `parent`, `modified`).

### 3. PHP template part
`template-parts/sections/hero.php`:

```php
<?php
/**
 * Section: Hero
 * Figma: <ВСТАВ ПОСИЛАННЯ НА НОДУ>
 * Поля з ACF Flexible Content layout "hero".
 */

$title    = get_sub_field('hero_title');
$subtitle = get_sub_field('hero_subtitle');
$image    = get_sub_field('hero_image');
$buttons  = get_sub_field('hero_buttons');

if (!$title && !$image) return; // не рендеримо порожню секцію
?>
<section class="section section--hero" data-section="hero" id="hero">
  <div class="container">
    <?php if ($title): ?>
      <h2 class="hero__title"><?= esc_html($title) ?></h2>
    <?php endif; ?>
    <?php if ($subtitle): ?>
      <p class="hero__subtitle"><?= esc_html($subtitle) ?></p>
    <?php endif; ?>
    <?php if ($buttons): ?>
      <div class="hero__actions">
        <?php foreach ($buttons as $btn):
          $link = $btn['link']; if (!$link) continue; ?>
          <a class="bttn" href="<?= esc_url($link['url']) ?>"
             target="<?= esc_attr($link['target'] ?: '_self') ?>">
            <?= esc_html($link['title']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
```

Правила розмітки:
- корінь: `<section class="section section--{slug}" data-section="{slug}">` — `data-section` потрібен JS для умовної ініціалізації.
- семантика для SEO ([[seo-optimization]]): один `<h1>` на сторінку — у hero зазвичай `<h1>`, решта секцій `<h2>`. BEM-неймінг блоку.
- ВСЕ екранувати: `esc_html`, `esc_url`, `esc_attr`, `wp_kses_post` для rich text.
- тексти інтерфейсу (не з ACF) — через `__('...', 'delta')`.
- ранній `return` якщо ключові поля порожні.

### 4. SCSS
`src/styles/sections/_hero.scss` — перший рядок завжди `@use '../core' as *;` (доступ до `$accent`, `rem()`, `color()`, `minw()`, `adaptiv-value()` — див. README теми). Стилізуй від мобільного вгору через `@include minw($tablet)`.

```scss
@use '../core' as *;
// Figma: <ПОСИЛАННЯ>

.section--hero { padding-block: rem(40); }
.hero__title  { color: var(--text); }

@include minw($tablet) {
  .section--hero { padding-block: rem(96); }
}
```

Зареєструй партіал у `src/styles/sections/_index.scss`:
```scss
@forward 'hero';
```
`_index.scss` уже підключений із `main.scss` через `@use 'sections';`. **Партіали Vite не сканує сам** — без `@forward` стилі не потраплять у збірку.

### 5. JS (тільки якщо є інтерактив)
`src/js/sections/hero.js` — експортуй init, що сам перевіряє наявність секції:

```js
export function initHero() {
  const el = document.querySelector('[data-section="hero"]');
  if (!el) return;
  // ... slider / parallax / gsap
}
```
Підключи в `src/js/app.js` (файл уже є, поки порожній):
```js
import { initHero } from '@js/sections/hero.js';
initHero();
```
Бібліотеки (`gsap`, `swiper`, `@fancyapps/ui`, `parallax-js`, `matter-js`) вже у залежностях — імпортуй напряму (див. README теми). Якщо ліба потрібна лише одній секції — вантаж її умовно через `delta_has_section()` у `_assets.php`, а не глобально.

### 6. Білд
Під час роботи тримай запущеним `npm run dev` (watch) — він пересобирає `build/` на кожну зміну ІСНУЮЧИХ файлів; `npm run build` вручну не запускай, бо його перетре наступна інкрементальна збірка.
**Виняток — НОВИЙ entry-файл** (`src/js/pages/*.js` чи `src/styles/pages/*.scss`): запущений watch його не підхоплює (список input фіксується на старті) — **перезапусти watch**. Партіали секцій (`sections/_*.scss`, `js/sections/*.js`) — не entry, їх watch підхоплює через `@forward`/`import`.
Ручний прод-білд — лише для фінального QA. Підключення в темі — через `build/manifest.json`.

## Чого НЕ робити
- ❌ не створювати окремий `page-*.php` під кожну секцію — секції живуть у конструкторі;
- ❌ не хардкодити тексти/посилання в PHP — усе через ACF;
- ❌ не писати inline-стилі та inline-скрипти;
- ❌ не чіпати `vite.config.js` / `vite-config/` — конвенції самодостатні.
