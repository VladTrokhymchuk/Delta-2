/**
 * Головний JS-бандл теми → build/js/app.min.js (manifest key: app).
 *
 * ВАЖЛИВО про імена файлів: за конвенцією збірки кожен .js без `_` на початку
 * стає ОКРЕМИМ entry, і тоді імпорт із app.js перетворюється на ESM-import між
 * бандлами — а app підключається класичним <script>, тож сторінка впаде з
 * «Cannot use import statement outside a module».
 * Тому спільні модулі й модулі секцій називаємо з підкресленням:
 *
 *   src/js/modules/_header.js      ✅ вбудується в app.min.js
 *   src/js/sections/_hero.js       ✅
 *   src/js/pages/booking.js        ✅ окремий bundle — так і задумано
 *
 * @see .claude/skills/section-builder
 */

import { initHeader } from '@js/modules/_header.js';
import { initSliders } from '@js/modules/_slider.js';
import { initGallery } from '@js/sections/_gallery.js';

initHeader();
initSliders();
initGallery();
