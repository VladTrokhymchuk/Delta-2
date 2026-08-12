<?php
/**
 * Конструктор сторінок — єдиний рендер-луп.
 * Кожен layout ACF Flexible Content `page_sections` => template-parts/sections/{layout}.php
 *
 * Нові секції створюються скілом section-builder (4 артефакти з однаковим slug).
 *
 * @see .claude/skills/page-constructor, .claude/skills/section-builder
 */

if (!defined('ABSPATH')) exit;

/**
 * Рендерить усі секції конструктора для поточного (або заданого) запису.
 */
function render_sections($post_id = null) {
    if (!function_exists('have_rows') || !have_rows('page_sections', $post_id)) {
        return;
    }

    while (have_rows('page_sections', $post_id)) {
        the_row();
        $layout = get_row_layout();
        $part   = locate_template("template-parts/sections/{$layout}.php");

        if ($part) {
            include $part; // template part читає поля через get_sub_field()
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            echo "<!-- section template missing: {$layout} -->";
        }
    }
}

/**
 * Чи є серед секцій запису хоч один із перелічених layout'ів.
 * Читаємо СИРУ мету: ACF тримає у flexible content простий масив назв layout'ів
 * (['hero', 'rooms-slider', ...]) — цього досить, щоб вирішити долю ассетів,
 * і це не тягне ACF та всі поля секцій у wp_enqueue_scripts.
 *
 * @param string|array $layouts назва layout або їх список.
 */
function delta_has_section($layouts, $post_id = null) {
    if (!$post_id) {
        // Без is_singular() на архіві сюди прилетів би ID терміна — і ми читали б
        // мету випадкового запису з таким же ID.
        if (!is_singular()) return false;
        $post_id = get_queried_object_id();
    }
    if (!$post_id) return false;

    $rows = get_post_meta($post_id, 'page_sections', true);
    if (!is_array($rows)) return false;

    return (bool) array_intersect((array) $layouts, $rows);
}

/**
 * Сторінка, зібрана конструктором, не має типових відступів <main> —
 * кожна секція має власні, а перша ще й фон на всю ширину.
 * Викликати ДО get_header() (фільтр читається при виводі <main>).
 */
function delta_sections_main_class($post_id = null) {
    if (!function_exists('get_field')) return;

    $post_id = $post_id ?: get_queried_object_id();
    if (!get_field('page_sections', $post_id)) return;

    add_filter('delta_main_class', function ($class) {
        return trim($class . ' main--sections');
    });
}

/**
 * Сторінкам вмикаємо «Уривок» — типово post type `page` його не підтримує,
 * а секції-переліки (картки зі сторінок) беруть із нього короткий опис.
 */
add_action('init', function () {
    add_post_type_support('page', 'excerpt');
});
