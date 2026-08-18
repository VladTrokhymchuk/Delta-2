<?php
/**
 * Хелпери відгуків (CPT `review`) + structured data.
 *
 * @see acf-json/group_review.json
 * @see .claude/skills/seo-for-ai
 */

if (!defined('ABSPATH')) exit;

/** Оцінка відгуку 1–5 (за замовчуванням 5). */
function delta_review_rating($post_id) {
    $rating = function_exists('get_field') ? (int) get_field('review_rating', $post_id) : 0;

    return ($rating >= 1 && $rating <= 5) ? $rating : 5;
}

/** Текст відгуку. */
function delta_review_text($post_id) {
    return function_exists('get_field') ? trim((string) get_field('review_text', $post_id)) : '';
}

/** Дата відгуку як Unix-час: поле ACF, інакше дата публікації. */
function delta_review_timestamp($post_id) {
    $raw = function_exists('get_field') ? get_field('review_date', $post_id) : '';

    if ($raw) {
        $date = DateTime::createFromFormat('Ymd', $raw);
        if ($date) return $date->getTimestamp();
    }

    return (int) get_post_timestamp($post_id);
}

/**
 * Зірки: `$rating` заповнених + решта контурних.
 * Обгортку читалка бачить як текст «Оцінка: 5 з 5» — самі іконки приховані.
 */
function delta_review_stars($rating) {
    $rating = max(1, min(5, (int) $rating));

    $out = sprintf(
        '<span class="review-card__stars" role="img" aria-label="%s">',
        esc_attr(sprintf(__('Оцінка: %d з 5', 'delta'), $rating))
    );

    for ($i = 1; $i <= 5; $i++) {
        $out .= delta_icon($i <= $rating ? 'star' : 'star-outline');
    }

    return $out . '</span>';
}

/**
 * JSON-LD для секції відгуків.
 *
 * Виводимо Review-и, прив'язані до готелю, і агреговану оцінку — саме вона
 * дає шанс на зірки у видачі. ВАЖЛИВО: розмітка має відповідати тому, що
 * реально видно на сторінці, а самі відгуки — бути справжніми; вигадані
 * оцінки Google трактує як спам і знімає розмітку по всьому сайту.
 *
 * @param int[] $ids ID відгуків, які виводяться в секції.
 */
function delta_reviews_schema($ids) {
    $reviews = array();
    $sum     = 0;

    foreach ($ids as $id) {
        $text = delta_review_text($id);
        if ($text === '') continue;

        $rating = delta_review_rating($id);
        $sum   += $rating;

        $item = array(
            '@type'         => 'Review',
            'author'        => array('@type' => 'Person', 'name' => wp_strip_all_tags(get_the_title($id))),
            'datePublished' => wp_date('Y-m-d', delta_review_timestamp($id)),
            'reviewBody'    => wp_strip_all_tags($text),
            'reviewRating'  => array(
                '@type'       => 'Rating',
                'ratingValue' => $rating,
                'bestRating'  => 5,
                'worstRating' => 1,
            ),
        );

        $source = function_exists('get_field') ? trim((string) get_field('review_source', $id)) : '';
        if ($source !== '') {
            $item['publisher'] = array('@type' => 'Organization', 'name' => $source);
        }

        $reviews[] = $item;
    }

    if (!$reviews) return;

    $count = count($reviews);

    // Ідентичність готелю (назва, адреса, телефон) описана один раз у графі
    // Yoast — див. functions-parts/_seo.php. Тут лишається той самий @id і
    // тільки оцінки: два вузли з однаковим @id читаються як один об'єкт, а
    // повторювати назву й URL означало б ризик розійтися в даних.
    $schema = array(
        '@context'        => 'https://schema.org',
        '@type'           => 'Hotel',
        '@id'             => delta_schema_hotel_id(),
        'aggregateRating' => array(
            '@type'       => 'AggregateRating',
            'ratingValue' => round($sum / $count, 1),
            'reviewCount' => $count,
            'bestRating'  => 5,
            'worstRating' => 1,
        ),
        'review'          => $reviews,
    );

    echo '<script type="application/ld+json">'
        . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . "</script>\n";
}
