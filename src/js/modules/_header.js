/**
 * Шапка: фіксація при скролі + мобільне меню (бургер → панель справа).
 *
 * Стан меню тримається класом `menu-open` на <body> — CSS показує панель,
 * вмикає затемнення й блокує скрол (див. _base.scss / _header.scss).
 * Сама шапка зафіксована в CSS; JS лише вішає `is-scrolled` для тіні.
 */

const DESKTOP_BREAKPOINT = 992; // = $sm-pc

export function initHeader() {
  const header = document.querySelector('.header');
  if (!header) return;

  initScrolledState(header);

  const burger = header.querySelector('.header__burger');
  const nav = header.querySelector('.header__nav');
  if (!burger || !nav) return;

  const body = document.body;
  let scrollY = 0;

  const isOpen = () => body.classList.contains('menu-open');

  /**
   * Блокує сторінку без стрибка: body стає fixed, тож треба вручну
   * запам'ятати позицію скролу й повернути її при закритті.
   */
  const lockScroll = () => {
    scrollY = window.scrollY;
    body.style.top = `-${scrollY}px`;
  };

  const unlockScroll = () => {
    body.style.top = '';
    window.scrollTo(0, scrollY);
  };

  const setOpen = (open) => {
    if (open === isOpen()) return;

    if (open) lockScroll();
    body.classList.toggle('menu-open', open);
    if (!open) unlockScroll();

    burger.setAttribute('aria-expanded', String(open));
  };

  burger.addEventListener('click', () => setOpen(!isOpen()));

  // Клік по пункту меню — закрити (актуально для якорів у межах сторінки).
  nav.addEventListener('click', (e) => {
    if (e.target.closest('a')) setOpen(false);
  });

  // Клік поза панеллю (затемнення або решта шапки).
  document.addEventListener('click', (e) => {
    if (!isOpen()) return;
    if (nav.contains(e.target) || burger.contains(e.target)) return;
    setOpen(false);
  });

  // Escape — закрити й повернути фокус на бургер.
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isOpen()) {
      setOpen(false);
      burger.focus();
    }
  });

  // Свайп праворуч по панелі — закрити (звичний жест для шухляди).
  let touchStartX = null;

  nav.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].clientX;
  }, { passive: true });

  nav.addEventListener('touchend', (e) => {
    if (touchStartX === null) return;
    const delta = e.changedTouches[0].clientX - touchStartX;
    touchStartX = null;
    if (delta > 60) setOpen(false);
  }, { passive: true });

  // Перехід на десктоп при відкритому меню — прибрати стан, інакше <body>
  // лишиться заблокованим. Пасивний слухач: лише читаємо ширину.
  window.addEventListener('resize', () => {
    if (window.innerWidth >= DESKTOP_BREAKPOINT && isOpen()) setOpen(false);
  }, { passive: true });
}

/**
 * Тінь під зафіксованою шапкою — лише коли сторінку прокрутили від верху.
 *
 * Клас перемикається в rAF, а не на кожен подієвий тик: скрол сипле десятки
 * подій за секунду, і кожна з них інакше чіпала б classList.
 */
function initScrolledState(header) {
  let ticking = false;

  const sync = () => {
    ticking = false;
    header.classList.toggle('is-scrolled', window.scrollY > 0);
  };

  window.addEventListener('scroll', () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(sync);
  }, { passive: true });

  sync(); // перезавантаження вже прокрученої сторінки
}
