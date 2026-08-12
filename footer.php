	</main>

<?php
/**
 * Підвал сайту (Figma: темна секція з логотипом, описом, колонками та копірайтом).
 *
 * Контент — ACF Options «Налаштування теми» → «Підвал»; колонка «Навігація» —
 * WP-меню локації `footer_menu`. Порожні поля просто не виводяться.
 *
 * @see functions-parts/parts/footer.php
 * @see acf-json/group_theme_settings.json
 */
$theme_uri = get_template_directory_uri();

$logo     = delta_header_opt('header_logo');
$logo_src = !empty($logo['url']) ? $logo['url'] : $theme_uri . '/build/img/logo.png';

$brand   = delta_header_opt('header_brand', get_bloginfo('name'));
$about   = delta_header_opt('footer_about');
$slogan  = delta_header_opt('footer_slogan');

$nav_title     = delta_header_opt('footer_nav_title', __('Навігація', 'delta'));
$socials_title = delta_header_opt('footer_socials_title', __('Соціальні мережі', 'delta'));
$socials       = delta_footer_socials();
?>
<footer class="footer" id="footer">
	<div class="container footer__inner">

		<div class="footer__brand-col">
			<a class="footer__brand" href="<?= esc_url( home_url('/') ); ?>">
				<img class="footer__logo"
				     src="<?= esc_url( $logo_src ); ?>"
				     alt=""
				     width="40" height="40"
				     loading="lazy">
				<span class="footer__brand-name"><?= esc_html( $brand ); ?></span>
			</a>

			<?php if ( $about ) : ?>
				<p class="footer__about"><?= nl2br( esc_html( $about ) ); ?></p>
			<?php endif; ?>
		</div>

		<div class="footer__cols">
			<nav class="footer__col" aria-labelledby="footer-nav-title">
				<p class="footer__col-title" id="footer-nav-title"><?= esc_html( $nav_title ); ?></p>
				<?php delta_footer_nav(); ?>
			</nav>

			<?php if ( $socials ) : ?>
				<div class="footer__col">
					<p class="footer__col-title" id="footer-socials-title"><?= esc_html( $socials_title ); ?></p>
					<ul class="footer__menu" aria-labelledby="footer-socials-title">
						<?php foreach ( $socials as $social ) : ?>
							<li>
								<a href="<?= esc_url( $social['url'] ); ?>" target="_blank" rel="noopener">
									<?= esc_html( $social['label'] ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>

		<div class="footer__bottom">
			<p class="footer__copy"><?= esc_html( delta_footer_copyright() ); ?></p>

			<?php if ( $slogan ) : ?>
				<p class="footer__slogan"><?= esc_html( $slogan ); ?></p>
			<?php endif; ?>
		</div>

	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
