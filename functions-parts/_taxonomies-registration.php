<?php
/*
 * Реєстрація кастомних таксономій (по одній на CPT за потреби).
 * Іменування: {cpt}_cat / {cpt}_tag (або змістовне, як `room_type`).
 * Після реєстрації скинь permalinks.
 *
 * @see .claude/skills/custom-post-types
 */

if (!defined('ABSPATH')) exit;

add_action('init', 'create_taxonomy');
function create_taxonomy()
{
    // --- Шаблон: розкоментуй під потрібний CPT ------------------------------
    /*
    register_taxonomy('room_type', array('room'), array(
        'labels' => array(
            'name'          => 'Типи номерів',
            'singular_name' => 'Тип номера',
            'all_items'     => 'Усі типи',
            'edit_item'     => 'Редагувати тип',
            'add_new_item'  => 'Додати тип',
            'menu_name'     => 'Типи номерів',
        ),
        'public'            => true,
        'hierarchical'      => true,      // true = як категорії; false = як теги
        'show_admin_column' => true,
        'show_in_rest'      => false,
        'rewrite'           => array('slug' => 'room-type', 'with_front' => false),
    ));
    */
}
