<?php
/**
 * Перенос сторінок — застосування пакета до запису.
 *
 * @see functions-parts/modules/page-transfer/init.php
 */

if (!defined('ABSPATH')) exit;

/**
 * Читає завантажений файл (.zip або .json) → пакет.
 *
 * @return array{data: array, zip: ZipArchive|null}|WP_Error
 */
function delta_pt_read_package($path) {
    if (!file_exists($path)) {
        return new WP_Error('delta_pt_file', 'Файл не знайдено.');
    }

    $zip = null;
    $raw = '';

    if (class_exists('ZipArchive')) {
        $try = new ZipArchive();
        if ($try->open($path) === true) {
            $raw = $try->getFromName('page.json');
            if ($raw === false) {
                $try->close();
                return new WP_Error('delta_pt_zip', 'В архіві немає page.json — це не пакет експорту.');
            }
            $zip = $try;
        }
    }

    if (!$zip) {
        $raw = (string) file_get_contents($path);
    }

    $data = json_decode($raw, true);
    if (!is_array($data) || empty($data['delta_page_export'])) {
        if ($zip) $zip->close();
        return new WP_Error('delta_pt_json', 'Файл не схожий на пакет експорту сторінки.');
    }

    return array('data' => $data, 'zip' => $zip);
}

/**
 * Байти файлу вкладення: з архіву, з base64 або (як фолбек) з вихідного сайту.
 */
function delta_pt_attachment_bytes($item, $zip) {
    if ($zip && !empty($item['file'])) {
        $bytes = $zip->getFromName($item['file']);
        if ($bytes !== false) return $bytes;
    }

    if (!empty($item['base64'])) {
        $bytes = base64_decode($item['base64'], true);
        if ($bytes !== false) return $bytes;
    }

    // Файлу в пакеті немає — пробуємо стягнути з вихідного сайту (спрацює,
    // тільки якщо він доступний із цього сервера).
    if (!empty($item['url'])) {
        $res = wp_remote_get($item['url'], array('timeout' => 30));
        if (!is_wp_error($res) && (int) wp_remote_retrieve_response_code($res) === 200) {
            return wp_remote_retrieve_body($res);
        }
    }

    return false;
}

/**
 * Шукає вже імпортоване вкладення за хешем файлу — щоб повторний імпорт
 * не плодив копії в медіатеці.
 */
function delta_pt_find_by_hash($hash) {
    if (!$hash) return 0;

    $found = get_posts(array(
        'post_type'      => 'attachment',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_delta_pt_hash',
        'meta_value'     => $hash,
        'no_found_rows'  => true,
    ));

    return $found ? (int) $found[0] : 0;
}

/**
 * Заливає вкладення з пакета в медіатеку (або перевикористовує наявне).
 *
 * @return int|WP_Error новий ID вкладення.
 */
function delta_pt_sideload($item, $zip) {
    $hash     = isset($item['hash']) ? $item['hash'] : '';
    $existing = delta_pt_find_by_hash($hash);
    if ($existing) return $existing;

    $bytes = delta_pt_attachment_bytes($item, $zip);
    if ($bytes === false || $bytes === '') {
        return new WP_Error('delta_pt_media', 'не вдалося отримати файл');
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $filename = !empty($item['filename']) ? sanitize_file_name($item['filename']) : 'file';
    $tmp      = wp_tempnam($filename);
    if (!$tmp || file_put_contents($tmp, $bytes) === false) {
        return new WP_Error('delta_pt_media', 'не вдалося записати тимчасовий файл');
    }

    $id = media_handle_sideload(array('name' => $filename, 'tmp_name' => $tmp), 0, null, array(
        'post_title'   => isset($item['title']) ? $item['title'] : '',
        'post_excerpt' => isset($item['caption']) ? $item['caption'] : '',
        'post_content' => isset($item['description']) ? $item['description'] : '',
    ));

    if (is_wp_error($id)) {
        @unlink($tmp);
        return $id;
    }

    if (!empty($item['alt'])) update_post_meta($id, '_wp_attachment_image_alt', $item['alt']);
    if ($hash)               update_post_meta($id, '_delta_pt_hash', $hash);
    if (!empty($item['url'])) update_post_meta($id, '_delta_pt_src', esc_url_raw($item['url']));

    return (int) $id;
}

/**
 * Рекурсивна заміна рядків у значенні мети (домен вихідного сайту → цей,
 * старі URL медіа → нові).
 *
 * strtr(), а не str_replace(): він іде рядком за один прохід, обираючи в кожній
 * позиції найдовший збіг, і НЕ перечитує вже підставлений текст. З масивами
 * str_replace() робить і те, і те неправильно — «…/1» зʼїдає початок
 * «…/1-1-scaled.webp», а потім ще раз лізе всередину щойно вставленого URL.
 */
function delta_pt_replace_deep($value, array $pairs) {
    if (is_string($value)) return $pairs ? strtr($value, $pairs) : $value;
    if (!is_array($value))  return $value;

    $out = array();
    foreach ($value as $k => $v) {
        $out[$k] = delta_pt_replace_deep($v, $pairs);
    }
    return $out;
}

/**
 * Підміняє ID у значенні поля-медіа / поля-звʼязку.
 */
function delta_pt_remap_ids($value, array $map) {
    if (is_array($value)) {
        $out = array();
        foreach ($value as $k => $v) {
            $new = delta_pt_remap_ids($v, $map);
            if ($new !== null && $new !== '') $out[$k] = $new;
        }
        return array_values($out);
    }

    if (!is_numeric($value) || (int) $value <= 0) return $value;

    $old = (int) $value;
    return isset($map[$old]) ? (string) $map[$old] : '';
}

/**
 * Пари «старий URL → новий URL» для заміни у HTML.
 * Ключ — база без розширення, щоб підмінялись і мініатюри (-300x200).
 *
 * @return array<string,string> для strtr().
 */
function delta_pt_url_pairs(array $attachments, array $map) {
    $pairs = array();

    foreach ($attachments as $old_id => $item) {
        if (empty($item['url']) || empty($map[$old_id])) continue;

        $new_url = wp_get_attachment_url($map[$old_id]);
        if (!$new_url) continue;

        $old_base = preg_replace('#\.[a-z0-9]+$#i', '', $item['url']);
        if ($old_base === '') continue;

        $pairs[$old_base] = preg_replace('#\.[a-z0-9]+$#i', '', $new_url);
    }

    return $pairs;
}

/**
 * Прибирає з запису стару ACF-мету, щоб від попереднього контенту не лишились
 * «хвости» (зайві рядки репітерів/флексібла).
 */
function delta_pt_wipe_acf_meta($post_id, array $incoming_keys) {
    $meta_all = get_post_meta($post_id);
    $delete   = array();

    foreach ($meta_all as $key => $values) {
        if ($key === '' || delta_pt_meta_blocked($key)) continue;

        $is_ref  = strpos($key, '_') === 0 && strpos((string) $values[0], 'field_') === 0;
        $has_ref = isset($meta_all['_' . $key][0]) && strpos((string) $meta_all['_' . $key][0], 'field_') === 0;

        if ($is_ref || $has_ref || in_array($key, $incoming_keys, true)) {
            $delete[] = $key;
        }
    }

    foreach (array_unique($delete) as $key) {
        delete_post_meta($post_id, $key);
    }
}

/**
 * Застосовує пакет до запису.
 *
 * @param array $package результат delta_pt_read_package().
 * @param int   $target  ID запису-приймача.
 * @param array $opts    title / slug / content / status / terms (bool).
 *
 * @return array{ok: bool, message: string, notes: string[]}|WP_Error
 */
function delta_pt_import($package, $target, array $opts = array()) {
    $data   = $package['data'];
    $zip    = $package['zip'];
    $target = (int) $target;
    $post   = get_post($target);

    if (!$post) return new WP_Error('delta_pt_target', 'Цільовий запис не знайдено.');

    $opts = array_merge(array(
        'title'   => true,
        'slug'    => false,
        'content' => true,
        'status'  => false,
        'terms'   => true,
    ), $opts);

    $notes = array();

    if (!empty($data['source']['post_type']) && $data['source']['post_type'] !== $post->post_type) {
        $notes[] = sprintf(
            'Тип запису відрізняється: у пакеті «%s», у приймача «%s» — частина полів може не показатись.',
            $data['source']['post_type'],
            $post->post_type
        );
    }

    /* ── 1. Медіа ───────────────────────────────────────────────── */
    $media_map = array();
    $created   = 0;
    foreach ((array) $data['attachments'] as $old_id => $item) {
        $new_id = delta_pt_sideload($item, $zip);
        if (is_wp_error($new_id)) {
            $notes[] = sprintf('Зображення «%s» не імпортовано: %s',
                isset($item['filename']) ? $item['filename'] : $old_id,
                $new_id->get_error_message()
            );
            continue;
        }
        $media_map[(int) $old_id] = (int) $new_id;
        $created++;
    }

    /* ── 2. Звʼязки на інші записи — по слагу в межах типу ──────── */
    $post_map = array();
    foreach ((array) $data['post_refs'] as $old_id => $ref) {
        $found = get_posts(array(
            'post_type'      => $ref['post_type'],
            'name'           => $ref['slug'],
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ));
        if ($found) {
            $post_map[(int) $old_id] = (int) $found[0];
        } else {
            $notes[] = sprintf('Не знайдено запис «%s» (%s) для поля-звʼязку — поле лишиться порожнім.',
                $ref['title'], $ref['post_type']);
        }
    }

    /* ── 3. Заміни у рядках: домен + URL медіа ──────────────────── */
    $pairs = delta_pt_url_pairs((array) $data['attachments'], $media_map);

    $src_home = isset($data['source']['home']) ? $data['source']['home'] : '';
    if ($src_home && $src_home !== untrailingslashit(home_url())) {
        $pairs[$src_home] = untrailingslashit(home_url());
    }

    $rewrite = function ($value) use ($pairs) {
        return delta_pt_replace_deep($value, $pairs);
    };

    /* ── 4. Мета ────────────────────────────────────────────────── */
    $meta  = (array) $data['meta'];
    $types = (array) $data['types'];

    delta_pt_wipe_acf_meta($target, array_keys($meta));

    foreach ($meta as $key => $value) {
        $value = $rewrite($value);
        $type  = isset($types[$key]) ? $types[$key] : '';

        if (in_array($type, delta_pt_media_types(), true)) {
            $value = delta_pt_remap_ids($value, $media_map);
        } elseif (in_array($type, delta_pt_post_types_fields(), true)) {
            $value = delta_pt_remap_ids($value, $post_map);
        }

        update_post_meta($target, $key, wp_slash($value));
    }

    /* ── 5. Поля запису ─────────────────────────────────────────── */
    $update = array('ID' => $target);
    if ($opts['title'])   $update['post_title']   = $data['post']['post_title'];
    if ($opts['slug'])    $update['post_name']    = $data['post']['post_name'];
    if ($opts['status'])  $update['post_status']  = $data['post']['post_status'];
    if ($opts['content']) {
        $update['post_content'] = $rewrite($data['post']['post_content']);
        $update['post_excerpt'] = $rewrite($data['post']['post_excerpt']);
    }

    if (count($update) > 1) {
        $res = wp_update_post(wp_slash($update), true);
        if (is_wp_error($res)) $notes[] = 'Не вдалося оновити поля запису: ' . $res->get_error_message();
    }

    $template = isset($data['post']['page_template']) ? $data['post']['page_template'] : '';
    update_post_meta($target, '_wp_page_template', $template ? $template : 'default');

    /* ── 6. Мініатюра ───────────────────────────────────────────── */
    $thumb = (int) (isset($data['thumbnail']) ? $data['thumbnail'] : 0);
    if ($thumb && isset($media_map[$thumb])) {
        set_post_thumbnail($target, $media_map[$thumb]);
    }

    /* ── 7. Терміни таксономій ──────────────────────────────────── */
    if ($opts['terms']) {
        foreach ((array) $data['terms'] as $tax => $terms) {
            if (!taxonomy_exists($tax)) continue;

            $ids = array();
            foreach ($terms as $term) {
                $existing = get_term_by('slug', $term['slug'], $tax);
                if (!$existing) {
                    $new = wp_insert_term($term['name'], $tax, array('slug' => $term['slug']));
                    if (is_wp_error($new)) continue;
                    $ids[] = (int) $new['term_id'];
                } else {
                    $ids[] = (int) $existing->term_id;
                }
            }
            if ($ids) wp_set_object_terms($target, $ids, $tax);
        }
    }

    if ($zip) $zip->close();

    // ACF кешує значення полів у пам'яті процесу — після сирого запису чистимо.
    if (function_exists('acf_flush_value_cache')) {
        acf_flush_value_cache($target);
    }
    clean_post_cache($target);

    return array(
        'ok'      => true,
        'message' => sprintf(
            'Імпортовано у «%s»: %d мета-полів, %d зображень (з %d у пакеті).',
            get_the_title($target),
            count($meta),
            $created,
            count((array) $data['attachments'])
        ),
        'notes'   => $notes,
    );
}
