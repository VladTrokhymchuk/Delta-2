<?php
/**
 * Section: Reviews — відгуки гостей.
 * Поля з ACF Flexible Content layout "reviews"; самі відгуки — CPT `review`.
 *
 * Секція додає розмітку Review + AggregateRating, тож відгуки працюють не
 * лише як контент, а і як сигнал для пошуку та AI-відповідей.
 *
 * @see functions-parts/parts/reviews.php
 * @see src/styles/sections/_reviews.scss
 */

if (!defined('ABSPATH')) exit;

$overline = get_sub_field('reviews_overline');
$title    = get_sub_field('reviews_title');
$subtitle = get_sub_field('reviews_subtitle');
$source   = get_sub_field('reviews_source') ?: 'auto';

if ($source === 'manual') {
    $ids = (array) get_sub_field('reviews_items');
} else {
    $limit = (int) get_sub_field('reviews_limit');
    $ids   = get_posts(array(
        'post_type'      => 'review',
        'post_status'    => 'publish',
        'posts_per_page' => $limit > 0 ? $limit : -1,
        'orderby'        => array('menu_order' => 'ASC', 'date' => 'DESC'),
        'fields'         => 'ids',
    ));
}

$ids = array_filter(array_map('intval', $ids));
if (!$ids && !$title) return;
?>
<section class="section section--reviews" data-section="reviews">
	<div class="container">

		<?php if ($overline || $title || $subtitle) : ?>
			<div class="reviews__head">
				<?php if ($overline) : ?>
					<p class="overline reviews__overline"><?= esc_html($overline); ?></p>
				<?php endif; ?>

				<?php if ($title) : ?>
					<h2 class="reviews__title"><?= esc_html($title); ?></h2>
				<?php endif; ?>

				<?php if ($subtitle) : ?>
					<p class="reviews__subtitle"><?= esc_html($subtitle); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ($ids) : ?>
			<ul class="reviews__grid">
				<?php foreach ($ids as $review_id) :
					$text = delta_review_text($review_id);
					if ($text === '') continue;

					$rating = delta_review_rating($review_id);
					$ts     = delta_review_timestamp($review_id);
					$src    = function_exists('get_field') ? trim((string) get_field('review_source', $review_id)) : '';
					?>
					<li class="reviews__item">
						<article class="review-card">
							<div class="review-card__head">
								<p class="review-card__author"><?= esc_html(get_the_title($review_id)); ?></p>
								<?= delta_review_stars($rating); // phpcs:ignore WordPress.Security.EscapeOutput — власні SVG ?>
							</div>

							<blockquote class="review-card__text">
								<p><?= esc_html($text); ?></p>
							</blockquote>

							<p class="review-card__meta">
								<time datetime="<?= esc_attr(wp_date('Y-m-d', $ts)); ?>">
									<?= esc_html(wp_date('d F, Y', $ts)); ?>
								</time>
								<?php if ($src) : ?>
									<span class="review-card__source"><?= esc_html($src); ?></span>
								<?php endif; ?>
							</p>
						</article>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php delta_reviews_schema($ids); ?>
		<?php endif; ?>

	</div>
</section>
