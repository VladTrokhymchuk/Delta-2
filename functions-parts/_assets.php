<?php
/**
 * Підключення ассетів зі збірки Vite через build/manifest.json.
 * Шляхи до файлів не хардкодимо — читаємо мапу entry → файл.
 *
 * Manifest keys (старт): critical, main, app, pages/page-404.
 * Критичний CSS вбудовується в <head>, сторінкові стилі/скрипти — умовно.
 *
 * @see README.md (розділ «Підключення у WordPress-темі»)
 */

if (!defined('ABSPATH')) exit;

/**
 * Кешований розбір build/manifest.json.
 */
function get_manifest() {
    static $manifest = null;
    if ($manifest !== null) return $manifest;

    $path = get_stylesheet_directory() . '/build/manifest.json';
    $manifest = is_readable($path)
        ? (json_decode(file_get_contents($path), true) ?: [])
        : [];

    return $manifest;
}

/**
 * URL до зібраного ассета за ключем manifest і типом (css|js).
 */
function get_asset($key, $type = 'css') {
    $manifest = get_manifest();
    if (empty($manifest[$key][$type])) return null;
    return get_stylesheet_directory_uri() . '/build/' . $manifest[$key][$type];
}

function my_assets() {
    $manifest = get_manifest();

    // Критичний CSS — інлайном у <head>.
    if (!empty($manifest['critical']['css'])) {
        $critical_path = get_stylesheet_directory() . '/build/' . $manifest['critical']['css'];
        if (is_readable($critical_path)) {
            wp_register_style('critical', false);
            wp_enqueue_style('critical');
            wp_add_inline_style('critical', file_get_contents($critical_path));
        }
    }

    // Головний CSS.
    if ($main = get_asset('main', 'css')) {
        wp_enqueue_style('main', $main, ['critical'], null);
    }

    // Головний JS.
    if ($app = get_asset('app', 'js')) {
        wp_enqueue_script('app', $app, [], null, true);
    }

    // Сторінкові стилі — умовно.
    if (is_404() && ($css404 = get_asset('pages/page-404', 'css'))) {
        wp_enqueue_style('page-404', $css404, ['main'], null);
    }

    // Приклад page-entry за шаблоном:
    // if (is_page_template('page-contacts.php') && ($css = get_asset('pages/page-contacts', 'css'))) {
    //     wp_enqueue_style('page-contacts', $css, ['main'], null);
    // }

    // Приклад умовної ліби під секцію конструктора:
    // if (function_exists('delta_has_section') && delta_has_section('rooms-slider')) {
    //     wp_enqueue_script('swiper', get_stylesheet_directory_uri() . '/build/js/libs/swiper-bundle.min.js', [], null, true);
    // }
}
add_action('wp_enqueue_scripts', 'my_assets', 20);

/**
 * Прибрати jQuery Migrate на фронті.
 */
function remove_jquery_migrate($scripts) {
    if (!is_admin() && isset($scripts->registered['jquery'])) {
        $deps = $scripts->registered['jquery']->deps;
        if ($deps) {
            $scripts->registered['jquery']->deps = array_diff($deps, ['jquery-migrate']);
        }
    }
}
add_action('wp_default_scripts', 'remove_jquery_migrate');
