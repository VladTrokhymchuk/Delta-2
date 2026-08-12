<?php
/**
 * Прибирання зайвого з <head>, глобальних стилів Gutenberg і emoji.
 *
 * @see .claude/skills/performance-optimization
 */

if (!defined('ABSPATH')) exit;

remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'index_rel_link');

add_action('after_setup_theme', function () {
	# Глобальні стилі тем блоків (theme.json) — тема їх не використовує.
	remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
	remove_action('wp_footer', 'wp_enqueue_global_styles', 1);

	# Фільтри render_block, що додають зайвий CSS/розмітку.
	remove_filter('render_block', 'wp_render_duotone_support');
	remove_filter('render_block', 'wp_restore_group_inner_container');
	remove_filter('render_block', 'wp_render_layout_support_flag');
});

# Emoji в TinyMCE.
add_filter('tiny_mce_plugins', 'disable_emojis_tinymce');
function disable_emojis_tinymce($plugins) {
	return is_array($plugins) ? array_diff($plugins, array('wpemoji')) : array();
}

# Стилі, які тема стилізує сама.
add_action('wp_print_styles', 'wps_deregister_styles', 100);
function wps_deregister_styles() {
	wp_deregister_style('contact-form-7');
	wp_deregister_style('wp-block-library');
	wp_deregister_style('wp-block-library-theme');
	wp_deregister_style('wc-block-style');
}
