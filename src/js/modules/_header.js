/**
 * Шапка: мобільне меню (бургер → панель справа).
 *
 * Стан тримається класом `menu-open` на <body> — CSS показує панель і
 * блокує скрол сторінки (див. _base.scss / _header.scss).
 */

const DESKTOP_BREAKPOINT = 992; // = $sm-pc

export function initHeader() {
  const header = document.querySelector('.header');
  if (!header) return;

  const burger = header.querySelector('.header__burger');
  const nav = header.querySelector('.header__nav');
  if (!burger || !nav) return;

  const isOpen = () => document.body.classList.contains('menu-open');

  const setOpen = (open) => {
    document.body.classList.toggle('menu-open', open);
    burger.setAttribute('aria-expanded', String(open));
  };

  burger.addEventListener('click', () => setOpen(!isOpen()));

  // Клік по пункту меню — закрити (посилання-якорі в межах сторінки).
  nav.addEventListener('click', (e) => {
    if (e.target.closest('a')) setOpen(false);
  });

  // Клік поза панеллю.
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

  // Перехід на десктоп при відкритому меню — прибрати стан,
  // інакше <body> лишиться заблокованим.
  window.addEventListener('resize', () => {
    if (window.innerWidth >= DESKTOP_BREAKPOINT && isOpen()) setOpen(false);
  });
}
