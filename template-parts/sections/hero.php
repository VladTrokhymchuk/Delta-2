<?php
/**
 * Section: Hero — перший екран.
 * Поля з ACF Flexible Content layout "hero" (acf-json/group_page_builder.json).
 *
 * Фон — <img>, а не CSS background: так браузер отримує srcset/sizes і тягне
 * розмір під екран, а не 2560px на телефон. Це LCP-елемент, тож без lazy,
 * із fetchpriority="high".
 *
 * Три режими роботи:
 *   • конструктор — поля рядка Flexible Content (get_sub_field);
 *   • архів номерів /rooms/ — значення приходять у $args із
 *     «Налаштування теми» (archive-room.php). Розмітка одна на обидва,
 *     щоб перший екран не розповзався у двох копіях;
 *   • сторінка номера — те саме через $args (single-room.php).
 *
 * @see src/styles/sections/_hero.scss
 */

if (!defined('ABSPATH')) exit;

$args = (isset($args) && is_array($args)) ? $args : array();

if ($args) {
	$image    = $args['image']    ?? null;
	$overline = $args['overline'] ?? '';
	$title    = $args['title']    ?? '';
	$subtitle = $args['subtitle'] ?? '';
	$buttons  = $args['buttons']  ?? array();
} else {
	$image    = get_sub_field('hero_image'); // return_format = id
	$overline = get_sub_field('hero_overline');
	$title    = get_sub_field('hero_title');
	$subtitle = get_sub_field('hero_subtitle');
	$buttons  = get_sub_field('hero_buttons');
}

// Модифікатор вигляду («--compact» для внутрішніх сторінок).
$modifier = $args['modifier'] ?? '';

if (!$title && !$image) return; // порожню секцію не рендеримо
?>
<section class="<?= esc_attr(trim('section section--hero ' . $modifier)); ?>" data-section="hero"<?= delta_section_id_attr('hero'); ?>>

	<?php if ($image) : ?>
		<div class="hero__media">
			<?= wp_get_attachment_image($image, 'full', false, array(
				'class'         => 'hero__bg',
				'alt'           => '',
				'fetchpriority' => 'high',
				'decoding'      => 'async',
				'sizes'         => '100vw',
			)); ?>
		</div>
	<?php endif; ?>

	<div class="container hero__inner">

		<?php if ($overline) : ?>
			<p class="overline overline--light hero__overline"><?= esc_html($overline); ?></p>
		<?php endif; ?>

		<?php if ($title) : ?>
			<h1 class="hero__title"><?= esc_html($title); ?></h1>
		<?php endif; ?>

		<?php if ($subtitle) : ?>
			<p class="hero__subtitle"><?= nl2br(esc_html($subtitle)); ?></p>
		<?php endif; ?>

		<?php if ($buttons) : ?>
			<div class="hero__actions">
				<?php foreach ($buttons as $btn) :
					$link = !empty($btn['link']) ? $btn['link'] : null;
					if (!$link || empty($link['url'])) continue;

					// «ghost» у hero — це варіант із рамкою поверх фото.
					$key = ($btn['style'] ?? 'gold') === 'ghost' ? 'ghost-light' : 'gold';
					?>
					<a class="<?= esc_attr(delta_button_class($key)); ?>"
					   href="<?= esc_url($link['url']); ?>"
					   <?php if (!empty($link['target'])) : ?>target="<?= esc_attr($link['target']); ?>" rel="noopener"<?php endif; ?>>
						<?= esc_html(!empty($link['title']) ? $link['title'] : __('Детальніше', 'delta')); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</section>
