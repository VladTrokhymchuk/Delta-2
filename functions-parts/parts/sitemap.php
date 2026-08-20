<?php
/**
 * Чистка XML-карти сайту (Yoast).
 *
 * Карту генерує Yoast — свою не робимо. Тут лише прибираємо з неї те, чого на
 * цьому сайті бути не повинно, бо кожен зайвий URL у карті — це запрошення
 * пошуковику витратити краулінговий бюджет на сторінку без цінності.
 *
 * Важливо: усе це — фільтри в темі, а не галочки в адмінці Yoast. Налаштування
 * живуть у базі й на прод не переїжджають разом із кодом, а фільтри переїжджають.
 *
 * @see .claude/skills/seo-optimization
 */

if (!defined('ABSPATH')) exit;

/**
 * Дефолтні Записи — геть із карти.
 *
 * Правило проєкту: контент лише в CPT, дефолтні Записи не використовуються.
 * У базі з часів старої теми лишився один запис «Hello world!», і Yoast
 * слухняно віддавав його окремим post-sitemap.xml.
 */
add_filter('wpseo_sitemap_exclude_post_type', 'delta_sitemap_exclude_posts', 10, 2);
function delta_sitemap_exclude_posts($excluded, $post_type) {
    return $post_type === 'post' ? true : $excluded;
}

/**
 * Архіви авторів — геть із карти.
 *
 * Сайт одноосібний: архів автора дублює головну добіркою тих самих записів і
 * заодно світить логін адміністратора. Yoast прибирає його з карти, щойно
 * на фільтрі повертається порожній список користувачів.
 */
add_filter('wpseo_sitemap_exclude_author', 'delta_sitemap_exclude_authors');
function delta_sitemap_exclude_authors($users) {
    return array();
}

/**
 * Ті самі сторінки — ще й noindex, бо виключення з карти саме по собі нічого
 * не закриває: у індекс потрапляють і сторінки, знайдені за посиланнями.
 */
add_filter('wpseo_robots_array', 'delta_sitemap_noindex_junk');
function delta_sitemap_noindex_junk($robots) {
    if (is_author() || is_singular('post') || is_home()) {
        $robots['index'] = 'noindex';
    }

    return $robots;
}
