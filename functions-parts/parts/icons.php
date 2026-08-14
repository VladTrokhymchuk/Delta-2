<?php
/**
 * Інлайн-іконки з build/img/icons/.
 *
 * Саме інлайном, а не <img src>: тоді іконка фарбується через currentColor
 * (у SVG навмисно stroke="currentColor") і не коштує окремого запиту.
 *
 * Нова іконка: поклади .svg у src/img/icons/ зі stroke="currentColor" —
 * і вона одразу доступна за іменем файлу.
 */

if (!defined('ABSPATH')) exit;

/**
 * Повертає вміст SVG-іконки або '' — якщо файлу немає.
 *
 * @param string $name Ім'я файлу без розширення (лише [a-z0-9_-]).
 */
function delta_icon($name) {
    static $cache = array();

    $name = strtolower((string) $name);

    // Захист від виходу за межі каталогу: ім'я з крапками чи слешами не пройде.
    if ($name === '' || !preg_match('/^[a-z0-9_-]+$/', $name)) {
        return '';
    }

    if (isset($cache[$name])) {
        return $cache[$name];
    }

    $path = get_stylesheet_directory() . '/build/img/icons/' . $name . '.svg';
    $cache[$name] = is_readable($path) ? file_get_contents($path) : '';

    return $cache[$name];
}

/**
 * Виводить іконку. Вміст — власний файл теми, тож ескейпити нічого:
 * це розмітка, а не користувацький ввід.
 */
function delta_the_icon($name) {
    echo delta_icon($name); // phpcs:ignore WordPress.Security.EscapeOutput
}
