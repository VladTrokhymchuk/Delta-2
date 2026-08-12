<?php
/**
 * Загальні хуки й інтеграції з плагінами.
 */

if (!defined('ABSPATH')) exit;

/**
 * Прибрати автоматичні <p>/<br>, які CF7 вставляє у форму.
 *
 * УВАГА до регулярок: `<p[^>]*>` без перевірки наступного символу вирізає не лише
 * <p>, а й БУДЬ-ЯКИЙ тег, що починається на «p» — <path> усередині інлайнового SVG,
 * <picture>, <pre>, <progress>. Тому далі — lookahead на пробіл/закриття.
 */
add_filter('wpcf7_form_elements', 'remove_p_and_br_tags_from_cf7_form');
function remove_p_and_br_tags_from_cf7_form($content) {
    $content = preg_replace('#<p(?=[\s>])[^>]*>#i', '', $content);
    $content = preg_replace('#</p\s*>#i', '', $content);
    $content = preg_replace('#<br(?=[\s>/])[^>]*>#i', '', $content);
    return $content;
}

/**
 * Сторінка налаштувань теми (ACF Pro Options) — глобальні дані:
 * логотип, контакти, соцмережі, підвал.
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	acf_add_options_page( array(
		'page_title' => 'Налаштування теми',
		'menu_title' => 'Налаштування теми',
		'menu_slug'  => 'theme-settings',
		'capability' => 'edit_posts',
		'redirect'   => false,
	) );
}

/**
 * ACF Google Map — ключ API (заповнити, коли на макеті з'явиться карта).
 */
// add_filter('acf/fields/google_map/api', function ($api) {
//     $api['key'] = '';
//     return $api;
// });
