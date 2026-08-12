<?php
/**
 * Хелпери шапки: дані з ACF Options + вивід навігації.
 *
 * Меню редагується в Зовнішній вигляд → Меню, локація `header_menu`.
 * Поки меню не призначене, виводиться плейсхолдер із макета — щоб шапка
 * не виглядала зламаною на свіжій інсталяції. Призначив меню — плейсхолдер зник.
 */

if (!defined('ABSPATH')) exit;

/**
 * Значення поля шапки з ACF Options із фолбеком на дефолт із макета.
 */
function delta_header_opt($field, $default = '') {
    if (!function_exists('get_field')) return $default;
    $value = get_field($field, 'options');
    return ($value === null || $value === '' || $value === false) ? $default : $value;
}

/**
 * Навігація шапки. Без призначеного меню — плейсхолдер із макета.
 */
function delta_header_nav() {
    if (has_nav_menu('header_menu')) {
        wp_nav_menu(array(
            'theme_location' => 'header_menu',
            'container'      => false,
            'menu_class'     => 'header__menu',
            'fallback_cb'    => false,
            'depth'          => 2,
        ));
        return;
    }

    // --- Плейсхолдер (меню ще не створене) ----------------------------------
    $items = array('Про нас', 'Номери', 'Послуги', 'Ресторан', 'Галерея', 'Контакти');

    echo '<ul class="header__menu header__menu--placeholder">';
    foreach ($items as $item) {
        echo '<li class="menu-item"><a href="#">' . esc_html($item) . '</a></li>';
    }
    echo '</ul>';

    if (current_user_can('edit_theme_options')) {
        printf(
            '<p class="header__menu-hint">%s <a href="%s">%s</a></p>',
            esc_html__('Меню ще не призначене:', 'delta'),
            esc_url(admin_url('nav-menus.php')),
            esc_html__('налаштувати', 'delta')
        );
    }
}
