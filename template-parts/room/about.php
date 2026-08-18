<?php
/**
 * Сторінка номера: опис + картка «Характеристики номера».
 *
 * Не секція конструктора: блок читає дані конкретного номера (текст із
 * редактора + репітер `room_features`), тож на звичайній сторінці йому
 * нема звідки взятись — звідси окремий каталог template-parts/room/.
 *
 * @see functions-parts/parts/rooms.php (delta_room_features)
 * @see src/styles/sections/_room-about.scss
 */

if (!defined('ABSPATH')) exit;

$room_id  = (int) ($args['room'] ?? get_the_ID());
$features = delta_room_features($room_id);

// Увесь блок — головний редактор: заголовок, абзаци, списки. the_content()
// працює з глобальним $post, тому беремо його явно від ID: блок може
// викликатись і поза лупом.
$content = apply_filters('the_content', get_post_field('post_content', $room_id));

if (!$content && !$features) return;

$specs_title = delta_opt('room_page_specs_title', __('Характеристики номера', 'delta'));
?>
<section class="section section--room-about" data-section="room-about">
	<div class="container room-about__inner">

		<div class="room-about__content">
			<?php // Заголовок блоку — теж із редактора (перший <h2> у тексті),
			      // окремого поля під нього немає. ?>
			<?php if ($content) : ?>
				<div class="text__content room-about__text">
					<?= wp_kses_post($content); ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ($features) : ?>
			<?php // <aside>, бо це супровідна до опису довідка, а не його частина. ?>
			<aside class="room-about__specs" aria-label="<?= esc_attr($specs_title); ?>">
				<h3 class="room-about__specs-title"><?= esc_html($specs_title); ?></h3>

				<dl class="room-about__list">
					<?php foreach ($features as $feature) :
						$icon = $feature['icon'] ? delta_icon($feature['icon']) : '';
						?>
						<div class="room-about__row">
							<?php if ($icon) : ?>
								<span class="room-about__icon">
									<?= $icon; // phpcs:ignore WordPress.Security.EscapeOutput — власний SVG теми ?>
								</span>
							<?php endif; ?>

							<?php // Назва та значення — пара <dt>/<dd>: це саме довідник
							      // «характеристика → значення», а не просто два рядки. ?>
							<div class="room-about__pair">
								<dt class="room-about__label"><?= esc_html($feature['label']); ?></dt>
								<dd class="room-about__value"><?= esc_html($feature['value']); ?></dd>
							</div>
						</div>
					<?php endforeach; ?>
				</dl>
			</aside>
		<?php endif; ?>

	</div>
</section>
