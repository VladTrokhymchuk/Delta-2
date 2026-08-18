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
 * Якір секції для посилань із меню: id першої секції такого типу на сторінці.
 *
 * Повертає порожній рядок для повторів — два однакові id зробили б розмітку
 * невалідною, а браузер усе одно стрибав би на перший.
 *
 * Аліаси, а не назви layout'ів: у меню й адресному рядку користувач бачить
 * «/#contacts», а не «/#location». Якщо перейменувати ключ — зламаються
 * посилання в меню, тож правити тут і одразу в меню.
 *
 * Виняток — «media-text»: секція універсальна, і її аліас описує те, що в
 * рядку зараз (СПА-послуги). Змінили вміст — змініть і аліас із пунктом меню.
 *
 * @param string $layout Назва layout ACF Flexible Content.
 * @return string id для атрибута або '' — якщо секція на сторінці не перша.
 */
function delta_section_anchor($layout) {
    $aliases = array(
        'hero'        => 'hero',
        'intro'       => 'about',
        'rooms'       => 'rooms',
        'rooms-list'  => 'rooms',
        'amenities'   => 'services',
        'media-text'  => 'spa',
        'gallery'     => 'gallery',
        'reviews'     => 'reviews',
        'booking-cta' => 'booking',
        'location'    => 'contacts',
        'text'        => 'text',
    );

    $anchor = $aliases[$layout] ?? $layout;

    // Прапорець у $GLOBALS, а не static: секції підключаються то з
    // render_sections(), то з get_template_part(), тобто щоразу в іншій
    // області видимості, і static лічив би кожну з них окремо.
    if (!isset($GLOBALS['delta_section_anchors'])) {
        $GLOBALS['delta_section_anchors'] = array();
    }

    if (isset($GLOBALS['delta_section_anchors'][$anchor])) return '';

    $GLOBALS['delta_section_anchors'][$anchor] = true;

    return $anchor;
}

/**
 * Готовий атрибут id="" для тега секції (порожньо — якщо якір уже зайнятий).
 */
function delta_section_id_attr($layout) {
    $anchor = delta_section_anchor($layout);

    return $anchor ? ' id="' . esc_attr($anchor) . '"' : '';
}

/**
 * Сторінкам вмикаємо «Уривок» — типово post type `page` його не підтримує,
 * а секції-переліки (картки зі сторінок) беруть із нього короткий опис.
 */
add_action('init', function () {
    add_post_type_support('page', 'excerpt');
});
