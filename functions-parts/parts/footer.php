<?php
/**
 * Хелпери підвалу.
 *
 * Увесь контент, окрім колонки «Навігація», редагується в ACF Options
 * «Налаштування теми» → вкладка «Підвал». Меню — Зовнішній вигляд → Меню,
 * локація `footer_menu`.
 *
 * @see acf-json/group_theme_settings.json
 * @see functions-parts/_custom-functions.php (delta_opt, delta_menu_missing_notice)
 */

if (!defined('ABSPATH')) exit;

/**
 * Копірайт: поле з ACF (підтримує %year%) або дефолт «© {рік} {сайт}».
 */
function delta_footer_copyright() {
    $year   = date_i18n('Y');
    $custom = delta_opt('footer_copyright');

    if ($custom) {
        return str_replace('%year%', $year, $custom);
    }

    return sprintf(
        /* translators: 1: рік, 2: назва сайту */
        __('© %1$s %2$s. Усі права захищено.', 'delta'),
        $year,
        get_bloginfo('name')
    );
}

/**
 * Навігація підвалу.
 *
 * Локація не призначена — редактор бачить підказку, відвідувач нічого.
 */
function delta_footer_nav() {
    if (!has_nav_menu('footer_menu')) {
        delta_menu_missing_notice('footer__menu-hint');
        return;
    }

    // menu_id явно — щоб id не збирався зі слага кириличної назви меню
    // (див. коментар у parts/header.php).
    wp_nav_menu(array(
        'theme_location' => 'footer_menu',
        'container'      => false,
        'menu_id'        => 'footer-menu',
        'menu_class'     => 'footer__menu',
        'fallback_cb'    => false,
        'depth'          => 1,
    ));
}

/**
 * Соцмережі з ACF Options (repeater footer_socials: network + url).
 * Немає заповнених рядків — колонка не виводиться взагалі.
 */
function delta_footer_socials() {
    if (!function_exists('get_field')) return array();

    $rows = get_field('footer_socials', 'options');
    if (!is_array($rows)) return array();

    $out = array();
    foreach ($rows as $row) {
        $label = isset($row['network']) ? trim((string) $row['network']) : '';
        $url   = isset($row['url']) ? trim((string) $row['url']) : '';
        if ($label === '' || $url === '') continue;

        $out[] = array('label' => $label, 'url' => $url);
    }

    return $out;
}
