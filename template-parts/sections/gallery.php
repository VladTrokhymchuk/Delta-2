<?php
/**
 * Section: Gallery — сітка фото з чергуванням розмірів.
 * Поля з ACF Flexible Content layout "gallery".
 *
 * Розкладку задає CSS за позицією елемента (nth-child), тож редактор просто
 * додає зображення в потрібному порядку — нічого обирати не треба.
 *
 * @see src/styles/sections/_gallery.scss
 */

if (!defined('ABSPATH')) exit;

$overline = get_sub_field('gallery_overline');
$title    = get_sub_field('gallery_title');
$subtitle = get_sub_field('gallery_subtitle');
$images   = get_sub_field('gallery_images'); // return_format = id

if (!$images && !$title) return;
?>
<section class="section section--gallery" data-section="gallery">
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
			<ul class="gallery__grid">
				<?php foreach ($images as $image_id) : ?>
					<li class="gallery__item">
						<?= wp_get_attachment_image($image_id, 'large', false, array(
							'class'    => 'gallery__img',
							'loading'  => 'lazy',
							'decoding' => 'async',
							'sizes'    => '(min-width: 992px) 50vw, 100vw',
						)); ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

	</div>
</section>
