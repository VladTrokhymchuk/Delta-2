---
name: code-testing
description: >-
  Test and lint the theme codebase — PHP (WPCS/PHPCS, PHPStan, WP best practices), JS
  (ESLint, optional Vitest unit tests), SCSS (Stylelint), plus build/render smoke checks.
  Use for "тестування", "перевір код", lint, linting, phpcs, eslint, stylelint, unit test,
  "чи валідний код", before commit/release QA, або коли треба переконатись що код не зламаний.
---

# Тестування та лінтинг коду (PHP / JS / SCSS)

Мета — впіймати помилки до релізу: статичний аналіз + лінтери + smoke-перевірка білда й рендера. Стек без тест-раннерів за замовчуванням, тож скіл і ставить інструменти, і запускає.

## Перед будь-якою перевіркою
- Тема ще будується — спершу переконайся, що файл існує, перш ніж його лінтити (багато каталогів поки порожні).
- Локальне середовище — **Local (Flywheel)**, сайт: http://hotel-delta.local/. Системний `php` доступний у PATH.

## 1. PHP

### PHP syntax (швидкий чек, без залежностей)
```bash
find . -name '*.php' -not -path './node_modules/*' -not -path './vendor/*' -print0 \
  | xargs -0 -n1 -P4 php -l
```
`php -l` ловить фатальні синтаксичні помилки в кожному файлі.

### WordPress Coding Standards (PHPCS + WPCS)
```bash
composer require --dev wp-coding-standards/wpcs dealerdirect/phpcodesniffer-composer-installer
./vendor/bin/phpcs  --standard=WordPress --extensions=php --ignore=*/vendor/*,*/node_modules/*,*/build/* .
./vendor/bin/phpcbf --standard=WordPress .   # автофікс
```
Перевіряє неймінг, екранування виводу (критично — узгоджено з [[acf-fields]]), nonce, санітизацію.

### PHPStan (статичний аналіз логіки)
```bash
composer require --dev phpstan/phpstan szepeviktor/phpstan-wordpress
./vendor/bin/phpstan analyse --level=5 functions.php functions-parts/ template-parts/
```

**На що дивитись у WP-PHP вручну:** усе виводиться через `esc_html/esc_url/esc_attr/wp_kses_post`; форми мають nonce + перевірку прав; немає прямих SQL без `$wpdb->prepare`; хуки на `init`/`wp_enqueue_scripts`; немає закритого `?>` в кінці чистих PHP-файлів; префікс функцій `delta_` (щоб не було колізій із плагінами).

## 2. JavaScript (ESLint)
```bash
npm i -D eslint @eslint/js
npx eslint "src/js/**/*.js"
```
Мінімальний `eslint.config.js` (flat config, ESM — проєкт `"type":"module"`):
```js
import js from '@eslint/js';
export default [
  js.configs.recommended,
  { languageOptions: { ecmaVersion: 'latest', sourceType: 'module',
      globals: { window: 'readonly', document: 'readonly' } },
    rules: { 'no-unused-vars': 'warn', 'no-undef': 'error' } },
];
```
Аліаси (`@js`, `@styles`…) резолвить Vite, не ESLint — ESLint лише синтаксис/логіка. Перевіряй, що init-модулі секцій guard'яться через `[data-section]` (узгоджено з [[section-builder]]).

### Юніт-тести (опційно, Vitest — вже є Vite)
```bash
npm i -D vitest
npx vitest run
```
Покривай чисту логіку з `src/js/utils/` (формат-функції, розрахунки дат/цін), не DOM-обв'язку.

## 3. SCSS (Stylelint)
```bash
npm i -D stylelint stylelint-config-standard-scss
npx stylelint "src/styles/**/*.scss"
```
`.stylelintrc.json`:
```json
{ "extends": "stylelint-config-standard-scss",
  "rules": { "no-descending-specificity": null, "scss/at-rule-no-unknown": true } }
```
Перевіряє валідність, дублі, специфічність. Узгоджено з конвенцією `@use '../core' as *`.

## 4. Build & render smoke-test
**Білд автоматичний** — тримай запущеним `npm run dev` (watch), який пересобирає на кожну зміну. Під час роботи **НЕ запускай `npm run build` вручну** — дочекайся, поки watch оновить `build/`. Ручний прод-білд — лише для фінального QA:
```bash
npm run build          # має пройти без помилок; перевір build/manifest.json
```
- очікувані ключі manifest на старті: `critical`, `main`, `app`, `pages/page-404`. Нові entry (`src/js/pages/*.js`, `src/styles/pages/*.scss`) з'являються лише після перезапуску watch;
- ключі manifest мають збігатися з `get_asset()` у [functions-parts/_assets.php](../../../functions-parts/_assets.php) — розсинхрон = стилі мовчки не підключились;
- відкрий ключові сторінки локально → консоль браузера без JS-помилок, секції рендеряться, `data-section`-модулі ініціалізуються;
- швидка перевірка кодів відповіді:
  ```bash
  curl -s -o /dev/null -w "%{http_code}\n" http://hotel-delta.local/
  curl -s -o /dev/null -w "%{http_code}\n" http://hotel-delta.local/no-such-page   # очікуємо 404
  curl -s http://hotel-delta.local/llms.txt | head
  ```
- для реального функціонального прогону в браузері використовуй скіл `/run`.

**Увага:** активною темою на локалці може бути стара `Delta` — перевір Зовнішній вигляд → Теми, інакше тестуєш не той код.

## Рекомендований порядок «перевір усе»
```bash
find . -name '*.php' -not -path './node_modules/*' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
npx stylelint "src/styles/**/*.scss"
npx eslint "src/js/**/*.js"
npm run build
# за наявності composer-залежностей:
./vendor/bin/phpcs --standard=WordPress . && ./vendor/bin/phpstan analyse --level=5 functions.php functions-parts/
```

## Чого НЕ робити
- ❌ комітити код, що не проходить `php -l` / lint;
- ❌ ігнорувати попередження PHPCS про екранування виводу;
- ❌ писати DOM-залежні тести замість виділення чистої логіки в `utils/`;
- ❌ правити згенерований `build/` руками — тільки `src/`.
