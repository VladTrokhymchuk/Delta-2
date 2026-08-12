<?php
/**
 * Одиночний запис — спільний фолбек для всіх CPT.
 * Мінімальний каркас: заголовок + обкладинка + контент. Під конкретний CPT
 * створюй single-{cpt}.php, коли з'явиться макет.
 */

if (!defined('ABSPATH')) exit;

get_header();

while (have_posts()) : the_post(); ?>
	<article class="single single--<?= esc_attr(get_post_type()); ?>">
		<div class="container">
			<h1 class="single__title"><?php the_title(); ?></h1>

			<?php if (has_post_thumbnail()) : ?>
				<div class="single__media"><?php the_post_thumbnail('large'); ?></div>
			<?php endif; ?>

			<div class="single__content">
				<?php the_content(); ?>
			</div>
		</div>
	</article>
<?php
endwhile;

get_footer();
