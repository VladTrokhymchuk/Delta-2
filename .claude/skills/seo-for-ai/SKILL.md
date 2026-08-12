---
name: seo-for-ai
description: >-
  Optimize for AI on two axes: (1) be found & cited by AI answer engines (ChatGPT/GPT, Claude,
  Perplexity, Google AI Overviews, Gemini) — GEO/AEO; (2) be operable by AI browsing agents —
  agentic browsing (WebMCP, Lighthouse Agentic Browsing, accessibility & CLS). Covers JSON-LD
  structured data, llms.txt, FAQ/HowTo schema, answer-first content, clean semantics, crawler
  access (GPTBot/ClaudeBot/PerplexityBot), and WebMCP tools/forms for agents. Use for "сео під
  GPT/Claude/AI", AI search, LLM SEO, GEO, AEO, llms.txt, цитованість у чатах, WebMCP, AI-агент
  на сайті, agentic browsing, Lighthouse agent audit.
---

# SEO під AI (GEO/AEO + Agentic Browsing)

Дві РІЗНІ осі — не змішуй їх:

| Вісь | Що це | Розділи нижче |
|------|-------|---------------|
| **GEO / AEO** | Щоб AI-движки **знаходили й цитували** контент. Пасивне читання. | §1–§5 |
| **Agentic Browsing** | Щоб AI-агент **діяв на сайті** — клікав, заповнював форми, бронював. Активна взаємодія. | §6 |

Обидві доповнюють класичне [[seo-optimization]] і спираються на ту саму чисту семантику. Для готелю GEO — первинна віддача («готель на Троєщині», «де зупинитись біля…»), а agentic browsing цікавий тим, що бронювання — транзакційний флоу, який агенти вчаться проходити.

## Частина A. GEO / AEO — щоб знаходили й цитували

## 1. Доступ для AI-краулерів (це треба зробити першим)
Без доступу нічого з решти не працює. Потрібні правила в `robots.txt`:

```
User-agent: GPTBot
Allow: /
User-agent: OAI-SearchBot
Allow: /
User-agent: ChatGPT-User
Allow: /
User-agent: ClaudeBot
Allow: /
User-agent: Claude-User
Allow: /
User-agent: PerplexityBot
Allow: /
User-agent: Google-Extended
Allow: /

Sitemap: https://example.com/sitemap_index.xml
```
(`Google-Extended` керує використанням у Gemini/AI Overviews; `sitemap_index.xml` генерує Yoast.)

**У цій темі це реалізовано** у [functions-parts/_seo-ai.php](../../../functions-parts/_seo-ai.php) — функція `delta_robots_ai_crawlers()` через WP-фільтр `robots_txt` (дописує правила у віртуальний robots.txt, який віддає WP; фізичного файлу немає). Не працює, поки сайт закритий від індексації в Налаштування → Читання. Перевір, що CDN/хостинг не блокує ці user-agent'и.

## 2. llms.txt
`llms.txt` у корені сайту — карта головного контенту для LLM (специфікація llmstxt.org): Markdown зі списком ключових сторінок і коротким описом.

**У цій темі це реалізовано** у [functions-parts/_seo-ai.php](../../../functions-parts/_seo-ai.php): динамічний роут `^llms\.txt$` (rewrite rule + `template_redirect` з пріоритетом 0, щоб не було 301 на `/llms.txt/`). Кешує у transient `delta_llms_txt` (12 год); кеш скидається на `save_post` / `deleted_post` / зміну назви-слогану, а також вручну кнопкою **«llms.txt ↻»** в адмін-барі.

**Додавання CPT у карту.** Список типів порожній за замовчуванням — після реєстрації CPT ([[custom-post-types]]) додай фільтр:
```php
add_filter('delta_llms_post_types', function ($types) {
    return $types + array(
        'room'  => 'Номери',
        'offer' => 'Спецпропозиції',
    );
});
```

**Що НЕ потрапляє в карту** (щоб не рекламувати AI сміття/недороблене):
- дефолтна WP `Sample Page`;
- сторінки/пости з Yoast `noindex`;
- **порожні сторінки конструктора «в розробці»** — без жодної секції `page_sections` і без власного шаблону (перевірка `delta_llms_skip()`).

Контакти в кінці карти беруться з ACF Options (`footer_phone` / `footer_email` / `footer_address` / `footer_socials`) — поки поля порожні, блок просто не виводиться.

Структура виводу:
```
# Готель Дельта

> Короткий опис готелю (з get_bloginfo('description')).

Сайт: https://example.com/

## Сторінки
- [Назва сторінки](https://example.com/slug)

## Номери
- [Люкс однокімнатний](https://example.com/rooms/lux): короткий опис.
```

## 3. Структуровані дані JSON-LD (головний канал для AI)
AI-движки добре «читають» schema.org. Для готелю пріоритет:
- **Hotel / LodgingBusiness** — назва, адреса (`PostalAddress`), телефон, координати, `starRating`, `amenityFeature`, `checkinTime`/`checkoutTime`, `priceRange`. Це база, з якої AI будує відповідь «що це за готель».
- **HotelRoom** + **Offer** — тип номера, місткість, ціна.
- **FAQPage** — секція питань-відповідей (дуже добре цитується в AI-відповідях).
- **Article** — новини/блог: `headline`, `datePublished`, `dateModified`, `author`.
- **BreadcrumbList** — дає Yoast.

Приклад FAQ (рендери з ACF repeater секції FAQ — див. [[section-builder]]):
```php
$faqs = get_sub_field('faq_items'); // repeater: question / answer
if ($faqs) {
  $schema = ['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>[]];
  foreach ($faqs as $f) {
    $schema['mainEntity'][] = [
      '@type'=>'Question','name'=>$f['question'],
      'acceptedAnswer'=>['@type'=>'Answer','text'=> wp_strip_all_tags($f['answer'])],
    ];
  }
  echo '<script type="application/ld+json">'
     . wp_json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . '</script>';
}
```

## 4. Контент під AEO (answer-first)
LLM витягують конкретні відповіді — структуруй контент так, щоб їх легко витягти:
- **Суть на початку.** Перший абзац секції = пряма відповідь на питання, далі деталі.
- **Питання як заголовки.** `<h2>` у формі питання користувача («Скільки коштує номер?», «Чи є паркінг?») + короткий чіткий абзац-відповідь.
- **Факти у списках/таблицях** — ціни, зручності, час заїзду/виїзду, відстані (легко парситься).
- **Самодостатні речення** — без «див. вище»; кожне твердження зрозуміле поза контекстом.
- **Конкретика й цифри** — адреса, відстань до метро/аеропорту, кількість номерів, ціни.
- **Сутності явно** — назва готелю, район, орієнтири текстом, не лише в зображенні.
- **Дати оновлення** — `dateModified` у schema + видима дата; свіжість впливає на цитування.

Усе це реалізується через поля ACF у секціях [[section-builder]] (FAQ, зручності, ціни), тож менеджер наповнює правильно структуровано.

## 5. Технічна доступність контенту
- Контент має бути в **HTML на сервері** (PHP-рендер), не довантажуватись лише JS — багато AI-краулерів не виконують JS. У цій темі секції рендеряться PHP — добре; не ховай ключовий контент за клієнтським fetch.
- Семантика й один H1 (див. [[seo-optimization]]) — AI спирається на ту саму структуру.
- Чисті, осмислені URL (slug з [[custom-post-types]]).

## Частина B. Agentic Browsing — щоб AI-агент діяв на сайті

Джерело: Chrome «Agent-Ready Toolkit» (developer.chrome.com/blog/agent-ready-toolkit). Це про сайти, якими AI-агент **керує** (заповнює форму бронювання, проходить флоу), а не просто читає.

## 6. Три речі, які перевіряє Chrome-тулкіт
1. **Accessibility** — семантичний HTML + програмні імена елементів (`<button>`/`<a>` за призначенням, `aria-label`, `<label>` до інпутів, коректний accessibility tree). Агент «бачить» сайт через дерево доступності, не піксельно. Це **той самий** чек-лист a11y, що й у [[seo-optimization]] та [[performance-optimization]] — виконуй його, і цю вісь на 80% закрито безкоштовно.
2. **Stability (CLS)** — мінімальний Cumulative Layout Shift, щоб агент не промахувався по кнопках, які «стрибнули». `width`/`height`/`aspect-ratio` зображенням, зарезервоване місце під динаміку — див. CLS-правила в [[performance-optimization]].
3. **WebMCP** — сайт декларує машиночитабельні «інструменти» й форми (Model Context Protocol для веб), щоб агент не парсив DOM наосліп, а викликав явний tool. Найкорисніше саме під форму бронювання / перевірку доступності номерів. **Статус: експериментальний** (Chrome M150+, прапорець `#enable-webmcp-testing`) — впроваджуй лише коли з'явиться реальна потреба; поки що фіксуємо як напрям.

**Інструмент перевірки:** Lighthouse → категорія **Agentic Browsing** (Chrome M150+) + Chrome DevTools for Agents. Ганяй разом зі звичайним Lighthouse (див. [[performance-optimization]]).

**Що робити зараз:** пункти 1–2 (a11y + CLS) — вони й так у скоупі перформансу/SEO; WebMCP (3) — на радар до появи форми бронювання.

## Чек-ліст під AI-пошук (GEO/AEO)
- [ ] AI-боти дозволені в robots.txt (реалізовано в `_seo-ai.php`)
- [ ] llms.txt віддається й актуальний; CPT додані через `delta_llms_post_types`
- [ ] Hotel/LodgingBusiness + контакти на всіх сторінках
- [ ] FAQPage / HotelRoom / Article schema де доречно
- [ ] answer-first контент, питання в заголовках
- [ ] ключовий контент у серверному HTML, не лише JS
- [ ] dateModified оновлюється

## Чек-ліст під agentic browsing
- [ ] семантичні `<button>`/`<a>`/`<label>`, коректний accessibility tree
- [ ] низький CLS (розміри зображень, зарезервоване місце)
- [ ] прогнати Lighthouse «Agentic Browsing» (Chrome M150+)
- [ ] WebMCP — оцінити під форму бронювання (поки на радарі)

## Чого НЕ робити
- ❌ ховати контент за клієнтським JS-фетчем;
- ❌ schema, що не відповідає видимому контенту (за це карають);
- ❌ блокувати AI-ботів «про всяк випадок», якщо мета — цитованість;
- ❌ «вода» без фактів — AI цитує конкретику.
