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
    // --- Шаблон: розкоментуй і заміни `room` на потрібну сутність -----------
    /*
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
    */
}
