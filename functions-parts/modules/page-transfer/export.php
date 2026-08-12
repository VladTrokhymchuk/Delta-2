<?php
/**
 * Перенос сторінок — збірка пакета експорту.
 *
 * @see functions-parts/modules/page-transfer/init.php
 */

if (!defined('ABSPATH')) exit;

/**
 * Мета-ключі, які НЕ переносяться: службові, WPML-звʼязки, лічильники редактора.
 */
function delta_pt_meta_blocklist() {
    return array(
        '_edit_lock', '_edit_last', '_thumbnail_id', '_pingme', '_encloseme',
        '_wp_old_slug', '_wp_old_date', '_wp_page_template', // шаблон окремо, у post[]
    );
}

function delta_pt_meta_blocked($key) {
    if (in_array($key, delta_pt_meta_blocklist(), true)) return true;
    // WPML / Polylang звʼязки і кошик — привʼязані до конкретної бази.
    return (bool) preg_match('/^(_wp_trash_meta_|_icl_|_wpml_|_pll_|_delta_pt_)/', $key);
}

/**
 * Тип ACF-поля для мета-ключа. Ключ поля лежить у сусідній меті `_{key}`.
 * Для clone/group ACF пише складений ref (`field_a_field_b`) — тоді беремо
 * останній `field_…` фрагмент, це ключ реального поля.
 *
 * @return string тип поля або '' якщо це не ACF-поле.
 */
function delta_pt_field_type($key, array $meta_all) {
    if (!function_exists('acf_get_field')) return '';

    $ref = isset($meta_all['_' . $key][0]) ? $meta_all['_' . $key][0] : '';
    if (strpos($ref, 'field_') !== 0) return '';

    $field = acf_get_field($ref);
    if (!$field) {
        $pos = strrpos($ref, 'field_');
        if ($pos > 0) $field = acf_get_field(substr($ref, $pos));
    }

    return $field && !empty($field['type']) ? $field['type'] : '';
}

/** Типи полів, значення яких — ID вкладень. */
function delta_pt_media_types() {
    return array('image', 'file', 'gallery');
}

/** Типи полів, значення яких — ID інших записів. */
function delta_pt_post_types_fields() {
    return array('post_object', 'relationship');
}

/**
 * Витягує ID вкладень із значення поля (скаляр або масив).
 */
function delta_pt_ids_from_value($value) {
    $ids = array();
    foreach ((array) $value as $item) {
        if (is_array($item) && isset($item['ID'])) $item = $item['ID'];   // формат «масив»
        if (is_numeric($item) && (int) $item > 0)  $ids[] = (int) $item;
    }
    return $ids;
}

/**
 * Знаходить у HTML посилання на медіа цього сайту і повертає ID вкладень.
 * Потрібно для картинок, вставлених у WYSIWYG (вони не лежать у полях-image).
 */
function delta_pt_ids_from_html($html) {
    if (!is_string($html) || strpos($html, 'wp-content') === false) return array();

    $uploads = wp_get_upload_dir();
    $base    = preg_quote($uploads['baseurl'], '#');
    if (!preg_match_all('#' . $base . '/[^"\'\s\\\\)]+#i', $html, $m)) return array();

    $ids = array();
    foreach (array_unique($m[0]) as $url) {
        // Обрізаємо суфікс розміру: foo-300x200.jpg → foo.jpg
        $full = preg_replace('#-\d+x\d+(\.[a-z0-9]+)$#i', '$1', $url);
        $id   = attachment_url_to_postid($full);
        if (!$id) $id = attachment_url_to_postid($url);
        if ($id)  $ids[] = (int) $id;
    }
    return $ids;
}

/**
 * Рекурсивно збирає ID вкладень із HTML у всіх рядках значення.
 */
function delta_pt_html_ids_deep($value) {
    if (is_string($value)) return delta_pt_ids_from_html($value);
    if (!is_array($value)) return array();

    $ids = array();
    foreach ($value as $item) {
        $ids = array_merge($ids, delta_pt_html_ids_deep($item));
    }
    return $ids;
}

/**
 * Дані вкладення для пакета + шлях до файлу на диску.
 */
function delta_pt_attachment_data($id) {
    $post = get_post($id);
    if (!$post || $post->post_type !== 'attachment') return null;

    $path = get_attached_file($id);
    $url  = wp_get_attachment_url($id);

    return array(
        'data' => array(
            'filename'    => $path ? basename($path) : basename((string) $url),
            'url'         => $url,
            'mime'        => $post->post_mime_type,
            'title'       => $post->post_title,
            'alt'         => get_post_meta($id, '_wp_attachment_image_alt', true),
            'caption'     => $post->post_excerpt,
            'description' => $post->post_content,
            'hash'        => ($path && file_exists($path)) ? sha1_file($path) : '',
            'size'        => ($path && file_exists($path)) ? filesize($path) : 0,
        ),
        'path' => ($path && file_exists($path)) ? $path : '',
    );
}

/**
 * Збирає повний пакет експорту одного запису.
 *
 * @return array{data: array, files: array<int,string>}
 */
function delta_pt_build_export($post_id) {
    $post = get_post($post_id);
    if (!$post) return new WP_Error('delta_pt_no_post', 'Запис не знайдено.');

    $meta_all = get_post_meta($post_id);
    $meta     = array();
    $types    = array();
    $media    = array();
    $refs     = array();

    foreach ($meta_all as $key => $values) {
        if ($key === '' || delta_pt_meta_blocked($key)) continue;

        $value        = maybe_unserialize($values[0]);
        $meta[$key]   = $value;
        $type         = strpos($key, '_') === 0 ? '' : delta_pt_field_type($key, $meta_all);
        if ($type) $types[$key] = $type;

        if (in_array($type, delta_pt_media_types(), true)) {
            $media = array_merge($media, delta_pt_ids_from_value($value));
        } elseif (in_array($type, delta_pt_post_types_fields(), true)) {
            foreach (delta_pt_ids_from_value($value) as $ref_id) $refs[] = $ref_id;
        }

        // Картинки, вставлені прямо в HTML (WYSIWYG, textarea).
        $media = array_merge($media, delta_pt_html_ids_deep($value));
    }

    $thumb = (int) get_post_thumbnail_id($post_id);
    if ($thumb) $media[] = $thumb;

    $media = array_merge($media, delta_pt_ids_from_html($post->post_content));

    /* ── Вкладення ──────────────────────────────────────────────── */
    $attachments = array();
    $files       = array();
    foreach (array_unique(array_filter($media)) as $id) {
        $item = delta_pt_attachment_data($id);
        if (!$item) continue;

        if ($item['path']) {
            $item['data']['file'] = 'media/' . $id . '-' . $item['data']['filename'];
            $files[$id]           = $item['path'];
        }
        $attachments[$id] = $item['data'];
    }

    /* ── Посилання на інші записи (форми CF7, історії успіху тощо) ─ */
    $post_refs = array();
    foreach (array_unique(array_filter($refs)) as $id) {
        $ref = get_post($id);
        if (!$ref) continue;
        $post_refs[$id] = array(
            'post_type' => $ref->post_type,
            'slug'      => $ref->post_name,
            'title'     => $ref->post_title,
        );
    }

    /* ── Терміни таксономій ─────────────────────────────────────── */
    $terms = array();
    foreach (get_object_taxonomies($post->post_type) as $tax) {
        $tax_terms = wp_get_object_terms($post_id, $tax);
        if (is_wp_error($tax_terms) || !$tax_terms) continue;
        foreach ($tax_terms as $term) {
            $terms[$tax][] = array('slug' => $term->slug, 'name' => $term->name);
        }
    }

    $uploads = wp_get_upload_dir();

    $data = array(
        'delta_page_export' => 1,
        'generated'       => current_time('mysql'),
        'source'          => array(
            'home'         => untrailingslashit(home_url()),
            'site'         => untrailingslashit(site_url()),
            'uploads_url'  => untrailingslashit($uploads['baseurl']),
            'post_id'      => (int) $post_id,
            'post_type'    => $post->post_type,
        ),
        'post' => array(
            'post_title'    => $post->post_title,
            'post_name'     => $post->post_name,
            'post_content'  => $post->post_content,
            'post_excerpt'  => $post->post_excerpt,
            'post_status'   => $post->post_status,
            'menu_order'    => (int) $post->menu_order,
            'post_type'     => $post->post_type,
            'page_template' => get_page_template_slug($post_id),
        ),
        'thumbnail'   => $thumb,
        'meta'        => $meta,
        'types'       => $types,
        'attachments' => $attachments,
        'post_refs'   => $post_refs,
        'terms'       => $terms,
    );

    return array('data' => $data, 'files' => $files);
}

function delta_pt_json_encode($data) {
    return wp_json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE
    );
}

/**
 * Пише пакет у .zip: page.json + media/*.
 *
 * @return bool
 */
function delta_pt_write_zip(array $package, $path) {
    if (!class_exists('ZipArchive')) return false;

    $zip = new ZipArchive();
    if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) return false;

    $zip->addFromString('page.json', delta_pt_json_encode($package['data']));
    foreach ($package['files'] as $id => $file) {
        $zip->addFile($file, $package['data']['attachments'][$id]['file']);
    }

    return $zip->close();
}

/**
 * Віддає пакет у браузер: .zip (json + файли) або .json (файли в base64),
 * якщо на сервері немає ZipArchive.
 */
function delta_pt_send_package($post_id) {
    $package = delta_pt_build_export($post_id);
    if (is_wp_error($package)) wp_die($package->get_error_message());

    $post = get_post($post_id);
    $slug = $post->post_name ? $post->post_name : sanitize_title($post->post_title);
    $name = 'page-' . $post_id . '-' . ($slug ?: 'export') . '-' . date('Ymd-His');

    nocache_headers();

    if (class_exists('ZipArchive')) {
        $tmp = wp_tempnam($name . '.zip');
        if (delta_pt_write_zip($package, $tmp)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $name . '.zip"');
            header('Content-Length: ' . filesize($tmp));
            readfile($tmp);
            @unlink($tmp);
            exit;
        }
        @unlink($tmp);
    }

    // Фолбек: усе одним JSON, файли — base64.
    foreach ($package['files'] as $id => $path) {
        $package['data']['attachments'][$id]['base64'] = base64_encode((string) file_get_contents($path));
        unset($package['data']['attachments'][$id]['file']);
    }
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $name . '.json"');
    echo delta_pt_json_encode($package['data']);
    exit;
}
