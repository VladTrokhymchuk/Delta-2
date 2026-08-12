<?php
/**
 * ACF Pro — локальний JSON (acf-json/).
 * Групи полів версіонуються файлами, а не лежать лише в БД: після git pull
 * заходь в ACF → Групи полів → «Синхронізувати».
 *
 * @see .claude/skills/acf-fields
 */

if (!defined('ABSPATH')) exit;

// Зберігати групи полів у тему.
add_filter('acf/settings/save_json', function () {
    return get_stylesheet_directory() . '/acf-json';
});

// Підвантажувати їх звідти.
add_filter('acf/settings/load_json', function ($paths) {
    $paths[0] = get_stylesheet_directory() . '/acf-json';
    return $paths;
});
