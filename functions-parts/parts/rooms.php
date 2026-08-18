<?php
/**
 * Хелпери номерів (CPT `room`).
 *
 * @see acf-json/group_room.json
 */

if (!defined('ABSPATH')) exit;

/**
 * Ціна номера у форматі «₴3,200 / ніч». Порожня ціна → ''.
 */
function delta_room_price($post_id = null) {
    if (!function_exists('get_field')) return '';

    $price = get_field('room_price', $post_id ?: get_the_ID());
    if (!$price) return '';

    return sprintf(
        /* translators: %s — відформатована ціна з валютою */
        __('%s / ніч', 'delta'),
        '₴' . number_format((float) $price, 0, ',', ',')
    );
}

/**
 * Статус номера для бейджа: ['label' => ..., 'modifier' => ...].
 * Невідомий/порожній статус → бейджа немає.
 */
function delta_room_status($post_id = null) {
    if (!function_exists('get_field')) return null;

    $status = get_field('room_status', $post_id ?: get_the_ID());

    $map = array(
        'available' => array('label' => __('вільний', 'delta'),  'modifier' => 'badge--available'),
        'busy'      => array('label' => __('зайнятий', 'delta'), 'modifier' => 'badge--busy'),
    );

    return $map[$status] ?? null;
}

/**
 * Ціна для розгорнутого списку: ['amount' => '7 800 ₴', 'period' => '/ ніч'].
 * Порожня ціна → null.
 *
 * Окремо від delta_room_price(): у списку ціна набирається двома кеглями
 * (сума крупно, «/ ніч» дрібно), тож одним рядком її не віддати. Формат теж
 * інший — у макеті сторінки «Номери» гривня стоїть після суми, а розряди
 * розділені пробілом. Пробіл нерозривний, щоб «7 800» не переносилось.
 */
function delta_room_price_parts($post_id = null) {
    if (!function_exists('get_field')) return null;

    $price = get_field('room_price', $post_id ?: get_the_ID());
    if (!$price) return null;

    return array(
        'amount' => number_format((float) $price, 0, ',', "\xC2\xA0") . "\xC2\xA0₴",
        'period' => __('/ ніч', 'delta'),
    );
}

/**
 * Ціна для компактної картки: ['amount' => '₴3 800', 'period' => '/ ніч'].
 * Порожня ціна → null.
 *
 * Третій формат ціни в темі, і кожен із макета: у картці головної гривня
 * приклеєна до числа («₴3,200 / ніч»), у списку номерів стоїть після суми
 * («7 800 ₴»), а тут — перед сумою й окремо від дрібного «/ ніч».
 * Пробіли нерозривні, щоб число не ламалось переносом.
 */
function delta_room_price_card($post_id = null) {
    if (!function_exists('get_field')) return null;

    $price = get_field('room_price', $post_id ?: get_the_ID());
    if (!$price) return null;

    return array(
        'amount' => '₴' . number_format((float) $price, 0, ',', "\xC2\xA0"),
        'period' => __('/ ніч', 'delta'),
    );
}

/**
 * Характеристики для рядка списку: [['icon' => 'bed-double', 'text' => 'King Bed'], ...].
 *
 * Джерело — репітер `room_specs` (іконка + текст). Якщо його не заповнили,
 * показуємо хоч площу з окремого поля — рядок із однією характеристикою
 * виглядає краще за порожнє місце під описом.
 */
function delta_room_specs($post_id = null) {
    if (!function_exists('get_field')) return array();

    $post_id = $post_id ?: get_the_ID();
    $specs   = array();

    foreach ((array) get_field('room_specs', $post_id) as $row) {
        $text = trim((string) ($row['text'] ?? ''));
        if ($text === '') continue;

        $specs[] = array(
            'icon' => (string) ($row['icon'] ?? ''),
            'text' => $text,
        );
    }

    if (!$specs && ($area = get_field('room_area', $post_id))) {
        $specs[] = array(
            'icon' => 'ruler',
            /* translators: %s — площа номера в м² */
            'text' => sprintf(__('%s м²', 'delta'), $area),
        );
    }

    return $specs;
}

/**
 * Рядки картки «Характеристики номера»: [['icon','label','value'], ...].
 *
 * Порожні рядки репітера пропускаємо: менеджер часто лишає останній рядок
 * незаповненим, і без фільтра картка отримала б пусту смугу з іконкою.
 */
function delta_room_features($post_id = null) {
    if (!function_exists('get_field')) return array();

    $features = array();

    foreach ((array) get_field('room_features', $post_id ?: get_the_ID()) as $row) {
        $label = trim((string) ($row['label'] ?? ''));
        $value = trim((string) ($row['value'] ?? ''));
        if ($label === '' && $value === '') continue;

        $features[] = array(
            'icon'  => (string) ($row['icon'] ?? ''),
            'label' => $label,
            'value' => $value,
        );
    }

    return $features;
}

/**
 * Зручності номера: [['icon' => 'wifi', 'title' => 'Wi-Fi'], ...].
 *
 * Ключ саме `title`, а не `text`: у такому вигляді список приймає секція
 * template-parts/sections/amenities.php — вона ж малює цей блок і в конструкторі.
 */
function delta_room_amenities($post_id = null) {
    if (!function_exists('get_field')) return array();

    $items = array();

    foreach ((array) get_field('room_amenities', $post_id ?: get_the_ID()) as $row) {
        $text = trim((string) ($row['text'] ?? ''));
        if ($text === '') continue;

        $items[] = array(
            'icon'  => (string) ($row['icon'] ?? ''),
            'title' => $text,
        );
    }

    return $items;
}

/**
 * Інші номери готелю — добірка для низу сторінки номера.
 *
 * «Схожі» тут означає «решта номерів у тому ж порядку, що в адмінці»:
 * номерів у готелі десяток, і алгоритмічна схожість (за ціною чи типом)
 * дала б менеджеру менше контролю, ніж звичний порядок сортування.
 *
 * @return int[] ID номерів без поточного.
 */
function delta_room_similar($post_id = null, $limit = 4) {
    $post_id = $post_id ?: get_the_ID();

    return get_posts(array(
        'post_type'      => 'room',
        'post_status'    => 'publish',
        'post__not_in'   => array((int) $post_id),
        'posts_per_page' => (int) $limit,
        'orderby'        => array('menu_order' => 'ASC', 'date' => 'DESC'),
        'fields'         => 'ids',
        // Меню тут ні до чого, а без цього WP тягне ще один запит на мета.
        'no_found_rows'  => true,
    ));
}

/**
 * Форма бронювання для номера. Три рівні, від вужчого до ширшого:
 * власна форма номера → спільна форма сторінок номера → загальна форма сайту.
 *
 * Середній рівень потрібен, бо на сторінці номера форма коротша (імʼя та
 * телефон): категорію обирати нема сенсу — номер уже відкритий, і в лист
 * його підставляє сам CF7 через [_post_title].
 *
 * @return int ID форми CF7 або 0.
 */
function delta_room_form_id($post_id = null) {
    if (!function_exists('get_field')) return 0;

    $own = (array) get_field('room_form', $post_id ?: get_the_ID());
    $id  = (int) reset($own);

    foreach (array('booking_room_form', 'booking_form') as $option) {
        if ($id) break;
        $shared = (array) delta_opt($option);
        $id     = (int) reset($shared);
    }

    return $id;
}

/**
 * Куди веде кнопка «Забронювати» у списку номерів.
 * Пріоритет: поле секції → «Налаштування теми» → якір на форму цієї ж сторінки.
 *
 * @param array|null $link ACF-поле link із секції.
 * @return array ['url' => ..., 'target' => ...]
 */
function delta_rooms_book_link($link = null) {
    if (empty($link['url'])) {
        $link = delta_opt('rooms_page_book_link');
    }

    if (empty($link['url'])) {
        return array('url' => '#booking', 'target' => '');
    }

    return array(
        'url'    => $link['url'],
        'target' => $link['target'] ?? '',
    );
}

/**
 * Короткий опис для картки: уривок, а якщо його немає — з контенту.
 */
function delta_room_excerpt($post_id = null, $words = 24) {
    $post_id = $post_id ?: get_the_ID();

    $excerpt = get_the_excerpt($post_id);
    if (!$excerpt) return '';

    return wp_trim_words($excerpt, $words, '…');
}

/**
 * Архів /rooms/ — уся добірка одним списком і в тому ж порядку, що в адмінці.
 *
 * Без цього WP віддав би 10 номерів на сторінку з пагінацією, а сортував би
 * за датою публікації — тобто порядок у списку не збігався б із порядком,
 * який менеджер виставив перетягуванням («Порядок» / page-attributes).
 */
add_action('pre_get_posts', function ($query) {
    if (is_admin() || !$query->is_main_query() || !$query->is_post_type_archive('room')) {
        return;
    }

    $query->set('posts_per_page', -1);
    $query->set('orderby', array('menu_order' => 'ASC', 'date' => 'DESC'));
});

/**
 * Список номерів в адмінці — теж за полем «Порядок», а не за датою.
 *
 * Інакше менеджер бачить один порядок у себе, а сайт віддає інший, і
 * незрозуміло, яке число куди рухати. Сортування ставимо лише тоді, коли
 * користувач не клікнув по колонці сам (`orderby` в запиті порожній).
 */
add_action('pre_get_posts', function ($query) {
    if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'room') {
        return;
    }
    if (!empty($_GET['orderby'])) {
        return;
    }

    $query->set('orderby', array('menu_order' => 'ASC', 'date' => 'DESC'));
});
