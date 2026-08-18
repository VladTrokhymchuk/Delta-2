/**
 * Section: Gallery — лайтбокс на сітці фото.
 *
 * Бібліотека fslightbox підключається окремим <script> із build/js/libs/
 * і тільки там, де галерея справді є (див. functions-parts/_assets.php),
 * тож у бандл вона не імпортується: 43 КБ на кожну сторінку не потрібні.
 * Звідси й перевірка window.FsLightbox — без скрипта модуль просто мовчить,
 * а посилання лишаються робочими (ведуть на повний розмір).
 *
 * @see template-parts/sections/gallery.php
 * @see src/styles/modules/_lightbox.scss
 */

export function initGallery() {
	const grids = document.querySelectorAll('[data-gallery]');
	if (!grids.length || typeof window.FsLightbox === 'undefined') return;

	grids.forEach((grid) => {
		const links = Array.from(grid.querySelectorAll('.gallery__link'));
		if (!links.length) return;

		// Один екземпляр на сітку: так стрілки гортають саме її фото, а не
		// всі, що є на сторінці (на сторінці номера галерея одна, але секцію
		// можна поставити конструктором двічі).
		const lightbox = new window.FsLightbox();
		lightbox.props.sources = links.map((link) => link.href);
		// Типи задаємо явно — інакше бібліотека визначає їх запитом на кожен файл.
		lightbox.props.types = links.map(() => 'image');

		links.forEach((link, index) => {
			link.addEventListener('click', (event) => {
				event.preventDefault();
				lightbox.open(index);
			});
		});
	});
}
