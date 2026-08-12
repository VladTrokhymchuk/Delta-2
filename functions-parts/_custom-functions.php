<?php
/**
 * Дрібні хелпери теми.
 */

if (!defined('ABSPATH')) exit;

# --- ACF Options -------------------------------------------------------------

/**
 * Значення поля зі сторінки «Налаштування теми» з фолбеком.
 * Один хелпер на всі глобальні поля (шапка, підвал, контакти).
 *
 * @see acf-json/group_theme_settings.json
 */
function delta_opt($field, $default = '') {
	if (!function_exists('get_field')) return $default;

	$value = get_field($field, 'options');

	return ($value === null || $value === '' || $value === false) ? $default : $value;
}

# --- Меню --------------------------------------------------------------------

/**
 * Повідомлення на місці меню, якщо локація не призначена.
 *
 * Показуємо ЛИШЕ тим, хто може це полагодити. Фейкових пунктів не малюємо:
 * посилання на «#» у продакшені виглядають як робоче меню й потрапляють
 * у видачу та до відвідувачів.
 */
function delta_menu_missing_notice($class = '') {
	if (!current_user_can('edit_theme_options')) return;

	printf(
		'<p class="%s">%s <a href="%s">%s</a></p>',
		esc_attr($class),
		esc_html__('Меню не призначене:', 'delta'),
		esc_url(admin_url('nav-menus.php?action=locations')),
		esc_html__('налаштувати', 'delta')
	);
}

# --- Пристрій (Mobile_Detect) ------------------------------------------------
function isMobile() {
  $detect = new Mobile_Detect;
  return $detect->isMobile();
}
function isTablet() {
  $detect = new Mobile_Detect;
  return $detect->isTablet();
}
function isDesktop() {
  return (!isTablet() && !isMobile());
}

# --- Поточний шаблон ---------------------------------------------------------
function get_current_template() {
  global $template;
  return basename($template);
}

# --- Умова «сторінка або її дочірні» ----------------------------------------
function is_tree($pid) {
	global $post;
	$page = get_page_by_path($pid);
	if (!$page) return false;

	return is_page() && ($post->post_parent == $page->ID || is_page($pid));
}

# --- Редактор: не вирізати <span> -------------------------------------------
add_filter('tiny_mce_before_init', 'my_adds_alls_elements', 20);
function my_adds_alls_elements($init) {
  if (current_user_can('unfiltered_html')) {
    $init['extended_valid_elements'] = 'span[*]';
  }
  return $init;
}
