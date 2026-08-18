<?php
/**
 * Section: Rooms — картки номерів.
 * Поля з ACF Flexible Content layout "rooms" (acf-json/group_page_builder.json).
 * Дані карток — CPT `room` (acf-json/group_room.json).
 *
 * Два режими роботи:
 *   • конструктор — поля рядка Flexible Content (get_sub_field);
 *   • сторінка номера («Інші номери готелю») — добірка й підписи приходять
 *     у $args, а $args['variant'] = 'compact' дає картку з макета: замість
 *     бейджа й опису — надзаголовок номера, а під назвою окремий рядок ціни.
 *
 * @see functions-parts/parts/rooms.php
 * @see src/styles/sections/_rooms.scss
 */

if (!defined('ABSPATH')) exit;

$args = (isset($args) && is_array($args)) ? $args : array();

if ($args) {
    $overline = $args['overline'] ?? '';
    $title    = $args['title']    ?? '';
    $subtitle = $args['subtitle'] ?? '';
    $btn_text = $args['button']   ?? __('Детальніше', 'delta');
    $ids      = (array) ($args['ids'] ?? array());
} else {
    $overline = get_sub_field('rooms_overline');
    $title    = get_sub_field('rooms_title');
    $subtitle = get_sub_field('rooms_subtitle');
    $btn_text = get_sub_field('rooms_button') ?: __('Детальніше', 'delta');
    $source   = get_sub_field('rooms_source') ?: 'auto';

    // --- Добірка номерів ----------------------------------------------------
    if ($source === 'manual') {
        $ids = (array) get_sub_field('rooms_items');
    } else {
        $limit = (int) get_sub_field('rooms_limit');
        $ids   = get_posts(array(
            'post_type'      => 'room',
            'post_status'    => 'publish',
            'posts_per_page' => $limit > 0 ? $limit : -1,
            'orderby'        => array('menu_order' => 'ASC', 'date' => 'DESC'),
            'fields'         => 'ids',
        ));
    }
}

$modifier = $args['modifier'] ?? '';
$compact  = ($args['variant'] ?? '') === 'compact';

// Скільки карток у ряду на десктопі. У конструкторі — три (картка ширша й
// має опис), у добірці «Інші номери» макет дає чотири. Проміжок теж різний:
// 4 картки по 302 + 3 × 24 = рівно 1280 контейнера.
$per_view_desktop = (int) ($args['per_view_desktop'] ?? 3);
$gap_desktop      = (int) ($args['gap_desktop'] ?? 32);

$ids = array_filter(array_map('intval', $ids));
if (!$ids && !$title) return;
?>
<section class="<?= esc_attr(trim('section section--rooms ' . $modifier)); ?>" data-section="rooms">
	<div class="container">

		<?php if ($overline || $title || $subtitle) : ?>
			<div class="rooms__head">
				<?php if ($overline) : ?>
					<p class="overline rooms__overline"><?= esc_html($overline); ?></p>
				<?php endif; ?>

				<?php if ($title) : ?>
					<h2 class="rooms__title"><?= esc_html($title); ?></h2>
				<?php endif; ?>

				<?php if ($subtitle) : ?>
					<p class="rooms__subtitle"><?= esc_html($subtitle); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ($ids) : ?>
		<?php // Мобільний фрейм: проміжок 16px, наступна картка визирає ~24px —
		      // без цього краю слайдер не читається як слайдер.
		      //
		      // Звідси 1.07: контент на 390 — це 358px, і картка з макета (358)
		      // разом із проміжком 16 закінчувалась би рівно на краю екрана,
		      // тобто сусідньої не було б видно взагалі. 1.07 дає картку ~334
		      // і 24px наступної. Більше значення = ширший край і вужча картка.
		      //
		      // Планшет і десктоп — на значеннях за замовчуванням (_slider.js). ?>
		<div class="rooms__slider swiper"
		     data-slider
		     data-slider-per-view="1.07"
		     data-slider-gap="16"
		     data-slider-per-view-tablet="2"
		     data-slider-per-view-desktop="<?= esc_attr($per_view_desktop); ?>"
		     data-slider-gap-desktop="<?= esc_attr($gap_desktop); ?>">
			<ul class="rooms__grid swiper-wrapper">
				<?php foreach ($ids as $room_id) :
					$status = $compact ? null : delta_room_status($room_id);

					// У компактній картці ціна стоїть окремим рядком під назвою й
					// набирається двома кеглями, тож приходить парою (макет
					// сторінки номера); у звичайній — одним рядком.
					$parts  = $compact ? delta_room_price_card($room_id) : null;
					$price  = $compact ? '' : delta_room_price($room_id);
					$text   = $compact ? '' : delta_room_excerpt($room_id);
					$label  = $compact ? get_field('room_overline', $room_id) : '';
					$url    = get_permalink($room_id);
					?>
					<li class="rooms__item swiper-slide">
						<article class="room-card<?= $compact ? ' room-card--compact' : ''; ?>">

							<?php if (has_post_thumbnail($room_id)) : ?>
								<div class="room-card__media">
									<?= get_the_post_thumbnail($room_id, 'large', array(
										'class'    => 'room-card__img',
										'loading'  => 'lazy',
										'decoding' => 'async',
										'alt'      => '',
									)); ?>
								</div>
							<?php endif; ?>

							<div class="room-card__body">

								<?php // У компактній картці рядок виводимо завжди, навіть порожній:
								      // надзаголовок є не в кожного номера, і без цього назви
								      // в ряду стояли б на різній висоті. ?>
								<?php if ($compact || $status || $price) : ?>
									<div class="room-card__meta">
										<?php if ($status) : ?>
											<span class="badge <?= esc_attr($status['modifier']); ?>"><?= esc_html($status['label']); ?></span>
										<?php endif; ?>

										<?php if ($label) : ?>
											<span class="overline room-card__overline"><?= esc_html($label); ?></span>
										<?php endif; ?>

										<?php // У компактній картці ціна стоїть окремим рядком нижче, а не тут. ?>
										<?php if ($price && !$compact) : ?>
											<span class="room-card__price"><?= esc_html($price); ?></span>
										<?php endif; ?>
									</div>
								<?php endif; ?>

								<?php // Посилання на назві розтягується на всю картку (див. _rooms.scss). ?>
								<div class="room-card__headline">
									<h3 class="room-card__title">
										<a class="room-card__cover-link" href="<?= esc_url($url); ?>">
											<?= esc_html(get_the_title($room_id)); ?>
										</a>
									</h3>
								</div>

								<?php if ($parts) : ?>
									<p class="room-card__price">
										<span class="room-card__price-value"><?= esc_html($parts['amount']); ?></span>
										<span class="room-card__price-period"><?= esc_html($parts['period']); ?></span>
									</p>
								<?php endif; ?>

								<?php if ($text) : ?>
									<p class="room-card__text"><?= esc_html($text); ?></p>
								<?php endif; ?>

								<div class="room-card__footer">
									<a class="bttn bttn--secondary room-card__link" href="<?= esc_url($url); ?>">
										<?= esc_html($btn_text); ?>
									</a>
								</div>
							</div>

						</article>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php
		// Керування слайдером. Якщо слайди вміщаються, Swiper вимикається
		// (watchOverflow) і JS ховає цей блок атрибутом hidden.
		$arrow = '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">'
			. '<path d="M13 16.5001L6.49988 9.99983L13 3.5" stroke="currentColor" stroke-width="2"/></svg>';
		?>
		<div class="slider__nav" data-slider-nav hidden>
			<div class="slider__progress" data-slider-progress></div>

			<div class="slider__arrows">
				<button class="slider__arrow slider__arrow--prev" type="button" data-slider-prev
				        aria-label="<?php esc_attr_e('Попередні номери', 'delta'); ?>">
					<?= $arrow; // phpcs:ignore WordPress.Security.EscapeOutput — статичний SVG ?>
				</button>

				<button class="slider__arrow slider__arrow--next" type="button" data-slider-next
				        aria-label="<?php esc_attr_e('Наступні номери', 'delta'); ?>">
					<?= $arrow; // phpcs:ignore WordPress.Security.EscapeOutput — статичний SVG ?>
				</button>
			</div>
		</div>
		<?php endif; ?>

	</div>
</section>
