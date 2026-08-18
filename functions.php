<?php
/**
 * Hotel Delta — точка входу теми. Тільки підключення частин, без логіки.
 *
 * @see .claude/skills/page-constructor
 */

if (!defined('ABSPATH')) exit;

# 1. Ядро теми
include_once 'functions-parts/Mobile_Detect.php';
include_once 'functions-parts/_assets.php';
include_once 'functions-parts/_acf.php';
include_once 'functions-parts/_sections.php';
include_once 'functions-parts/_post-types-registration.php';
include_once 'functions-parts/_taxonomies-registration.php';
include_once 'functions-parts/_hooks.php';
include_once 'functions-parts/_custom-functions.php';
include_once 'functions-parts/_seo.php';
include_once 'functions-parts/_seo-ai.php';
include_once 'functions-parts/_ajax.php';

# 2. Дрібні налаштування WP
include_once 'functions-parts/headache.php';
include_once 'functions-parts/parts/remove_action.php';
include_once 'functions-parts/parts/allow_svg.php';
include_once 'functions-parts/parts/configure_menu.php';
include_once 'functions-parts/parts/header.php';
include_once 'functions-parts/parts/footer.php';
include_once 'functions-parts/parts/rooms.php';
include_once 'functions-parts/parts/icons.php';
include_once 'functions-parts/parts/buttons.php';
include_once 'functions-parts/parts/reviews.php';
include_once 'functions-parts/parts/redirects.php';
include_once 'functions-parts/parts/classic-editor.php';
include_once 'functions-parts/parts/admin_style.php';
include_once 'functions-parts/parts/security.php';
include_once 'functions-parts/parts/shortText.php';

# 3. Модулі
include_once 'functions-parts/modules/page-transfer/init.php'; // перенос сторінок локал → прод
