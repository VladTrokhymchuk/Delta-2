<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="shortcut icon" type="image/svg+xml" href="<?= esc_url( get_template_directory_uri() . '/build/img/favicon.svg' ); ?>">

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
$logo      = delta_header_opt('header_logo');
$logo_src  = !empty($logo['url']) ? $logo['url'] : $theme_uri . '/build/img/logo.png';
$logo_alt  = !empty($logo['alt']) ? $logo['alt'] : '';

// --- Назва й підпис -------------------------------------------------------
$brand   = delta_header_opt('header_brand', get_bloginfo('name'));
$tagline = delta_header_opt('header_tagline', 'Premium hospitality');

// --- Кнопка ---------------------------------------------------------------
$cta        = delta_header_opt('header_button');
$cta_url    = !empty($cta['url'])    ? $cta['url']    : '#';
$cta_title  = !empty($cta['title'])  ? $cta['title']  : __('Забронювати номер', 'delta');
$cta_target = !empty($cta['target']) ? $cta['target'] : '';
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
