<?php
/**
 * Сторінка «Номери» — архів CPT `room` (/rooms/).
 *
 * Це не сторінка конструктора: архіву в WP не відповідає жоден запис, тож
 * полів рядка тут узяти нема звідки. Склад секцій фіксований (перший екран →
 * список номерів → заклик до бронювання), а весь текст редагується в
 * «Налаштування теми» → вкладки «Сторінка "Номери"» та «Бронювання».
 * Самі секції — ті самі частини, що й у конструкторі: вони вміють приймати
 * значення через $args.
 *
 * @see functions-parts/parts/rooms.php (порядок і кількість номерів у запиті)
 * @see template-parts/sections/rooms-list.php
 * @see acf-json/group_theme_settings.json
 */

if (!defined('ABSPATH')) exit;

// Аналог delta_sections_main_class(): сторінка зібрана з секцій, тож типові
// відступи <main> їй не потрібні — кожна секція має власні. Фільтр читається
// при виводі <main>, тому ставимо його до get_header().
add_filter('delta_main_class', function ($class) {
	return trim($class . ' main--sections');
});

get_header();

// --- Перший екран -----------------------------------------------------------
get_template_part('template-parts/sections/hero', null, array(
	'modifier' => 'section--hero-compact',
	'image'    => delta_opt('rooms_page_hero_image'), // return_format = id
	'overline' => delta_opt('rooms_page_hero_overline'),
	'title'    => delta_opt('rooms_page_hero_title', post_type_archive_title('', false)),
	'subtitle' => delta_opt('rooms_page_hero_subtitle'),
));

// --- Список номерів ---------------------------------------------------------
// ID беремо з головного запиту, а не окремим get_posts(): запит уже виконано,
// а другий такий самий коштував би зайвих SQL.
if (have_posts()) {
	get_template_part('template-parts/sections/rooms-list', null, array(
		'ids' => wp_list_pluck($GLOBALS['wp_query']->posts, 'ID'),
	));
} elseif (current_user_can('edit_posts')) {
	// Порожній архів — це або новий сайт, або всі номери в чернетках.
	// Повідомлення бачить лише той, хто може це виправити.
	printf(
		'<div class="section"><div class="container"><p>%s <a href="%s">%s</a></p></div></div>',
		esc_html__('Опублікованих номерів ще немає.', 'delta'),
		esc_url(admin_url('post-new.php?post_type=room')),
		esc_html__('Додати номер', 'delta')
	);
}

// --- Заклик до бронювання ---------------------------------------------------
get_template_part('template-parts/sections/booking-cta', null, array(
	'source' => 'options',
));

get_footer();
