<?php
/**
 * Section: Media + Text — універсальний блок «текст поруч із зображенням».
 * Поля з ACF Flexible Content layout "media-text".
 *
 * Секція навмисно не прив'язана до теми контенту: ресторан, сауна, СПА тощо.
 * Чергування сторін задається полем «Зображення розташувати».
 *
 * @see src/styles/sections/_media-text.scss
 */

if (!defined('ABSPATH')) exit;

$image    = get_sub_field('media_text_image'); // return_format = id
$position = get_sub_field('media_text_position') ?: 'right';
$overline = get_sub_field('media_text_overline');
$title    = get_sub_field('media_text_title');
$lead     = get_sub_field('media_text_lead');
$note     = get_sub_field('media_text_note');
$buttons  = get_sub_field('media_text_buttons');

if (!$title && !$lead && !$image) return;

$modifier = $position === 'left' ? ' section--media-text-reverse' : '';
?>
<section class="section section--media-text<?= esc_attr($modifier); ?>" data-section="media-text">
	<div class="container media-text__inner">

		<div class="media-text__content">
			<?php if ($overline) : ?>
				<p class="overline media-text__overline"><?= esc_html($overline); ?></p>
			<?php endif; ?>

			<?php if ($title) : ?>
				<h2 class="media-text__title"><?= esc_html($title); ?></h2>
			<?php endif; ?>

			<?php if ($lead) : ?>
				<p class="media-text__lead"><?= nl2br(esc_html($lead)); ?></p>
			<?php endif; ?>

			<?php if ($note) : ?>
				<p class="media-text__note"><?= nl2br(esc_html($note)); ?></p>
			<?php endif; ?>

			<?php if ($buttons) : ?>
				<div class="media-text__actions">
					<?php foreach ($buttons as $btn) :
						$link = !empty($btn['link']) ? $btn['link'] : null;
						if (!$link || empty($link['url'])) continue;
						?>
						<a class="<?= esc_attr(delta_button_class($btn['style'] ?? 'primary')); ?>"
						   href="<?= esc_url($link['url']); ?>"
						   <?php if (!empty($link['target'])) : ?>target="<?= esc_attr($link['target']); ?>" rel="noopener"<?php endif; ?>>
							<?= esc_html(!empty($link['title']) ? $link['title'] : __('Детальніше', 'delta')); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ($image) : ?>
			<div class="media-text__media">
				<?= wp_get_attachment_image($image, 'full', false, array(
					'class'    => 'media-text__img',
					'loading'  => 'lazy',
					'decoding' => 'async',
					'sizes'    => '(min-width: 992px) 60vw, 100vw',
				)); ?>
			</div>
		<?php endif; ?>

	</div>
</section>
