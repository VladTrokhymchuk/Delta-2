<?php
/**
 * Стилізація адмін-панелі.
 */

if (!defined('ABSPATH')) exit;

# CSS адмінки — правити у wp-admin.css (у корені теми, поза збіркою Vite).
add_action('admin_enqueue_scripts', 'my_admin_css', 99);
function my_admin_css(){
	wp_enqueue_style('my-wp-admin', get_template_directory_uri() . '/wp-admin.css');
}

# Текст у підвалі адмінки.
// add_filter('admin_footer_text', function () {
// 	return 'Розробка: <a href="#" target="_blank">Hotel Delta</a>';
// });

# Логотип на сторінці авторизації.
// add_action('login_head', function () {
// 	echo '<style>.login h1 a{background-image:url(' . esc_url(get_template_directory_uri() . '/build/img/logo.svg') . ') !important;background-size:contain !important;width:260px !important;}</style>';
// });
// add_filter('login_headerurl', fn() => home_url());
// add_filter('login_headertext', fn() => get_bloginfo('name'));
