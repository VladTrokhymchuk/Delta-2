/**
 * Спільний слайдер на Swiper.
 *
 * Розмітка:
 *   <div class="swiper" data-slider data-slider-per-view-desktop="3">
 *     <ul class="swiper-wrapper">…</ul>
 *   </div>
 *   <div class="slider__nav" data-slider-nav>
 *     <div class="slider__progress" data-slider-progress></div>
 *     <div class="slider__arrows">
 *       <button data-slider-prev>…</button>
 *       <button data-slider-next>…</button>
 *     </div>
 *   </div>
 *
 * Налаштування — через data-атрибути, тож нова секція зі слайдером не
 * потребує ані рядка JS:
 *   data-slider-per-view / -tablet / -desktop
 *   data-slider-gap      / -tablet / -desktop
 *
 * Swiper підключається окремим класичним скриптом і лише на сторінках, де
 * слайдер справді є (delta_has_section() у _assets.php), тому беремо його з
 * window і мовчки виходимо, якщо його немає.
 */

export function initSliders() {
  const nodes = document.querySelectorAll('[data-slider]');
  if (!nodes.length) return;

  if (typeof window.Swiper === 'undefined') {
    if (window.console) console.warn('[slider] Swiper не завантажено — список лишається статичним');
    return;
  }

  nodes.forEach((el) => {
    // Керування живе поза .swiper (інакше воно поїхало б разом зі слайдами),
    // тому шукаємо його в межах секції.
    const scope = el.closest('[data-section]') || el.parentElement;
    const nav = scope ? scope.querySelector('[data-slider-nav]') : null;

    const d = el.dataset;
    const num = (value, fallback) => {
      const parsed = parseFloat(value);
      return Number.isFinite(parsed) ? parsed : fallback;
    };

    const swiper = new window.Swiper(el, {
      slidesPerView: num(d.sliderPerView, 1.1),
      spaceBetween: num(d.sliderGap, 24),
      watchOverflow: true, // слайдів менше, ніж влазить → Swiper вимикається сам
      a11y: {
        prevSlideMessage: 'Попередній слайд',
        nextSlideMessage: 'Наступний слайд',
      },
      navigation: nav
        ? {
            prevEl: nav.querySelector('[data-slider-prev]'),
            nextEl: nav.querySelector('[data-slider-next]'),
          }
        : false,
      pagination: nav
        ? {
            el: nav.querySelector('[data-slider-progress]'),
            type: 'progressbar',
          }
        : false,
      breakpoints: {
        768: {
          slidesPerView: num(d.sliderPerViewTablet, 2),
          spaceBetween: num(d.sliderGapTablet, 24),
        },
        992: {
          slidesPerView: num(d.sliderPerViewDesktop, 3),
          spaceBetween: num(d.sliderGapDesktop, 32),
        },
      },
    });

    if (!nav) return;

    // Керування показуємо лише коли гортати справді є що.
    const syncNav = () => {
      nav.hidden = swiper.isLocked === true;
    };

    syncNav();
    swiper.on('lock', syncNav);
    swiper.on('unlock', syncNav);
    swiper.on('breakpoint', syncNav);
  });
}
