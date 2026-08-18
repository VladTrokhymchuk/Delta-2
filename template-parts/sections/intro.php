<?php
/**
 * Section: Intro — вступний текст по центру.
 * Поля з ACF Flexible Content layout "intro" (acf-json/group_page_builder.json).
 *
 * @see src/styles/sections/_intro.scss
 */

if (!defined('ABSPATH')) exit;

$overline = get_sub_field('intro_overline');
$title    = get_sub_field('intro_title');
$lead     = get_sub_field('intro_lead');
$text     = get_sub_field('intro_text');

if (!$title && !$lead) return; // порожню секцію не рендеримо
?>
<section class="section section--intro" data-section="intro"<?= delta_section_id_attr('intro'); ?>>
	<div class="container intro__inner">

		<?php if ($overline) : ?>
			<p class="overline intro__overline"><?= esc_html($overline); ?></p>
		<?php endif; ?>

		<?php if ($title) : ?>
			<h2 class="intro__title"><?= esc_html($title); ?></h2>
		<?php endif; ?>

		<?php // Декоративний розділювач — від скрінрідерів ховаємо. ?>
		<span class="intro__ornament" aria-hidden="true"></span>

		<?php if ($lead) : ?>
			<p class="intro__lead"><?= nl2br(esc_html($lead)); ?></p>
		<?php endif; ?>

		<?php if ($text) : ?>
			<p class="intro__text"><?= nl2br(esc_html($text)); ?></p>
		<?php endif; ?>

	</div>
</section>
