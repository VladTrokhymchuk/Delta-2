<?php
/**
 * Section: Text — довгий текст документа (правила, політика, оферта).
 * Поля з ACF Flexible Content layout "text" (acf-json/group_page_builder.json).
 *
 * Єдина секція конструктора з WYSIWYG: усі інші мають фіксовану структуру,
 * а тут її задає сам редактор — заголовки, абзаци, нумеровані списки.
 * Тому й ширина колонки обмежена: суцільний юридичний текст на 1280px
 * читати неможливо.
 *
 * @see src/styles/sections/_text.scss
 */

if (!defined('ABSPATH')) exit;

$overline = get_sub_field('text_overline');
$title    = get_sub_field('text_title');
$content  = get_sub_field('text_content');

if (!$title && !$content) return;
?>
<section class="section section--text" data-section="text"<?= delta_section_id_attr('text'); ?>>
	<div class="container">

		<?php if ($overline || $title) : ?>
			<div class="text__head">
				<?php if ($overline) : ?>
					<p class="overline text__overline"><?= esc_html($overline); ?></p>
				<?php endif; ?>

				<?php if ($title) : ?>
					<h2 class="text__title"><?= esc_html($title); ?></h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ($content) : ?>
			<?php // .text__content — спільні стилі WYSIWYG-тексту з _typography.scss. ?>
			<div class="text__content text__body">
				<?= wp_kses_post($content); ?>
			</div>
		<?php endif; ?>

	</div>
</section>
