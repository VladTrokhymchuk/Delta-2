<?php
/**
 * Коментарі вимкнені повністю.
 *
 * Правило проєкту — лише CPT без дефолтних Записів; коментарів у готелі немає
 * ні на сторінках, ні в номерах. Пункт меню вже прибирає configure_menu.php,
 * але цього мало: `/wp-admin/edit-comments.php` відкривався за прямим
 * посиланням, а сам екран лишався ціллю для спаму й місцем, куди редактор
 * може випадково зайти й побачити чужі дані.
 *
 * Тут — усе інше: доступ, підтримка в типах записів, сліди в інтерфейсі.
 * Фільтр `comments_open` та вимкнення фідів коментарів живуть у headache.php.
 *
 * @see functions-parts/parts/configure_menu.php (remove_menu_page)
 * @see functions-parts/headache.php (comments_open, фіди)
 */

if (!defined('ABSPATH')) exit;

/**
 * Пряме звернення до екрана коментарів — назад на головну адмінки.
 * Спрацьовує до виводу розмітки, тож користувач бачить лише консоль.
 */
add_action('admin_init', 'delta_block_comments_screen');
function delta_block_comments_screen() {
    global $pagenow;

    if ($pagenow !== 'edit-comments.php' && $pagenow !== 'options-discussion.php') return;

    wp_safe_redirect(admin_url(), 302);
    exit;
}

/**
 * Прибрати підтримку коментарів і трекбеків у всіх типів записів —
 * інакше метабокс «Обговорення» лишається в редакторі, а WP далі рахує
 * дозволи на коментування.
 */
add_action('init', 'delta_remove_comment_support', 100);
function delta_remove_comment_support() {
    foreach (get_post_types(array(), 'names') as $type) {
        if (post_type_supports($type, 'comments')) {
            remove_post_type_support($type, 'comments');
            remove_post_type_support($type, 'trackbacks');
        }
    }
}

/**
 * Сліди в інтерфейсі: пункт «Коментарі» в адмін-барі, віджет консолі
 * «Останні коментарі» та колонка коментарів у списках записів.
 */
add_action('wp_before_admin_bar_render', 'delta_remove_comments_admin_bar');
function delta_remove_comments_admin_bar() {
    global $wp_admin_bar;

    $wp_admin_bar->remove_menu('comments');
}

add_action('wp_dashboard_setup', 'delta_remove_comments_dashboard');
function delta_remove_comments_dashboard() {
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
}

add_filter('manage_edit-page_columns', 'delta_remove_comments_column');
add_filter('manage_edit-post_columns', 'delta_remove_comments_column');
add_filter('manage_edit-room_columns', 'delta_remove_comments_column');
add_filter('manage_edit-review_columns', 'delta_remove_comments_column');
function delta_remove_comments_column($columns) {
    unset($columns['comments']);

    return $columns;
}

/**
 * Фронт: закриваємо й існуючі коментарі теж.
 *
 * `comments_open` у headache.php забороняє нові, але старі коментарі (якщо
 * лишились у базі від попередньої теми) далі виводились би шаблоном.
 */
add_filter('comments_array', '__return_empty_array', 10);
add_filter('pings_open', '__return_false', 20);
add_filter('feed_links_show_comments_feed', '__return_false');
