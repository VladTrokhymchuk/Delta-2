<?php
/**
 * 301-редіректи зі старої структури URL.
 *
 * Стара тема тримала номери в CPT `news` зі слагом /news-post/{slug}/, а
 * бронювання — окремою сторінкою /zabronyuvati/. Нова тема ні того типу, ні
 * тієї сторінки не має, тож без редіректів усі старі адреси (а вони в індексі
 * Google, у старому sitemap.xml і в зовнішніх посиланнях) віддавали б 404.
 *
 * Правило вимикається, коли міграція не потрібна:
 * define('DELTA_LEGACY_REDIRECTS', false) у wp-config.php.
 *
 * @see functions-parts/_post-types-registration.php
 */

if (!defined('ABSPATH')) exit;

if (defined('DELTA_LEGACY_REDIRECTS') && !DELTA_LEGACY_REDIRECTS) return;

/**
 * Старий слаг номера → новий.
 *
 * Зіставляти «за тим самим слагом» тут не можна: cyr2lat змінив транслітерацію
 * закінчення «ий» (було `-ij`, стало `-yj`), тож збігаються лише два слаги з
 * п'яти. Без цієї мапи три найважчі сторінки — «Люкс» і два «Економи» —
 * втратили б свою вагу, звалившись у загальний фолбек на архів.
 *
 * Слаги старого сайту фіксовані (їх більше ніхто не змінить), нові — беруться
 * з бази на льоту, тож перейменування номера в адмінці мапу не зламає:
 * спрацює фолбек на пошук за старим слагом, а далі на архів.
 */
function delta_legacy_room_slugs() {
    return apply_filters('delta_legacy_room_slugs', array(
        'lyuks-odnokimnatnij' => 'lyuks-odnokimnatnyj',
        'ekonom-odnomisnij'   => 'ekonom-odnomisnyj',
        'ekonom-dvomisnij'    => 'ekonom-dvomisnyj',
        'komfort-dabl'        => 'komfort-dabl',
        'komfort-tvin'        => 'komfort-tvin',
    ));
}

/**
 * Старий шлях (без слешів по краях) → куди вести.
 *
 * Бронювання більше не окрема сторінка, а секція головної, тож ведемо на її
 * якір: користувач зі старого посилання одразу бачить форму, а не мусить її
 * шукати. `/pravila/` тут немає навмисно — ця сторінка існує й у новій темі
 * з тим самим слагом.
 */
function delta_legacy_paths() {
    return apply_filters('delta_legacy_paths', array(
        'zabronyuvati' => home_url('/#booking'),
    ));
}

add_action('template_redirect', 'delta_legacy_redirects');
function delta_legacy_redirects() {
    if (!is_404()) return;

    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $path = strtolower(trim((string) $path, '/'));
    if ($path === '') return;

    // 1. Точні збіги окремих сторінок.
    $paths = delta_legacy_paths();
    if (isset($paths[$path])) {
        delta_legacy_go($paths[$path]);
    }

    // 2. Далі — лише старий префікс номерів.
    $parts = explode('/', $path);
    if ($parts[0] !== 'news-post') return;

    // /news-post/ (старий архів) → /rooms/
    $slug = sanitize_title($parts[1] ?? '');
    if ($slug === '') {
        delta_legacy_go_archive();
    }

    // /news-post/{slug}/ → номер. Спершу за мапою, потім — за самим слагом
    // (на випадок номерів, доданих уже після міграції).
    $map        = delta_legacy_room_slugs();
    $candidates = array_unique(array_filter(array($map[$slug] ?? '', $slug)));

    foreach ($candidates as $candidate) {
        $room = get_posts(array(
            'post_type'      => 'room',
            'post_status'    => 'publish',
            'name'           => $candidate,
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ));

        if ($room) {
            delta_legacy_go(get_permalink($room[0]));
        }
    }

    // Номер зняли з публікації — ведемо на архів, а не в 404.
    delta_legacy_go_archive();
}

/** Фолбек: архів номерів, а якщо його немає — головна. */
function delta_legacy_go_archive() {
    delta_legacy_go(get_post_type_archive_link('room') ?: home_url('/'));
}

/** Один вихід: 301 + exit, щоб не дублювати пару рядків у кожній гілці. */
function delta_legacy_go($url) {
    wp_safe_redirect($url, 301);
    exit;
}
