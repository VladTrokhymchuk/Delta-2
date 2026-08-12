---
name: acf-fields
description: >-
  Work with ACF Pro field groups via acf-json (local JSON sync). Conventions for creating/
  editing field groups, naming, the save/load point, safe get_field/get_sub_field retrieval,
  repeaters and Flexible Content. Use for ACF, acf-json, "поля", field group, флексібл контент,
  repeater, options page, або читання полів у шаблоні.
---

# ACF Pro + acf-json

Усі поля версіонуються як JSON у `acf-json/`. Редагування в адмінці автоматично пише JSON; на іншому середовищі ACF пропонує «Sync». Це дає масштабованість і командну роботу без експорту вручну.

**Стан:** `acf-json/` поки порожня — групи полів створюються під час верстки макета.

## Save/Load point

Уже підключено в [functions-parts/_acf.php](../../../functions-parts/_acf.php):

```php
add_filter('acf/settings/save_json', fn() => get_stylesheet_directory() . '/acf-json');
add_filter('acf/settings/load_json', function ($paths) {
  $paths[0] = get_stylesheet_directory() . '/acf-json';
  return $paths;
});
```
Папка `acf-json/` має існувати і бути writable. Після `git pull` — ACF → Групи полів → **Синхронізувати**.

## Ключові групи
1. **`group_page_builder.json`** — Flexible Content `page_sections` (конструктор, див. [[page-constructor]] / [[section-builder]]). Location: `Post Type == page` (+ потрібні CPT). Створюється першою, коли починається верстка секцій.
2. **Групи під сутності** — окрема група на кожен CPT, location `Post Type == {cpt}` (див. [[custom-post-types]]).
3. **Глобальні дані** (логотип, контакти, соцмережі, підвал) — **Options Page** «Налаштування теми», зареєстрована в [functions-parts/_hooks.php](../../../functions-parts/_hooks.php) (menu_slug `theme-settings`).
   Читати: `get_field('footer_phone', 'options')`.

Каркас теми вже очікує такі поля Options (можна перейменувати, але тоді онови й місця використання):
`header_logo` (header.php), `footer_phone` / `footer_email` / `footer_address` / `footer_socials` (llms.txt у `_seo-ai.php`).

## Вкладки — ЗАВЖДИ вертикальні

Поле типу `tab` у цьому проєкті створюється тільки з `placement: left` (у адмінці — «Розміщення: Ліворуч»). Горизонтальні вкладки згортаються в кашу, щойно їх стає більше чотирьох, а групи тут ростуть.

```json
{
  "key": "field_theme_tab_header",
  "label": "Шапка",
  "name": "",
  "type": "tab",
  "placement": "left",
  "endpoint": 0
}
```
Правило стосується і груп полів CPT, і конструктора сторінок, і Options. Якщо створюєш групу в адмінці — не забудь перемкнути «Розміщення» на «Ліворуч» перед збереженням, інакше в JSON поїде `top`.

## Іменування полів
- snake_case, з префіксом контексту: `hero_title`, `room_price`, `footer_copyright`.
- у Flexible Content `name` layout = slug секції (узгоджено з [[section-builder]]).
- repeater для будь-чого повторюваного; group для логічного блоку полів.
- НЕ перейменовувати `name`/`key` після релізу — це ламає прив'язку даних у БД.

## Читання у шаблонах (безпечно)

```php
// звичайне поле
$title = get_field('hero_title');               // у Flexible: get_sub_field('hero_title')

// зображення (return format = Array)
$img = get_field('image');
if ($img) printf('<img src="%s" alt="%s" width="%d" height="%d" loading="lazy">',
  esc_url($img['url']), esc_attr($img['alt']), (int) $img['width'], (int) $img['height']);

// repeater
if (have_rows('cards')) {
  while (have_rows('cards')) { the_row();
    echo esc_html(get_sub_field('card_title'));
  }
}

// rich text (WYSIWYG) — дозволений HTML
echo wp_kses_post(get_field('description'));
```
Завжди: перевірка на порожнечу + екранування (`esc_html`/`esc_url`/`esc_attr`/`wp_kses_post`). Для зображень — `width`/`height` (проти CLS), `loading="lazy"` (крім first-screen) і реальний `alt` (важливо для [[seo-optimization]]).

## Правка JSON руками (коли без адмінки)
Можна, але обережно: зберігай валідний `key` (`field_...`, `group_...`), `parent`, оновлюй `modified` (unix timestamp). Краще — створити/змінити в адмінці й закомітити згенерований JSON.

## Чого НЕ робити
- ❌ зберігати поля в БД без acf-json (втрата версіонування);
- ❌ дублювати `key` між полями;
- ❌ виводити поля без екранування;
- ❌ хардкодити контент, який має бути полем.
