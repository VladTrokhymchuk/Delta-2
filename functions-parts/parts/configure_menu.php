<?php
/**
 * Меню адмінки, підтримка тем і локації навігації.
 */

if (!defined('ABSPATH')) exit;

# Видалення пунктів меню адмінки (правило проєкту — лише CPT, без дефолтних Записів/Коментарів)
add_action('admin_menu', 'remove_menus');
function remove_menus(){
	// remove_menu_page('index.php');                  //Консоль
	remove_menu_page('edit.php');                   //Записи
	// remove_menu_page('upload.php');                 //Медіафайли
	// remove_menu_page('edit.php?post_type=page');    //Сторінки
	remove_menu_page('edit-comments.php');          //Коментарі
	// remove_menu_page('themes.php');                 //Зовнішній вигляд
	// remove_menu_page('plugins.php');                //Плагіни
	// remove_menu_page('users.php');                  //Користувачі
	// remove_menu_page('tools.php');                  //Інструменти
	// remove_menu_page('options-general.php');        //Налаштування
}

add_theme_support('menus');

add_action( 'after_setup_theme', function(){
	# Зображення запису («Featured image») — без цього блок не показується в редакторі,
	# навіть якщо CPT має supports => 'thumbnail'.
	add_theme_support( 'post-thumbnails' );

	# Тег <title> — без нього WP/Yoast не виводять заголовок сторінки взагалі
	# (у header.php <title> немає). Критично для SEO: title у видачі, вкладці, OG.
	add_theme_support( 'title-tag' );

	register_nav_menus( [
		'header_menu' => 'Меню в шапці',
		'footer_menu' => 'Меню в підвалі',
	] );
} );

# Додаткові розміри зображень — за макетом.
// add_image_size( 'room-card', 640, 480, true );
