<?php
/**
 * Реєстр AJAX-обробників теми. Кожен домен — окремий файл у ajax/.
 *
 * Приклад:
 *   include_once __DIR__ . '/ajax/_ajax-rooms.php';
 *
 * Правила: свій `wp_ajax_` + `wp_ajax_nopriv_` хук, nonce на кожен запит,
 * санітизація вхідних даних, вихід через wp_send_json_success/error.
 */

if (!defined('ABSPATH')) exit;
