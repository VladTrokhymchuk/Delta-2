<?php
/**
 * 301-редіректи зі старої структури URL.
 *
 * Стара тема тримала номери в CPT `news` зі слагом /news-post/{slug}/.
 * Нова тема цей тип не реєструє, тож без редіректів усі старі адреси
 * (а вони в індексі Google і в зовнішніх посиланнях) віддавали б 404.
 *
 * Зіставлення — за слагом: /news-post/lyuks-odnokimnatnij/ шукає номер
 * із тим самим слагом. Якщо номер не знайдено — редіректимо на архів
 * /rooms/, щоб користувач не впирався в порожню сторінку.
 *
 * Правило вимикається, коли міграція не потрібна:
 * define('DELTA_LEGACY_REDIRECTS', false) у wp-config.php.
 */

if (!defined('ABSPATH')) exit;

if (defined('DELTA_LEGACY_REDIRECTS') && !DELTA_LEGACY_REDIRECTS) return;

add_action('template_redirect', 'delta_legacy_room_redirects');
function delta_legacy_room_redirects() {
    if (!is_404()) return;

    $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', '/');
    if ($path === '') return;

    $parts = explode('/', $path);

    // Цікавить лише старий префікс.
    if ($parts[0] !== 'news-post') return;

    // /news-post/ (архів) → /rooms/
    $slug = $parts[1] ?? '';
    if ($slug === '') {
        delta_redirect_to_rooms_archive();
    }

    // /news-post/{slug}/ → номер із таким самим слагом
    $room = get_posts(array(
        'post_type'      => 'room',
        'post_status'    => 'publish',
        'name'           => sanitize_title($slug),
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ));

    if ($room) {
        wp_safe_redirect(get_permalink($room[0]), 301);
        exit;
    }

    delta_redirect_to_rooms_archive();
}

/** Фолбек: архів номерів, а якщо його немає — головна. */
function delta_redirect_to_rooms_archive() {
    $archive = get_post_type_archive_link('room');
    wp_safe_redirect($archive ?: home_url('/'), 301);
    exit;
}
