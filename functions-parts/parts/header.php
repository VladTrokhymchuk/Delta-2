<?php
/**
 * Хелпери шапки.
 *
 * Контент шапки — ACF Options «Налаштування теми» (читається через delta_opt()).
 * Меню — Зовнішній вигляд → Меню, локація `header_menu`.
 *
 * @see functions-parts/_custom-functions.php (delta_opt, delta_menu_missing_notice)
 */

if (!defined('ABSPATH')) exit;

/**
 * Навігація шапки.
 *
 * Локація не призначена — редактор бачить підказку, відвідувач нічого.
 */
function delta_header_nav() {
    if (!has_nav_menu('header_menu')) {
        delta_menu_missing_notice('header__menu-hint');
        return;
    }

    // menu_id задаємо явно: інакше WP підставляє `menu-{slug}`, а слаг береться
    // з назви меню — назвуть його кирилицею, і в розмітку потрапить
    // id="menu-%d0%b3%d0%be...". Явний id робить markup незалежним від назви.
    wp_nav_menu(array(
        'theme_location' => 'header_menu',
        'container'      => false,
        'menu_id'        => 'header-menu',
        'menu_class'     => 'header__menu',
        'fallback_cb'    => false,
        'depth'          => 2,
    ));
}
