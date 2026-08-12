/**
 * Головний JS-бандл теми → build/js/app.min.js (manifest key: app).
 *
 * Модулі секцій підключаються тут і самі перевіряють наявність своєї секції
 * на сторінці (guard по [data-section]), тож зайвий код не виконується:
 *
 *   import { initHero } from '@js/sections/hero.js';
 *   initHero();
 *
 * @see .claude/skills/section-builder
 */
