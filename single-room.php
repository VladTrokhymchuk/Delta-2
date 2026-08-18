<?php
/**
 * Сторінка номера — CPT `room` (/rooms/{slug}/).
 *
 * Як і архів, зібрана з готових секцій: перший екран → галерея → опис із
 * характеристиками → зручності → бронювання → інші номери. Склад фіксований
 * (конструктора в номерів немає), а весь текст редагується в полях номера
 * та в «Налаштування теми» → вкладки «Сторінка номера» й «Бронювання».
 *
 * @see functions-parts/parts/rooms.php (хелпери полів номера)
 * @see template-parts/room/about.php
 * @see acf-json/group_room.json
 */

if (!defined('ABSPATH')) exit;

// Сторінка зібрана з секцій — типові відступи <main> їй не потрібні.
// Фільтр читається при виводі <main>, тому ставимо його до get_header().
add_filter('delta_main_class', function ($class) {
	return trim($class . ' main--sections');
});

get_header();

while (have_posts()) : the_post();

	$room_id = get_the_ID();

	// --- Перший екран -------------------------------------------------------
	// Фон — «Зображення запису», а якщо його немає, перше фото з галереї:
	// у номера обкладинка й галерея часто заповнюються по черзі.
	$gallery = (array) get_field('room_gallery', $room_id);
	$hero_id = get_post_thumbnail_id($room_id) ?: (int) reset($gallery);

	get_template_part('template-parts/sections/hero', null, array(
		// Другий клас — власний фрейм сторінки номера (1440×640, заголовок
		// 64px); перший дає їй надзаголовок внутрішніх сторінок.
		'modifier' => 'section--hero-compact section--hero-room',
		'image'    => $hero_id,
		// Окреме поле, а не `room_overline`: той надзаголовок стоїть на картці
		// номера й у списку, і поверх фото читався б як та сама підпис-мітка.
		'overline' => get_field('room_hero_overline', $room_id),
		'title'    => get_the_title(),
	));

	// --- Галерея ------------------------------------------------------------
	get_template_part('template-parts/sections/gallery', null, array(
		'images'   => $gallery,
		'modifier' => 'section--gallery-room',
	));

	// --- Опис + характеристики ----------------------------------------------
	get_template_part('template-parts/room/about', null, array(
		'room' => $room_id,
	));

	// --- Зручності ----------------------------------------------------------
	// Заголовок має фолбек, тож секція вивелась би й порожньою — самим
	// заголовком над нічим. Тому вирішує наявність самих зручностей.
	$amenities = delta_room_amenities($room_id);

	if ($amenities) {
		get_template_part('template-parts/sections/amenities', null, array(
			'modifier' => 'section--amenities-room',
			'overline' => delta_opt('room_page_amenities_overline'),
			'title'    => delta_opt('room_page_amenities_title', __('Зручності номера', 'delta')),
			'items'    => $amenities,
		));
	}

	// --- Бронювання ---------------------------------------------------------
	get_template_part('template-parts/sections/booking-cta', null, array(
		'source' => 'room',
		'room'   => $room_id,
	));

	// --- Інші номери --------------------------------------------------------
	$similar_limit = (int) delta_opt('room_page_similar_limit', 4);
	$similar       = delta_room_similar($room_id, $similar_limit ?: 4);

	if ($similar) {
		get_template_part('template-parts/sections/rooms', null, array(
			'modifier'         => 'section--rooms-similar',
			'variant'          => 'compact',
			'overline'         => delta_opt('room_page_similar_overline'),
			'title'            => delta_opt('room_page_similar_title', __('Інші номери готелю', 'delta')),
			'ids'              => $similar,
			'button'           => delta_opt('rooms_page_detail_label', __('Детальніше', 'delta')),
			'per_view_desktop' => 4,
			'gap_desktop'      => 24,
		));
	}

endwhile;

get_footer();
