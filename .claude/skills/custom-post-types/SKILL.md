---
name: custom-post-types
description: >-
  Register custom post types and taxonomies. The project uses ONLY custom post types &
  taxonomies — never default Posts/Categories/Tags. Use for "кастомний пост тайп", CPT,
  "таксономія", register_post_type, register_taxonomy, new content entity, archive/single
  templates for a CPT, or content modeling.
---

# Custom Post Types & Taxonomies

**Правило проєкту: лише кастомні пост-тайпи й таксономії.** Дефолтні `post`, `category`, `post_tag` не використовуються — вони вже приховані (`parts/configure_menu.php` прибирає меню «Записи», `parts/classic-editor.php` відв'язує дефолтні таксономії).

Сутності готелю (номери, послуги, спецпропозиції, відгуки, новини) реєструються під час верстки — коли з макета зрозуміла модель контенту.

## Де реєструвати
- CPT — [functions-parts/_post-types-registration.php](../../../functions-parts/_post-types-registration.php) (функція `init_post_types()` на хуку `init`, зараз містить закоментований шаблон);
- таксономії — [functions-parts/_taxonomies-registration.php](../../../functions-parts/_taxonomies-registration.php) (`create_taxonomy()`).

## Реєстрація CPT

```php
add_action('init', 'init_post_types');
function init_post_types() {
  register_post_type('room', array(
    'labels' => array(
      'name'          => 'Номери',
      'singular_name' => 'Номер',
      'add_new_item'  => 'Додати номер',
      'edit_item'     => 'Редагувати номер',
      'menu_name'     => 'Номери',
    ),
    'public'        => true,
    'has_archive'   => true,          // /rooms/ — потрібен archive-room.php
    'show_in_menu'  => true,
    'show_in_rest'  => false,         // класичний редактор, не Gutenberg
    'menu_position' => 5,
    'menu_icon'     => 'dashicons-building',
    'hierarchical'  => false,
    'supports'      => array('title', 'editor', 'thumbnail', 'excerpt'),
    'rewrite'       => array('slug' => 'rooms', 'with_front' => false),
  ));
}
```

Ключові рішення:
- `show_in_rest => false` — щоб лишався класичний редактор (узгоджено з [[page-constructor]]). Якщо CPT теж збирається конструктором — `supports` без `editor`, а location rule ACF `Post Type == room`.
- `has_archive => true` лише якщо є сторінка-список; інакше `false` + звичайна WP-сторінка з власним шаблоном як індекс.
- slug у `rewrite` — стабільний, lowercase, множина для архіву.
- після реєстрації **скинь permalinks** (Налаштування → Постійні посилання → Зберегти), інакше 404.

## Реєстрація таксономії

```php
add_action('init', 'create_taxonomy');
function create_taxonomy() {
  register_taxonomy('room_type', array('room'), array(
    'labels' => array(
      'name'          => 'Типи номерів',
      'singular_name' => 'Тип номера',
      'menu_name'     => 'Типи номерів',
    ),
    'public'            => true,
    'hierarchical'      => true,        // true = як категорії; false = як теги
    'show_admin_column' => true,
    'show_in_rest'      => false,
    'rewrite'           => array('slug' => 'room-type', 'with_front' => false),
  ));
}
```

## Шаблони під CPT (template hierarchy)
- `single-{cpt}.php` — одиночний запис (напр. `single-room.php`). Може теж рендерити секції через `render_sections()`. Фолбек — загальний [single.php](../../../single.php).
- `archive-{cpt}.php` — список/архів; кастомний `WP_Query` або стандартний луп + пагінація.
- `taxonomy-{taxonomy}.php` — сторінка терміна.
- ассети — окремий page-entry за потреби (`src/js/pages/room.js`), підключення умовно через `manifest.json` у `_assets.php`.

## Іменування
- CPT slug: однина, lowercase, латиниця (`room`, `offer`, `service`, `review`).
- taxonomy slug: `{cpt}_cat` / `{cpt}_tag` або змістовне (`room_type`).
- НЕ використовуй зарезервовані слова (`post`, `page`, `type`, `tag`, `category`, `author`, `attachment`…).
- ACF-поля сутності — окрема група з location `Post Type == {cpt}` (див. [[acf-fields]]).

## Не забудь після створення CPT
- додати його в карту для LLM: фільтр `delta_llms_post_types` (див. [[seo-for-ai]]);
- увімкнути тип у Yoast (Search Appearance) і задати шаблони title/description ([[seo-optimization]]).

## Чого НЕ робити
- ❌ дефолтні Posts/Categories під реальний контент;
- ❌ `show_in_rest => true` (поверне Gutenberg);
- ❌ забути скинути permalinks;
- ❌ реєструвати CPT поза хуком `init`.
