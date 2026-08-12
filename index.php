<?php
/**
 * Резервний шаблон (обов'язковий для теми).
 * Сторінки рендеряться через page.php + конструктор; цей файл — фолбек.
 *
 * @see .claude/skills/page-constructor
 */

if (!defined('ABSPATH')) exit;

get_header();

if ( have_posts() ) {
	while ( have_posts() ) : the_post();
		render_sections();
	endwhile;
}

get_footer();
