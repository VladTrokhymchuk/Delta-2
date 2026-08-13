<?php
/*
 * Реєстрація кастомних пост-тайпів.
 * Правило проєкту: лише CPT, ніяких дефолтних Записів.
 * show_in_rest => false (класичний редактор). Після реєстрації скинь permalinks.
 *
 * Поки порожньо — сутності готелю (номери, послуги, пропозиції…) додаємо
 * за макетом. Нижче — робочий шаблон реєстрації.
 *
 * @see .claude/skills/custom-post-types
 */

if (!defined('ABSPATH')) exit;

add_action('init', 'init_post_types');
function init_post_types()
{
    // --- Номери -------------------------------------------------------------
    // Стара тема тримала номери в CPT `news` зі слагом /news-post/ — назва
    // не описувала сутність. Тут вони називаються своїм іменем; редіректи
    // зі старих URL — у functions-parts/parts/redirects.php.
    register_post_type('room', array(
        'labels' => array(
            'name'               => 'Номери',
            'singular_name'      => 'Номер',
            'add_new'            => 'Додати номер',
            'add_new_item'       => 'Додати номер',
            'edit_item'          => 'Редагувати номер',
            'new_item'           => 'Новий номер',
            'view_item'          => 'Переглянути номер',
            'search_items'       => 'Знайти номер',
            'not_found'          => 'Номерів не знайдено',
            'not_found_in_trash' => 'У кошику порожньо',
            'menu_name'          => 'Номери',
        ),
        'public'        => true,
        'has_archive'   => true,   // /rooms/ — сторінка-список
        'show_in_menu'  => true,
        'show_in_rest'  => false,  // класичний редактор, не Gutenberg
        'menu_position' => 5,
        'menu_icon'     => 'dashicons-building',
        'hierarchical'  => false,
        'supports'      => array('title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'),
        'rewrite'       => array('slug' => 'rooms', 'with_front' => false),
    ));
}
