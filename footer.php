	</main>

<?php
/**
 * Підвал сайту — каркас під верстку з Figma.
 *
 * Контент (контакти, соцмережі, копірайт) редагується в ACF Options
 * («Налаштування теми»); меню — локація `footer_menu`.
 */
?>
<footer class="footer" id="footer">
	<div class="container footer__inner">
		<nav class="footer__nav" aria-label="<?php esc_attr_e('Меню підвалу', 'delta'); ?>">
			<?php
			wp_nav_menu([
				'theme_location' => 'footer_menu',
				'container'      => false,
				'menu_class'     => 'footer__menu',
				'fallback_cb'    => false,
				'depth'          => 1,
			]);
			?>
		</nav>

		<p class="footer__copy">
			&copy; <?= esc_html( date_i18n('Y') ); ?> <?= esc_html( get_bloginfo('name') ); ?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
