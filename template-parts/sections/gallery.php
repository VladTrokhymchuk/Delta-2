<?php
/**
 * Section: Gallery — сітка фото з чергуванням розмірів.
 * Поля з ACF Flexible Content layout "gallery".
 *
 * Розкладку задає CSS за позицією елемента (nth-child), тож редактор просто
 * додає зображення в потрібному порядку — нічого обирати не треба.
 *
 * Два режими роботи:
 *   • конструктор — поля рядка Flexible Content (get_sub_field);
 *   • сторінка номера — зображення з поля `room_gallery` приходять у $args,
 *     а модифікатор «--gallery-room» перемикає сітку на розкладку макета
 *     (2 фото у верхньому ряду, 3 у нижньому).
 *
 * @see src/styles/sections/_gallery.scss
 */

if (!defined('ABSPATH')) exit;

$args = (isset($args) && is_array($args)) ? $args : array();

if ($args) {
	$overline = $args['overline'] ?? '';
	$title    = $args['title']    ?? '';
	$subtitle = $args['subtitle'] ?? '';
	$images   = (array) ($args['images'] ?? array());
} else {
	$overline = get_sub_field('gallery_overline');
	$title    = get_sub_field('gallery_title');
	$subtitle = get_sub_field('gallery_subtitle');
	$images   = get_sub_field('gallery_images'); // return_format = id
}

$modifier = $args['modifier'] ?? '';

if (!$images && !$title) return;
?>
<section class="<?= esc_attr(trim('section section--gallery ' . $modifier)); ?>" data-section="gallery"<?= delta_section_id_attr('gallery'); ?>>
	<div class="container">

		<?php if ($overline || $title || $subtitle) : ?>
			<div class="gallery__head">
				<?php if ($overline) : ?>
					<p class="overline gallery__overline"><?= esc_html($overline); ?></p>
				<?php endif; ?>

				<?php if ($title) : ?>
					<h2 class="gallery__title"><?= esc_html($title); ?></h2>
				<?php endif; ?>

				<?php if ($subtitle) : ?>
					<p class="gallery__subtitle"><?= esc_html($subtitle); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ($images) : ?>
			<?php $total = count($images); ?>
			<ul class="gallery__grid" data-gallery>
				<?php foreach (array_values($images) as $i => $image_id) :
					// Посилання веде на повний розмір: його відкриває лайтбокс,
					// а без JS воно так само працює — фото просто відкриється
					// у вкладці. На сторінці лишається 'large'.
					$full = wp_get_attachment_image_url($image_id, 'full');
					if (!$full) continue;

					/* translators: 1: назва сторінки, 2: номер фото */
					$alt = delta_image_alt($image_id, sprintf(__('%1$s — фото %2$d', 'delta'), get_the_title(), $i + 1));
					?>
					<li class="gallery__item">
						<?php // Підпис для скрінрідера: alt у галерейних фото порожній
						      // (вони декоративні в потоці), тож посилання лишилось би
						      // без імені. ?>
						<a class="gallery__link" href="<?= esc_url($full); ?>"
						   aria-label="<?php printf(esc_attr__('Відкрити фото %1$d з %2$d', 'delta'), $i + 1, $total); ?>">
							<?= wp_get_attachment_image($image_id, 'large', false, array(
								'class'    => 'gallery__img',
								'loading'  => 'lazy',
								'decoding' => 'async',
								'sizes'    => '(min-width: 992px) 50vw, 100vw',
								'alt'      => $alt,
							)); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

	</div>
</section>
