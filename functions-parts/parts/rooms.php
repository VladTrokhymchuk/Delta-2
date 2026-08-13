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
 * Короткий опис для картки: уривок, а якщо його немає — з контенту.
 */
function delta_room_excerpt($post_id = null, $words = 24) {
    $post_id = $post_id ?: get_the_ID();

    $excerpt = get_the_excerpt($post_id);
    if (!$excerpt) return '';

    return wp_trim_words($excerpt, $words, '…');
}
