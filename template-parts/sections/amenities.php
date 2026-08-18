<?php
/**
 * Section: Amenities — зручності та сервіси (іконка + підпис).
 * Поля з ACF Flexible Content layout "amenities" (acf-json/group_page_builder.json).
 *
 * Іконки інлайняться з build/img/icons/ через delta_icon() — див.
 * functions-parts/parts/icons.php.
 *
 * Два режими роботи:
 *   • конструктор — поля рядка Flexible Content (get_sub_field);
 *   • сторінка номера — зручності з поля `room_amenities` приходять у $args,
 *     а модифікатор «--amenities-room» дає розкладку макета (шапка ліворуч,
 *     чотири колонки, іконка поруч із підписом).
 *
 * @see src/styles/sections/_amenities.scss
 */

if (!defined('ABSPATH')) exit;

$args = (isset($args) && is_array($args)) ? $args : array();

if ($args) {
	$overline = $args['overline'] ?? '';
	$title    = $args['title']    ?? '';
	$subtitle = $args['subtitle'] ?? '';
	$items    = (array) ($args['items'] ?? array());
} else {
	$overline = get_sub_field('amenities_overline');
	$title    = get_sub_field('amenities_title');
	$subtitle = get_sub_field('amenities_subtitle');
	$items    = get_sub_field('amenities_items');
}

$modifier = $args['modifier'] ?? '';

if (!$title && !$items) return;
?>
<section class="<?= esc_attr(trim('section section--amenities ' . $modifier)); ?>" data-section="amenities"<?= delta_section_id_attr('amenities'); ?>>
	<div class="container">

		<?php if ($overline || $title || $subtitle) : ?>
			<div class="amenities__head">
				<?php if ($overline) : ?>
					<p class="overline amenities__overline"><?= esc_html($overline); ?></p>
				<?php endif; ?>

				<?php if ($title) : ?>
					<h2 class="amenities__title"><?= esc_html($title); ?></h2>
				<?php endif; ?>

				<?php if ($subtitle) : ?>
					<p class="amenities__subtitle"><?= esc_html($subtitle); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ($items) : ?>
			<ul class="amenities__list">
				<?php foreach ($items as $item) :
					$icon  = $item['icon'] ?? '';
					$label = $item['title'] ?? '';
					if (!$label && !$icon) continue;
					?>
					<li class="amenities__item">
						<?php if ($svg = delta_icon($icon)) : ?>
							<span class="amenities__icon">
								<?= $svg; // phpcs:ignore WordPress.Security.EscapeOutput — власний SVG теми ?>
							</span>
						<?php endif; ?>

						<?php if ($label) : ?>
							<span class="amenities__label"><?= esc_html($label); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

	</div>
</section>
