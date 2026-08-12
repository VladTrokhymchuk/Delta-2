<?php
/**
 * Класичний редактор замість Gutenberg (узгоджено з конструктором сторінок).
 * Для CPT додатково ставимо show_in_rest => false при реєстрації.
 *
 * @see .claude/skills/page-constructor
 */

if (!defined('ABSPATH')) exit;

add_filter('use_block_editor_for_post', '__return_false', 10);
add_filter('use_block_editor_for_post_type', '__return_false', 10);

// Відв'язати дефолтні таксономії від дефолтного типу post (лише CPT у проєкті).
add_action('init', function () {
    register_taxonomy('category', []);
    register_taxonomy('post_tag', []);
});
