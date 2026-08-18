/**
 * Дотягування до якоря після повного завантаження.
 *
 * Браузер стрибає на #anchor одразу після парсингу, а частина секцій змінює
 * висоту пізніше: слайдери ініціалізуються після підключення Swiper, карта
 * вантажиться в iframe. Через це перехід на «/#contacts» із меню зупинявся
 * на відгуках — секція локації встигала поїхати нижче.
 *
 * Тому після load повторюємо прокрутку. behavior: 'instant' — щоб сторінка
 * не «доїжджала» на очах (у html стоїть scroll-behavior: smooth), відступ під
 * фіксовану шапку дає scroll-padding-top із _header.scss.
 */
export function initAnchorFix() {
	const { hash } = window.location;
	if (!hash || hash.length < 2) return;

	let target;
	try {
		target = document.getElementById(decodeURIComponent(hash.slice(1)));
	} catch (e) {
		return; // некоректний відсотковий екранований рядок у хеші
	}

	if (!target) return;

	window.addEventListener('load', () => {
		target.scrollIntoView({ block: 'start', behavior: 'instant' });
	}, { once: true });
}
