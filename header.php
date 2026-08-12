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
 * Шапка сайту — каркас під верстку з Figma.
 *
 * Логотип і CTA беруться з ACF Options («Налаштування теми»), меню — з локації
 * `header_menu` (Зовнішній вигляд → Меню). Розмітка мінімальна: класи й структуру
 * доводимо під макет, коли він з'явиться.
 */
$has_acf  = function_exists('get_field');
$logo     = $has_acf ? get_field('header_logo', 'options') : false;
$logo_src = !empty($logo['url']) ? $logo['url'] : '';
$logo_alt = !empty($logo['alt']) ? $logo['alt'] : get_bloginfo('name');
?>
<header class="header" id="header">
	<div class="container header__inner">
		<a href="<?= esc_url( home_url('/') ); ?>" class="header__logo" aria-label="<?= esc_attr( get_bloginfo('name') ); ?>">
			<?php if ($logo_src) : ?>
				<img src="<?= esc_url( $logo_src ); ?>" alt="<?= esc_attr( $logo_alt ); ?>">
			<?php else : ?>
				<?= esc_html( get_bloginfo('name') ); ?>
			<?php endif; ?>
		</a>

		<nav class="header__nav" aria-label="<?php esc_attr_e('Головне меню', 'delta'); ?>">
			<?php
			wp_nav_menu([
				'theme_location' => 'header_menu',
				'container'      => false,
				'menu_class'     => 'header__menu',
				'fallback_cb'    => false,
				'depth'          => 2,
			]);
			?>
		</nav>

		<button class="header__burger" type="button" aria-label="<?php esc_attr_e('Меню', 'delta'); ?>" aria-expanded="false">
			<span></span>
			<span></span>
		</button>
	</div>
</header>

<main class="<?= esc_attr( trim( 'main ' . apply_filters( 'delta_main_class', '' ) ) ); ?>">
