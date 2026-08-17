<?php
/**
 * Хелпери номерів (CPT `room`).
 *
 * @see acf-json/group_room.json
 */

if (!defined('ABSPATH')) exit;

/**
 * Ціна номера у форматі «₴3,200 / ніч». Порожня ціна → ''.
 */
function delta_room_price($post_id = null) {
    if (!function_exists('get_field')) return '';

    $price = get_field('room_price', $post_id ?: get_the_ID());
    if (!$price) return '';

    return sprintf(
        /* translators: %s — відформатована ціна з валютою */
        __('%s / ніч', 'delta'),
        '₴' . number_format((float) $price, 0, ',', ',')
    );
}

/**
 * Статус номера для бейджа: ['label' => ..., 'modifier' => ...].
 * Невідомий/порожній статус → бейджа немає.
 */
function delta_room_status($post_id = null) {
    if (!function_exists('get_field')) return null;

    $status = get_field('room_status', $post_id ?: get_the_ID());

    $map = array(
        'available' => array('label' => __('вільний', 'delta'),  'modifier' => 'badge--available'),
        'busy'      => array('label' => __('зайнятий', 'delta'), 'modifier' => 'badge--busy'),
    );

    return $map[$status] ?? null;
}

/**
 * Ціна для розгорнутого списку: ['amount' => '7 800 ₴', 'period' => '/ ніч'].
 * Порожня ціна → null.
 *
 * Окремо від delta_room_price(): у списку ціна набирається двома кеглями
 * (сума крупно, «/ ніч» дрібно), тож одним рядком її не віддати. Формат теж
 * інший — у макеті сторінки «Номери» гривня стоїть після суми, а розряди
 * розділені пробілом. Пробіл нерозривний, щоб «7 800» не переносилось.
 */
function delta_room_price_parts($post_id = null) {
    if (!function_exists('get_field')) return null;

    $price = get_field('room_price', $post_id ?: get_the_ID());
    if (!$price) return null;

    return array(
        'amount' => number_format((float) $price, 0, ',', "\xC2\xA0") . "\xC2\xA0₴",
        'period' => __('/ ніч', 'delta'),
    );
}

/**
 * Характеристики для рядка списку: [['icon' => 'bed', 'text' => 'King Bed'], ...].
 *
 * Джерело — репітер `room_specs` (іконка + текст). Якщо його не заповнили,
 * показуємо хоч площу з окремого поля — рядок із однією характеристикою
 * виглядає краще за порожнє місце під описом.
 */
function delta_room_specs($post_id = null) {
    if (!function_exists('get_field')) return array();

    $post_id = $post_id ?: get_the_ID();
    $specs   = array();

    foreach ((array) get_field('room_specs', $post_id) as $row) {
        $text = trim((string) ($row['text'] ?? ''));
        if ($text === '') continue;

        $specs[] = array(
            'icon' => (string) ($row['icon'] ?? ''),
            'text' => $text,
        );
    }

    if (!$specs && ($area = get_field('room_area', $post_id))) {
        $specs[] = array(
            'icon' => 'area',
            /* translators: %s — площа номера в м² */
            'text' => sprintf(__('%s м²', 'delta'), $area),
        );
    }

    return $specs;
}

/**
 * Куди веде кнопка «Забронювати» у списку номерів.
 * Пріоритет: поле секції → «Налаштування теми» → якір на форму цієї ж сторінки.
 *
 * @param array|null $link ACF-поле link із секції.
 * @return array ['url' => ..., 'target' => ...]
 */
function delta_rooms_book_link($link = null) {
    if (empty($link['url'])) {
        $link = delta_opt('rooms_page_book_link');
    }

    if (empty($link['url'])) {
        return array('url' => '#booking', 'target' => '');
    }

    return array(
        'url'    => $link['url'],
        'target' => $link['target'] ?? '',
    );
}

/**
 * Короткий опис для картки: уривок, а якщо його немає — з контенту.
 */
function delta_room_excerpt($post_id = null, $words = 24) {
    $post_id = $post_id ?: get_the_ID();

    $excerpt = get_the_excerpt($post_id);
    if (!$excerpt) return '';

    return wp_trim_words($excerpt, $words, '…');
}

/**
 * Архів /rooms/ — уся добірка одним списком і в тому ж порядку, що в адмінці.
 *
 * Без цього WP віддав би 10 номерів на сторінку з пагінацією, а сортував би
 * за датою публікації — тобто порядок у списку не збігався б із порядком,
 * який менеджер виставив перетягуванням («Порядок» / page-attributes).
 */
add_action('pre_get_posts', function ($query) {
    if (is_admin() || !$query->is_main_query() || !$query->is_post_type_archive('room')) {
        return;
    }

    $query->set('posts_per_page', -1);
    $query->set('orderby', array('menu_order' => 'ASC', 'date' => 'DESC'));
});
