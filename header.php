<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<?php
	// Іконка вкладки — емблема готелю (src/img/favicon.png). SVG у списку
	// першим на випадок, якщо колись з'явиться векторна версія: вона різкіша
	// на будь-якому екрані. Кожен файл перевіряємо — посилання на відсутній
	// давало б 404 на кожній сторінці сайту (саме так тут і було).
	$icons = array(
		'favicon.svg' => 'image/svg+xml',
		'favicon.png' => 'image/png',
		'logo.png'    => 'image/png',
	);

	$apple = '';

	foreach ( $icons as $icon => $type ) {
		$path = get_stylesheet_directory() . '/build/img/' . $icon;
		if ( ! is_readable( $path ) ) continue;

		$url = get_stylesheet_directory_uri() . '/build/img/' . $icon;

		printf( '<link rel="icon" type="%s" href="%s">' . "\n", esc_attr( $type ), esc_url( $url ) );

		// iOS ігнорує rel="icon" і бере окремий apple-touch-icon; SVG він теж
		// не приймає, тож віддаємо перший растровий файл зі списку.
		if ( ! $apple && $type === 'image/png' ) $apple = $url;
	}

	if ( $apple ) {
		printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $apple ) );
	}
	?>
	<!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-122481211-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'UA-122481211-1');
    </script>

	<?php wp_head(); ?>

	<script>let ajaxurl = '<?= esc_js( admin_url('admin-ajax.php') ); ?>';</script>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
/**
 * Шапка сайту (Figma: хедер із логотипом, назвою, меню та кнопкою бронювання).
 *
 * Контент — ACF Options «Налаштування теми»; якщо поле порожнє, підставляється
 * значення з макета. Меню — Зовнішній вигляд → Меню, локація `header_menu`.
 *
 * @see functions-parts/parts/header.php
 * @see acf-json/group_theme_settings.json
 */
$theme_uri = get_template_directory_uri();

// --- Логотип --------------------------------------------------------------
$logo      = delta_opt('header_logo');
$logo_src  = !empty($logo['url']) ? $logo['url'] : $theme_uri . '/build/img/logo.png';
$logo_alt  = !empty($logo['alt']) ? $logo['alt'] : '';

// --- Назва й підпис -------------------------------------------------------
$brand   = delta_opt('header_brand', get_bloginfo('name'));
$tagline = delta_opt('header_tagline', 'Premium hospitality');

// --- Кнопка ---------------------------------------------------------------
$cta        = delta_opt('header_button');
$cta_url    = !empty($cta['url'])    ? $cta['url']    : '#';
$cta_title  = !empty($cta['title'])  ? $cta['title']  : __('Забронювати номер', 'delta');
$cta_target = !empty($cta['target']) ? $cta['target'] : '';

// Сторінка номера й архів мають власну форму бронювання, тож кнопка веде до
// неї, а не на головну: інакше з відкритого «Люкса» гість їхав би бронювати
// абстрактний номер. Підміняємо тільки якір #booking — довільне посилання
// з налаштувань лишається як є.
if ( ( is_singular( 'room' ) || is_post_type_archive( 'room' ) ) && str_contains( $cta_url, '#booking' ) ) {
	$cta_url = '#booking';
}
?>
<header class="header" id="header">
	<div class="container header__inner">

		<a class="header__brand" href="<?= esc_url( home_url('/') ); ?>">
			<img class="header__logo"
			     src="<?= esc_url( $logo_src ); ?>"
			     alt="<?= esc_attr( $logo_alt ); ?>"
			     width="48" height="48"
			     fetchpriority="high">
			<span class="header__brand-text">
				<span class="header__brand-name"><?= esc_html( $brand ); ?></span>
				<?php if ( $tagline ) : ?>
					<span class="header__brand-tagline"><?= esc_html( $tagline ); ?></span>
				<?php endif; ?>
			</span>
		</a>

		<nav class="header__nav" id="header-nav" aria-label="<?php esc_attr_e('Головне меню', 'delta'); ?>">
			<?php delta_header_nav(); ?>

			<?php // Дублікат кнопки — видно лише у відкритому мобільному меню. ?>
			<a class="bttn header__cta header__cta--mobile" href="<?= esc_url( $cta_url ); ?>"<?= $cta_target ? ' target="' . esc_attr($cta_target) . '"' : ''; ?>>
				<?= esc_html( $cta_title ); ?>
			</a>
		</nav>

		<div class="header__actions">
			<a class="bttn header__cta" href="<?= esc_url( $cta_url ); ?>"<?= $cta_target ? ' target="' . esc_attr($cta_target) . '"' : ''; ?>>
				<?= esc_html( $cta_title ); ?>
			</a>

			<button class="header__burger"
			        type="button"
			        aria-label="<?php esc_attr_e('Меню', 'delta'); ?>"
			        aria-expanded="false"
			        aria-controls="header-nav">
				<span></span>
				<span></span>
				<span></span>
			</button>
		</div>

	</div>
</header>

<main class="<?= esc_attr( trim( 'main ' . apply_filters( 'delta_main_class', '' ) ) ); ?>">
