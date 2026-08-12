<?php
/**
 * Сторінка — рендериться конструктором (ACF Flexible Content `page_sections`).
 * Окремі page-*.php потрібні лише там, де є логіка поза конструктором.
 *
 * @see .claude/skills/page-constructor
 */

if (!defined('ABSPATH')) exit;

delta_sections_main_class(); // прибирає типові відступи <main>, якщо є секції

get_header();

while (have_posts()) : the_post();
	render_sections();
endwhile;

get_footer();
