<?php
/**
 * 404 — сторінку не знайдено.
 * Стилі: src/styles/pages/page-404.scss => manifest pages/page-404 (умовно у _assets.php).
 */

if (!defined('ABSPATH')) exit;

get_header(); ?>

<section class="page-404">
	<div class="container page-404__inner">
		<p class="page-404__code">404</p>
		<h1 class="page-404__title"><?php esc_html_e('Сторінку не знайдено', 'delta'); ?></h1>
		<a href="<?= esc_url( home_url('/') ); ?>" class="page-404__link bttn">
			<?php esc_html_e('На головну', 'delta'); ?>
		</a>
	</div>
</section>

<?php get_footer();
