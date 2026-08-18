<?php
/**
 * Section: Rooms list — розгорнутий список номерів.
 * Кожен номер — рядок на всю ширину: фото з одного боку, опис із іншого.
 * Сусідні рядки дзеркальні й на різному фоні (див. _rooms-list.scss).
 *
 * Поля з ACF Flexible Content layout "rooms-list" (acf-json/group_page_builder.json).
 * Дані рядків — CPT `room` (acf-json/group_room.json).
 *
 * Два режими роботи:
 *   • конструктор — поля рядка Flexible Content (get_sub_field);
 *   • архів номерів /rooms/ — добірку рахує головний запит, і ID приходять
 *     у $args['ids'] (archive-room.php).
 *
 * @see functions-parts/parts/rooms.php
 * @see src/styles/sections/_rooms-list.scss
 */

if (!defined('ABSPATH')) exit;

$args = (isset($args) && is_array($args)) ? $args : array();

if ($args) {
	$overline     = '';
	$title        = '';
	$subtitle     = '';
	$detail_label = '';
	$book_label   = '';
	$book_link    = null;
	$ids          = (array) ($args['ids'] ?? array());
} else {
	$overline     = get_sub_field('rooms_list_overline');
	$title        = get_sub_field('rooms_list_title');
	$subtitle     = get_sub_field('rooms_list_subtitle');
	$detail_label = get_sub_field('rooms_list_detail_label');
	$book_label   = get_sub_field('rooms_list_book_label');
	$book_link    = get_sub_field('rooms_list_book_link');

	// --- Добірка номерів ----------------------------------------------------
	if ((get_sub_field('rooms_list_source') ?: 'auto') === 'manual') {
		$ids = (array) get_sub_field('rooms_list_items');
	} else {
		$limit = (int) get_sub_field('rooms_list_limit');
		$ids   = get_posts(array(
			'post_type'      => 'room',
			'post_status'    => 'publish',
			'posts_per_page' => $limit > 0 ? $limit : -1,
			'orderby'        => array('menu_order' => 'ASC', 'date' => 'DESC'),
			'fields'         => 'ids',
		));
	}
}

$ids = array_filter(array_map('intval', $ids));
if (!$ids) return;

// Підписи кнопок: поле секції → «Налаштування теми» → значення з макета.
$detail_label = $detail_label ?: delta_opt('rooms_page_detail_label', __('Детальніше', 'delta'));
$book_label   = $book_label   ?: delta_opt('rooms_page_book_label', __('Забронювати', 'delta'));
$book         = delta_rooms_book_link($book_link);
?>
<section class="section section--rooms-list" data-section="rooms-list"<?= delta_section_id_attr('rooms-list'); ?>>

	<?php if ($overline || $title || $subtitle) : ?>
		<div class="container">
			<div class="rooms-list__head">
				<?php if ($overline) : ?>
					<p class="overline rooms-list__overline"><?= esc_html($overline); ?></p>
				<?php endif; ?>

				<?php if ($title) : ?>
					<h2 class="rooms-list__title"><?= esc_html($title); ?></h2>
				<?php endif; ?>

				<?php if ($subtitle) : ?>
					<p class="rooms-list__subtitle"><?= esc_html($subtitle); ?></p>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php foreach (array_values($ids) as $i => $room_id) :
		// Непарні рядки дзеркальні й на темнішому фоні — один модифікатор на обидва.
		$alt      = ($i % 2 === 1) ? ' rooms-list__row--alt' : '';
		$permalink = get_permalink($room_id);
		$room_overline = get_field('room_overline', $room_id);
		$specs    = delta_room_specs($room_id);
		$price    = delta_room_price_parts($room_id);
		$text     = delta_room_excerpt($room_id, 40);
		?>
		<div class="rooms-list__row<?= esc_attr($alt); ?>">
			<div class="container rooms-list__inner">

				<?php if (has_post_thumbnail($room_id)) : ?>
					<div class="rooms-list__media">
						<?= get_the_post_thumbnail($room_id, 'large', array(
							'class'    => 'rooms-list__img',
							// Перший рядок видно одразу під першим екраном — його
							// відкладати не можна, решту вантажимо ліниво.
							'loading'  => $i === 0 ? 'eager' : 'lazy',
							'decoding' => 'async',
							'sizes'    => '(min-width: 992px) 50vw, 100vw',
							'alt'      => delta_image_alt(get_post_thumbnail_id($room_id), get_the_title($room_id)),
						)); ?>
					</div>
				<?php endif; ?>

				<div class="rooms-list__content">

					<?php // Надзаголовок, назва й орнамент — одна група: між ними
					      // ритм щільніший, ніж 32px між блоками контенту. ?>
					<div class="rooms-list__room-head">
						<?php if ($room_overline) : ?>
							<p class="overline rooms-list__room-overline"><?= esc_html($room_overline); ?></p>
						<?php endif; ?>

						<h2 class="rooms-list__room-title">
							<a href="<?= esc_url($permalink); ?>"><?= esc_html(get_the_title($room_id)); ?></a>
						</h2>

						<?php // Декоративний розділювач — від скрінрідерів ховаємо. ?>
						<span class="rooms-list__ornament" aria-hidden="true"></span>
					</div>

					<?php if ($text) : ?>
						<p class="rooms-list__text"><?= esc_html($text); ?></p>
					<?php endif; ?>

					<?php if ($specs) : ?>
						<ul class="rooms-list__specs">
							<?php foreach ($specs as $spec) :
								$icon = $spec['icon'] ? delta_icon($spec['icon']) : '';
								?>
								<li class="rooms-list__spec">
									<?php if ($icon) : ?>
										<span class="rooms-list__spec-icon">
											<?= $icon; // phpcs:ignore WordPress.Security.EscapeOutput — власний SVG теми ?>
										</span>
									<?php endif; ?>
									<span class="rooms-list__spec-text"><?= esc_html($spec['text']); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<div class="rooms-list__footer">

						<?php if ($price) : ?>
							<p class="rooms-list__price">
								<span class="rooms-list__price-label"><?php esc_html_e('Ціна від', 'delta'); ?></span>
								<span class="rooms-list__price-value">
									<?= esc_html($price['amount']); ?>
									<span class="rooms-list__price-period"><?= esc_html($price['period']); ?></span>
								</span>
							</p>
						<?php endif; ?>

						<div class="rooms-list__actions">
							<a class="bttn bttn--secondary rooms-list__action" href="<?= esc_url($permalink); ?>">
								<?= esc_html($detail_label); ?>
							</a>

							<a class="bttn rooms-list__action"
							   href="<?= esc_url($book['url']); ?>"
							   <?php if ($book['target']) : ?>target="<?= esc_attr($book['target']); ?>" rel="noopener"<?php endif; ?>>
								<?= esc_html($book_label); ?>
							</a>
						</div>
					</div>

				</div>

			</div>
		</div>
	<?php endforeach; ?>

</section>
