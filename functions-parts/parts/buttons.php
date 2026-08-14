<?php
/**
 * Кнопки: значення з ACF → CSS-клас модифікатора.
 *
 * Один словник на всі секції, щоб вигляд кнопки в адмінці й клас у розмітці
 * не розходились. Кожна секція пропонує в select лише доречні їй варіанти.
 *
 * @see src/styles/partials/_bttn.scss
 */

if (!defined('ABSPATH')) exit;

/**
 * @param string $style Ключ зі списку ACF.
 * @return string Класи кнопки, готові до вставки в class="".
 */
function delta_button_class($style = 'primary') {
    $map = array(
        'primary'     => '',                   // базова .bttn — Forest Green
        'secondary'   => 'bttn--secondary',    // прозора з рамкою
        'gold'        => 'bttn--gold',         // Warm Gold (поверх фото)
        'ghost'       => 'bttn--ghost',        // лише текст, на світлому
        'ghost-light' => 'bttn--ghost-light',  // з рамкою, поверх фото
        'light'       => 'bttn--light',        // біла заливка на темному
        'accent'      => 'bttn--accent',       // Copper CTA
    );

    $modifier = $map[$style] ?? '';

    return trim('bttn ' . $modifier);
}
