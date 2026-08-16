<?php
/**
 * Section: Location — «Ваш шлях до релаксу»: текст і контакти зліва, карта справа.
 * Поля з ACF Flexible Content layout "location" (acf-json/group_page_builder.json).
 *
 * Адреса, телефони й email порожні — беруться з «Налаштування теми» → Контакти,
 * щоб не розходились із підвалом.
 *
 * @see functions-parts/parts/icons.php (delta_icon)
 * @see src/styles/sections/_location.scss
 */

if (!defined('ABSPATH')) exit;

$overline = get_sub_field('location_overline');
$title    = get_sub_field('location_title');
$text     = get_sub_field('location_text');

$address = get_sub_field('location_address') ?: delta_opt('footer_address');
$email   = get_sub_field('location_email')   ?: delta_opt('footer_email');

$phones = array();
foreach ((array) get_sub_field('location_phones') as $row) {
	$number = trim((string) ($row['number'] ?? ''));
	if ($number !== '') $phones[] = $number;
}
if (!$phones && ($fallback = delta_opt('footer_phone'))) {
	$phones[] = $fallback;
}

$map_src = delta_gmap_src(get_sub_field('location_map_embed'));

// Один список замість чотирьох майже однакових блоків розмітки.
// Адреса без href — це не клікабельний контакт, а просто рядок.
$contacts = array();

if ($address) {
	$contacts[] = array('icon' => 'map-pin', 'text' => $address, 'href' => '');
}
foreach ($phones as $phone) {
	// tel: не терпить пробілів, дужок і дефісів — лишаємо цифри та провідний «+».
	$contacts[] = array('icon' => 'phone', 'text' => $phone, 'href' => 'tel:' . preg_replace('/[^\d+]/', '', $phone));
}
if ($email) {
	$contacts[] = array('icon' => 'mail', 'text' => $email, 'href' => 'mailto:' . sanitize_email($email));
}

if (!$title && !$text && !$map_src) return;
?>
<section class="section section--location" data-section="location">
	<div class="container location__inner">

		<div class="location__content">
			<?php if ($overline) : ?>
				<p class="overline location__overline"><?= esc_html($overline); ?></p>
			<?php endif; ?>

			<?php if ($title) : ?>
				<h2 class="location__title"><?= esc_html($title); ?></h2>
			<?php endif; ?>

			<?php if ($text) : ?>
				<p class="location__text"><?= nl2br(esc_html($text)); ?></p>
			<?php endif; ?>

			<?php if ($contacts) : ?>
				<ul class="location__contacts">
					<?php foreach ($contacts as $contact) : ?>
						<li class="location__contact">
							<span class="location__contact-icon">
								<?= delta_icon($contact['icon']); // phpcs:ignore WordPress.Security.EscapeOutput — власний SVG теми ?>
							</span>

							<?php if ($contact['href']) : ?>
								<a class="location__contact-link" href="<?= esc_attr($contact['href']); ?>">
									<?= esc_html($contact['text']); ?>
								</a>
							<?php else : ?>
								<span class="location__contact-text"><?= esc_html($contact['text']); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<?php if ($map_src) : ?>
			<div class="location__media">
				<?php
				// Свій <iframe>, а не вставлений із поля: у коді Google зашиті
				// width/height 600×450, які ламають сітку, і немає title —
				// без нього скрінрідер оголошує фрейм безіменним.
				?>
				<iframe class="location__map"
				        src="<?= esc_url($map_src); ?>"
				        title="<?= esc_attr(sprintf(__('%s на карті', 'delta'), get_bloginfo('name'))); ?>"
				        loading="lazy"
				        referrerpolicy="strict-origin-when-cross-origin"
				        allowfullscreen></iframe>
			</div>
		<?php endif; ?>

	</div>
</section>
