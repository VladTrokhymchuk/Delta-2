---
name: page-constructor
description: >-
  Create and manage typical/landing page templates assembled from ACF Flexible Content
  sections — the page constructor (конструктор типових сторінок). Classic editor, NOT
  Gutenberg. Use for new page templates, "типова сторінка", "шаблон сторінки", page builder,
  assembling/rendering sections, the global render loop, or template hierarchy for pages & CPT.
---

# Page Constructor — конструктор типових сторінок

Архітектура масштабованості: **сторінка = набір секцій**, які менеджер компонує у класичному редакторі через ACF Flexible Content. Жодного Gutenberg. Секції створюються скілом [[section-builder]]; цей скіл — про каркас, що їх збирає.

## Що вже є в темі

| Файл | Роль |
|---|---|
| [functions-parts/_sections.php](../../../functions-parts/_sections.php) | `render_sections()`, `delta_has_section()`, `delta_sections_main_class()` |
| [page.php](../../../page.php) | дефолтний шаблон сторінки — просто викликає `render_sections()` |
| [index.php](../../../index.php) | фолбек |
| `template-parts/sections/` | порожня — сюди лягають частини секцій |
| [functions-parts/parts/classic-editor.php](../../../functions-parts/parts/classic-editor.php) | вимкнений Gutenberg |

Чого ще немає: групи `group_page_builder.json` у `acf-json/` (Flexible Content `page_sections`). Її створюємо разом із першою секцією.

## Базові рішення архітектури

1. **Класичний редактор, не Gutenberg** — фільтри вже стоять у `parts/classic-editor.php`; для CPT додатково `show_in_rest => false`.

2. **Єдине поле-конструктор.** ACF Flexible Content `page_sections` (group `group_page_builder`, acf-json). Location rule: `Post Type == page` АБО потрібні CPT ([[custom-post-types]]). Кожен layout = секція.

3. **Один універсальний рендер-луп.** Не дублюй цикл по шаблонах — усе через `render_sections()`.

## Рендер-луп (єдина точка)

`functions-parts/_sections.php`:

```php
function render_sections($post_id = null) {
  if (!function_exists('have_rows') || !have_rows('page_sections', $post_id)) return;
  while (have_rows('page_sections', $post_id)) {
    the_row();
    $layout = get_row_layout();
    $part = locate_template("template-parts/sections/{$layout}.php");
    if ($part) {
      include $part; // template part читає get_sub_field()
    } elseif (defined('WP_DEBUG') && WP_DEBUG) {
      echo "<!-- section template missing: {$layout} -->";
    }
  }
}
```

Поруч — два хелпери:
- `delta_has_section('hero')` — чи є layout на поточній сторінці (для умовного підключення ассетів, читає сиру мету, не тягне ACF);
- `delta_sections_main_class()` — вішає `main--sections` на `<main>` (сторінка з секціями не має типових відступів). Викликати **до** `get_header()`.

## Шаблони сторінок

**Дефолтний `page.php`** уже вміє все — окремі `page-*.php` майже не потрібні:

```php
delta_sections_main_class();
get_header();
while (have_posts()) : the_post();
  render_sections();
endwhile;
get_footer();
```

Створюй іменований Template (`Template Name:`) тільки коли потрібна логіка поза конструктором (напр. сторінка з кастомним `WP_Query`, форма бронювання). Сторінкові ассети підключай умовно через `manifest.json` + `is_page_template()` у [functions-parts/_assets.php](../../../functions-parts/_assets.php).

## Як додати новий тип «типової сторінки»
1. Створи сторінку в адмінці → додай потрібні секції конструктором.
2. Якщо потрібен унікальний page-entry (окремий JS/SCSS bundle) — додай `src/js/pages/<name>.js` + `src/styles/pages/page-<name>.scss` (авто-підхоплення, README теми) і підключи умовно в `_assets.php`.
3. Нову секцію — через [[section-builder]].

## Масштабованість: куди класти PHP
`functions.php` лише підключає частини:
```
functions.php                              // тільки include_once
functions-parts/_assets.php                // ассети через build/manifest.json
functions-parts/_acf.php                   // acf-json save/load point ([[acf-fields]])
functions-parts/_sections.php              // render_sections() та хелпери
functions-parts/_post-types-registration.php  // CPT ([[custom-post-types]])
functions-parts/_taxonomies-registration.php  // таксономії
functions-parts/_hooks.php                 // Options page, CF7, інтеграції
functions-parts/_seo-ai.php                // robots + llms.txt ([[seo-for-ai]])
functions-parts/_ajax.php                  // реєстр AJAX-обробників
functions-parts/parts/*.php                // дрібні налаштування WP
functions-parts/modules/*/init.php         // самодостатні модулі
template-parts/sections/*.php              // секції конструктора
```

## Чого НЕ робити
- ❌ Gutenberg-блоки;
- ❌ копіпаст рендер-лупа по кількох шаблонах — тільки `render_sections()`;
- ❌ верстка контенту прямо в `page.php` замість секцій;
- ❌ «магічні» page-id у коді — прив'язуйся до template / CPT / ACF.
