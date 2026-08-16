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

# --- Карти -------------------------------------------------------------------

/**
 * Дістає адресу вбудованої карти Google із того, що вставили в поле.
 *
 * Менеджер копіює з «Поділитися → Вбудувати карту» цілий <iframe>. Виводити
 * його як є не можна з двох причин: у коді Google зашиті width/height 600×450,
 * які ламають сітку, і це сира HTML з поля — тобто вектор для довільної
 * розмітки. Тому беремо ЛИШЕ src, перевіряємо, що це справді google-мапа,
 * а сам <iframe> малюємо своїми атрибутами.
 *
 * @param string $embed Код <iframe> або гола адреса.
 * @return string Безпечний URL або '' — якщо це не карта Google.
 */
function delta_gmap_src($embed) {
	$embed = trim((string) $embed);
	if ($embed === '') return '';

	// Вставили цілий <iframe> — витягуємо src; вставили саму адресу — беремо як є.
	$url = preg_match('#\ssrc\s*=\s*["\']([^"\']+)["\']#i', $embed, $m)
		? $m[1]
		: $embed;

	$url = esc_url_raw(html_entity_decode($url, ENT_QUOTES));
	if (!$url) return '';

	$host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
	$path = (string) wp_parse_url($url, PHP_URL_PATH);

	// Хост зіставляємо цілком: інакше `google.evil.com` пройшов би перевірку
	// «починається з google». {1,2} — це і google.com, і google.com.ua.
	if (!preg_match('#^(www\.|maps\.)?google(\.[a-z]{2,3}){1,2}$#', $host)) return '';
	if (strpos($path, '/maps/embed') === false) return '';

	return $url;
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
