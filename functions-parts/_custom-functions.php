<?php
/**
 * Дрібні хелпери теми.
 */

if (!defined('ABSPATH')) exit;

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
