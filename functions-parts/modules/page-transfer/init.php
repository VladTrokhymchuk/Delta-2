<?php
/**
 * Перенос сторінок (тимчасовий інструмент).
 *
 * Експортує запис із усіма ACF-полями конструктора, мініатюрою, термінами
 * та ФАЙЛАМИ зображень у .zip, і заливає цей пакет у такий самий запис на
 * іншому сайті (локал → прод). Файли їдуть усередині пакета, тому продові
 * не потрібен доступ до локального сайту.
 *
 * Де кнопки:
 *  • у редакторі запису — метабокс «Перенос сторінки» (експорт + лінк на імпорт);
 *  • Інструменти → «Перенос сторінок» — експорт/імпорт будь-якого запису.
 *
 * Вимкнути (не видаляючи код): define('DELTA_PAGE_TRANSFER', false) у wp-config.php.
 * Видалити повністю: цей каталог + рядок include у functions.php.
 */

if (!defined('ABSPATH')) exit;

if (defined('DELTA_PAGE_TRANSFER') && !DELTA_PAGE_TRANSFER) return;

require_once __DIR__ . '/export.php';
require_once __DIR__ . '/import.php';

/** Права: інструмент лише для адмінів. */
function delta_pt_cap() {
    return 'manage_options';
}

function delta_pt_page_url($args = array()) {
    return add_query_arg(array_merge(array('page' => 'delta-page-transfer'), $args), admin_url('tools.php'));
}

/** Типи записів, які має сенс переносити. */
function delta_pt_post_types() {
    $types = get_post_types(array('show_ui' => true), 'objects');
    unset($types['attachment']);
    return apply_filters('delta_pt_post_types', $types);
}

/* ───────────────────────── Метабокс у редакторі ──────────────────────── */

add_action('add_meta_boxes', function () {
    if (!current_user_can(delta_pt_cap())) return;

    add_meta_box(
        'delta-page-transfer',
        'Перенос сторінки',
        'delta_pt_metabox',
        array_keys(delta_pt_post_types()),
        'side',
        'low'
    );
});

function delta_pt_metabox($post) {
    $export = wp_nonce_url(
        admin_url('admin-post.php?action=delta_pt_export&post=' . $post->ID),
        'delta_pt_export_' . $post->ID
    );
    ?>
    <p style="margin-top:0">
        Пакет із усіма полями конструктора та зображеннями — щоб залити цей самий
        контент у таку саму сторінку на іншому сайті.
    </p>
    <p>
        <a class="button button-primary" href="<?php echo esc_url($export); ?>">⬇ Експорт (.zip)</a>
    </p>
    <p style="margin-bottom:0">
        <a href="<?php echo esc_url(delta_pt_page_url(array('target' => $post->ID))); ?>">⬆ Імпортувати сюди пакет…</a>
    </p>
    <?php
}

/* ───────────────────────── Сторінка інструменту ──────────────────────── */

add_action('admin_menu', function () {
    add_management_page(
        'Перенос сторінок',
        'Перенос сторінок',
        delta_pt_cap(),
        'delta-page-transfer',
        'delta_pt_render_page'
    );
});

/** Список записів для селектів. */
function delta_pt_options($selected = 0) {
    $html = '<option value="">— оберіть запис —</option>';

    foreach (delta_pt_post_types() as $type => $obj) {
        $posts = get_posts(array(
            'post_type'      => $type,
            'post_status'    => array('publish', 'draft', 'pending', 'private'),
            'posts_per_page' => 300,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ));
        if (!$posts) continue;

        $html .= '<optgroup label="' . esc_attr($obj->labels->name) . '">';
        foreach ($posts as $p) {
            $html .= sprintf(
                '<option value="%d"%s>%s (#%d)</option>',
                $p->ID,
                selected($selected, $p->ID, false),
                esc_html($p->post_title ? $p->post_title : '(без назви)'),
                $p->ID
            );
        }
        $html .= '</optgroup>';
    }

    return $html;
}

function delta_pt_render_page() {
    if (!current_user_can(delta_pt_cap())) wp_die('Немає доступу.');

    $target = isset($_GET['target']) ? (int) $_GET['target'] : 0;
    $source = isset($_GET['source']) ? (int) $_GET['source'] : 0;
    $result = get_transient('delta_pt_result_' . get_current_user_id());
    if ($result) delete_transient('delta_pt_result_' . get_current_user_id());
    ?>
    <div class="wrap">
        <h1>Перенос сторінок</h1>
        <p class="description" style="max-width:760px">
            Експортує запис із усіма полями конструктора, мініатюрою, термінами й
            <strong>файлами зображень</strong> у .zip. На другому сайті обираєш такий самий
            запис і заливаєш пакет — поля перезаписуються, зображення додаються в медіатеку
            (повторний імпорт того самого файлу дублікатів не створює).
        </p>

        <?php if ($result) : ?>
            <div class="notice notice-<?php echo empty($result['ok']) ? 'error' : 'success'; ?>">
                <p><strong><?php echo esc_html($result['message']); ?></strong></p>
                <?php if (!empty($result['notes'])) : ?>
                    <ul style="list-style:disc;margin-left:20px">
                        <?php foreach ($result['notes'] as $note) : ?>
                            <li><?php echo esc_html($note); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if (!empty($result['ok']) && !empty($result['edit'])) : ?>
                    <p><a href="<?php echo esc_url($result['edit']); ?>">Відкрити запис →</a></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div style="display:flex;gap:20px;flex-wrap:wrap;align-items:flex-start;margin-top:20px">

            <div class="card" style="max-width:420px;padding:8px 20px 20px">
                <h2>1. Експорт</h2>
                <form method="get" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="delta_pt_export">
                    <?php wp_nonce_field('delta_pt_export_any'); ?>
                    <p>
                        <label for="delta-pt-source"><strong>Що експортувати</strong></label><br>
                        <select name="post" id="delta-pt-source" style="width:100%" required>
                            <?php echo delta_pt_options($source); ?>
                        </select>
                    </p>
                    <p><button class="button button-primary">⬇ Завантажити пакет</button></p>
                </form>
            </div>

            <div class="card" style="max-width:520px;padding:8px 20px 20px">
                <h2>2. Імпорт</h2>
                <form method="post" enctype="multipart/form-data"
                      action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="delta_pt_import">
                    <?php wp_nonce_field('delta_pt_import'); ?>

                    <p>
                        <label for="delta-pt-target"><strong>Куди імпортувати</strong></label><br>
                        <select name="target" id="delta-pt-target" style="width:100%" required>
                            <?php echo delta_pt_options($target); ?>
                        </select>
                    </p>

                    <p>
                        <label for="delta-pt-file"><strong>Пакет (.zip або .json)</strong></label><br>
                        <input type="file" name="delta_pt_file" id="delta-pt-file" accept=".zip,.json" required>
                        <br><span class="description">Максимум для завантаження: <?php echo esc_html(size_format(wp_max_upload_size())); ?></span>
                    </p>

                    <fieldset style="margin:12px 0">
                        <legend><strong>Що оновити</strong></legend>
                        <label><input type="checkbox" name="opt[title]" value="1" checked> заголовок</label><br>
                        <label><input type="checkbox" name="opt[content]" value="1" checked> вміст редактора та уривок</label><br>
                        <label><input type="checkbox" name="opt[terms]" value="1" checked> терміни таксономій</label><br>
                        <label><input type="checkbox" name="opt[slug]" value="1"> слаг (URL)</label><br>
                        <label><input type="checkbox" name="opt[status]" value="1"> статус публікації</label>
                    </fieldset>

                    <p class="description">
                        Усі поля ACF цільового запису буде <strong>перезаписано</strong> вмістом пакета.
                        Зроби бекап бази, якщо запис на приймачі не порожній.
                    </p>

                    <p><button class="button button-primary">⬆ Імпортувати</button></p>
                </form>
            </div>
        </div>
    </div>
    <?php
}

/* ─────────────────────────────── Обробники ───────────────────────────── */

add_action('admin_post_delta_pt_export', function () {
    $post_id = isset($_REQUEST['post']) ? (int) $_REQUEST['post'] : 0;

    if (!$post_id || !current_user_can(delta_pt_cap()) || !current_user_can('edit_post', $post_id)) {
        wp_die('Немає доступу до експорту.');
    }

    $nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';
    if (!wp_verify_nonce($nonce, 'delta_pt_export_' . $post_id) && !wp_verify_nonce($nonce, 'delta_pt_export_any')) {
        wp_die('Недійсний токен — онови сторінку.');
    }

    @set_time_limit(300);
    delta_pt_send_package($post_id);
});

add_action('admin_post_delta_pt_import', function () {
    check_admin_referer('delta_pt_import');

    $target = isset($_POST['target']) ? (int) $_POST['target'] : 0;

    if (!current_user_can(delta_pt_cap()) || !current_user_can('upload_files')
        || !$target || !current_user_can('edit_post', $target)) {
        wp_die('Немає доступу до імпорту.');
    }

    $result = array('ok' => false, 'message' => '', 'notes' => array());

    $file = isset($_FILES['delta_pt_file']) ? $_FILES['delta_pt_file'] : null;
    if (!$file || !empty($file['error']) || empty($file['tmp_name'])) {
        $result['message'] = 'Файл не завантажено (можливо, він більший за ліміт '
            . size_format(wp_max_upload_size()) . ').';
    } else {
        @set_time_limit(600);

        $package = delta_pt_read_package($file['tmp_name']);
        if (is_wp_error($package)) {
            $result['message'] = $package->get_error_message();
        } else {
            $opts = isset($_POST['opt']) ? (array) $_POST['opt'] : array();
            $done = delta_pt_import($package, $target, array(
                'title'   => !empty($opts['title']),
                'slug'    => !empty($opts['slug']),
                'content' => !empty($opts['content']),
                'status'  => !empty($opts['status']),
                'terms'   => !empty($opts['terms']),
            ));

            $result = is_wp_error($done)
                ? array('ok' => false, 'message' => $done->get_error_message(), 'notes' => array())
                : $done;
        }
    }

    $result['edit'] = get_edit_post_link($target, 'raw');
    set_transient('delta_pt_result_' . get_current_user_id(), $result, 5 * MINUTE_IN_SECONDS);

    wp_safe_redirect(delta_pt_page_url(array('target' => $target)));
    exit;
});
