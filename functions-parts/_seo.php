<?php
/**
 * Структуровані дані (JSON-LD) під сутності готелю.
 *
 * Yoast дає базовий граф (Organization / WebSite / WebPage / BreadcrumbList),
 * тому свої вузли не друкуємо окремим <script>, а домішуємо в ЙОГО граф через
 * `wpseo_schema_graph`: два незалежні графи на сторінці конкурували б між собою,
 * і пошуковик сам обирав би, якому вірити.
 *
 * Що додаємо:
 *   • Hotel        — картка закладу (адреса, телефон, час заїзду, діапазон цін);
 *   • HotelRoom    — номер із площею, зручностями та ціною (Offer).
 *
 * Схема мусить відповідати видимому контенту сторінки — усе тут береться
 * з тих самих полів, що й розмітка.
 *
 * @see .claude/skills/seo-optimization
 * @see functions-parts/parts/rooms.php
 */

if (!defined('ABSPATH')) exit;

add_filter('wpseo_schema_graph', 'delta_schema_graph', 20);
function delta_schema_graph($graph) {
    if (!is_array($graph)) return $graph;

    if (is_singular('room')) {
        $graph[] = delta_schema_hotel();
        $graph[] = delta_schema_room(get_queried_object_id());
    } elseif (is_front_page() || is_post_type_archive('room')) {
        $graph[] = delta_schema_hotel();
    }

    return array_values(array_filter($graph));
}

/**
 * Ідентифікатор закладу — один на весь сайт, щоб номери посилались на нього,
 * а не описували готель заново на кожній сторінці.
 */
function delta_schema_hotel_id() {
    return home_url('/#hotel');
}

/**
 * Картка готелю. Адреса, телефон і пошта — з «Налаштування теми» → Контакти,
 * час заїзду/виїзду — звідти ж, щоб схема не розійшлася зі сторінкою правил.
 */
function delta_schema_hotel() {
    $node = array(
        '@type' => 'Hotel',
        '@id'   => delta_schema_hotel_id(),
        'name'  => delta_opt('header_brand', get_bloginfo('name')),
        'url'   => home_url('/'),
    );

    if ($address = delta_opt('footer_address')) {
        $node['address'] = array(
            '@type'           => 'PostalAddress',
            'streetAddress'   => $address,
            'addressLocality' => 'Київ',
            'addressCountry'  => 'UA',
        );
    }

    if ($phone = delta_opt('footer_phone')) $node['telephone'] = $phone;
    if ($email = delta_opt('footer_email')) $node['email']     = $email;

    $node['checkinTime']  = delta_opt('hotel_checkin', '14:00');
    $node['checkoutTime'] = delta_opt('hotel_checkout', '12:00');

    // Діапазон цін рахуємо з самих номерів — інакше він застаріє першою ж
    // зміною прайса, і пошук покаже ціну, якої на сайті вже немає.
    if ($range = delta_schema_price_range()) {
        $node['priceRange'] = $range;
    }

    return $node;
}

/**
 * «850–1550 UAH» із опублікованих номерів. Порожньо — якщо цін ще немає.
 */
function delta_schema_price_range() {
    $prices = array();

    foreach (get_posts(array(
        'post_type'      => 'room',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    )) as $id) {
        $price = (float) get_field('room_price', $id);
        if ($price > 0) $prices[] = $price;
    }

    if (!$prices) return '';

    $min = number_format(min($prices), 0, '.', '');
    $max = number_format(max($prices), 0, '.', '');

    // Фігурні дужки обов'язкові: тире тут — багатобайтове, і PHP затягнув би
    // його в назву змінної ($min–), тобто рядок вийшов би порожнім.
    return $min === $max ? "{$min} UAH" : "{$min}–{$max} UAH";
}

/**
 * Номер: площа, зручності та ціна за добу.
 */
function delta_schema_room($room_id) {
    $node = array(
        '@type'            => 'HotelRoom',
        '@id'              => get_permalink($room_id) . '#room',
        'name'             => get_the_title($room_id),
        'url'              => get_permalink($room_id),
        'containedInPlace' => array('@id' => delta_schema_hotel_id()),
    );

    if ($excerpt = get_the_excerpt($room_id)) {
        $node['description'] = wp_strip_all_tags($excerpt);
    }

    if ($image = get_the_post_thumbnail_url($room_id, 'large')) {
        $node['image'] = $image;
    }

    // MTK — код квадратного метра в UN/CEFACT, який очікує schema.org.
    if ($area = (float) get_field('room_area', $room_id)) {
        $node['floorSize'] = array(
            '@type'    => 'QuantitativeValue',
            'value'    => $area,
            'unitCode' => 'MTK',
        );
    }

    foreach (delta_room_amenities($room_id) as $amenity) {
        $node['amenityFeature'][] = array(
            '@type' => 'LocationFeatureSpecification',
            'name'  => $amenity['title'],
            'value' => true,
        );
    }

    if ($price = (float) get_field('room_price', $room_id)) {
        $node['offers'] = array(
            '@type'         => 'Offer',
            'price'         => number_format($price, 0, '.', ''),
            'priceCurrency' => 'UAH',
            'availability'  => 'https://schema.org/InStock',
            'url'           => get_permalink($room_id),
        );
    }

    return $node;
}
