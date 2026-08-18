<?php
/**
 * Section: Booking CTA — темна панель із закликом та формою швидкого бронювання.
 * Поля з ACF Flexible Content layout "booking-cta" (acf-json/group_page_builder.json).
 *
 * Форма не хардкодиться: поле `booking_cta_form` — відношення до запису
 * Contact Form 7, тож менеджер міняє форму без правок теми.
 * Телефони (репітер, їх може бути кілька) та email порожні — беруться з
 * «Налаштування теми» → Контакти, щоб контакти не розходились між підвалом
 * і секцією.
 *
 * Три режими роботи:
 *   • конструктор — поля рядка Flexible Content (get_sub_field);
 *   • архів номерів /rooms/ — $args['source'] = 'options', і весь текст
 *     та форма приходять з «Налаштування теми» → Бронювання;
 *   • сторінка номера — $args['source'] = 'room' + $args['room'] = ID.
 *     Заголовок збирається з шаблону з %s, замість контактів іде ціна
 *     номера, а форма береться з поля номера (у кожного своя).
 *
 * @see functions-parts/parts/icons.php (delta_icon)
 * @see functions-parts/parts/rooms.php (delta_room_form_id, delta_room_price_parts)
 * @see src/styles/sections/_booking-cta.scss
 */

if (!defined('ABSPATH')) exit;

$source       = $args['source'] ?? '';
$from_options = $source === 'options';
$from_room    = $source === 'room';

// --- Ціна номера в панелі (лише на сторінці номера) --------------------------
$price = null;

if ($from_room) {
	$room_id = (int) ($args['room'] ?? get_the_ID());

	$overline   = delta_opt('booking_overline');
	$title      = sprintf(
		/* translators: %s — назва номера */
		delta_opt('room_page_booking_title', __('Забронювати номер «%s»', 'delta')),
		get_the_title($room_id)
	);
	$text       = get_field('room_booking_text', $room_id) ?: delta_opt('booking_text');
	$form_title = delta_opt('booking_form_title');
	$rows       = array(); // замість контактів тут ціна
	$email      = '';
	$form_ids   = array(delta_room_form_id($room_id));

	$price = delta_room_price_parts($room_id);
} elseif ($from_options) {
	$overline   = delta_opt('booking_overline');
	$title      = delta_opt('booking_title');
	$text       = delta_opt('booking_text');
	$form_title = delta_opt('booking_form_title');
	$rows       = array(); // телефони в опціях не дублюємо — вони у вкладці «Контакти»
	$email      = '';
	$form_ids   = (array) delta_opt('booking_form');
} else {
	$overline   = get_sub_field('booking_cta_overline');
	$title      = get_sub_field('booking_cta_title');
	$text       = get_sub_field('booking_cta_text');
	$form_title = get_sub_field('booking_cta_form_title');
	$rows       = (array) get_sub_field('booking_cta_phones');
	$email      = get_sub_field('booking_cta_email');
	$form_ids   = (array) get_sub_field('booking_cta_form');
}

$phones = array();
foreach ($rows as $row) {
	$number = trim((string) ($row['number'] ?? ''));
	if ($number !== '') $phones[] = $number;
}

// На сторінці номера місце контактів займає ціна — інакше під нею опинився б
// ще й телефон із підвалу, і акцент панелі розпорошився б на два блоки.
if (!$from_room) {
	if (!$phones && ($fallback = delta_opt('footer_phone'))) {
		$phones[] = $fallback;
	}

	$email = $email ?: delta_opt('footer_email');
}

// Relationship повертає масив ID — беремо перший (max = 1).
$form_id = (int) reset($form_ids);

$form = ($form_id && class_exists('WPCF7_ContactForm'))
	? WPCF7_ContactForm::get_instance($form_id)
	: null;

if (!$title && !$text && !$form) return;
?>
<section class="section section--booking-cta" data-section="booking-cta"<?= delta_section_id_attr('booking-cta'); ?>>
	<div class="container">
		<div class="booking-cta__panel">

			<div class="booking-cta__content">
				<?php if ($overline) : ?>
					<p class="overline booking-cta__overline"><?= esc_html($overline); ?></p>
				<?php endif; ?>

				<?php if ($title) : ?>
					<h2 class="booking-cta__title"><?= esc_html($title); ?></h2>
				<?php endif; ?>

				<?php if ($text) : ?>
					<p class="booking-cta__text"><?= nl2br(esc_html($text)); ?></p>
				<?php endif; ?>

				<?php if ($price) : ?>
					<p class="booking-cta__price">
						<span class="booking-cta__price-label"><?php esc_html_e('від', 'delta'); ?></span>
						<span class="booking-cta__price-value"><?= esc_html($price['amount']); ?></span>
						<span class="booking-cta__price-period"><?= esc_html($price['period']); ?></span>
					</p>
				<?php endif; ?>

				<?php if ($phones || $email) : ?>
					<ul class="booking-cta__contacts">
						<?php foreach ($phones as $phone) :
							// tel: не терпить пробілів, дужок і дефісів — лишаємо цифри та провідний «+».
							$phone_href = preg_replace('/[^\d+]/', '', $phone);
							?>
							<li class="booking-cta__contact">
								<span class="booking-cta__contact-icon">
									<?= delta_icon('phone'); // phpcs:ignore WordPress.Security.EscapeOutput — власний SVG теми ?>
								</span>
								<a class="booking-cta__contact-link" href="tel:<?= esc_attr($phone_href); ?>">
									<?= esc_html($phone); ?>
								</a>
							</li>
						<?php endforeach; ?>

						<?php if ($email) : ?>
							<li class="booking-cta__contact">
								<span class="booking-cta__contact-icon">
									<?= delta_icon('mail'); // phpcs:ignore WordPress.Security.EscapeOutput — власний SVG теми ?>
								</span>
								<a class="booking-cta__contact-link" href="mailto:<?= esc_attr(sanitize_email($email)); ?>">
									<?= esc_html($email); ?>
								</a>
							</li>
						<?php endif; ?>
					</ul>
				<?php endif; ?>
			</div>

			<?php if ($form) : ?>
				<div class="booking-cta__card">
					<?php if ($form_title) : ?>
						<h3 class="booking-cta__form-title"><?= esc_html($form_title); ?></h3>
					<?php endif; ?>

					<?php
					// hash — актуальний формат ідентифікатора CF7 (числовий id застарів).
					echo do_shortcode(sprintf(
						'[contact-form-7 id="%s" title="%s" html_class="booking-form"]',
						esc_attr($form->hash()),
						esc_attr($form->title())
					));
					?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
