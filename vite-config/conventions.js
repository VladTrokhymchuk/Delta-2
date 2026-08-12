// ═════════════════════════════════════════════════════════════════════════════
// BUILD CONVENTIONS — the single source of truth for what ships where.
// Drop files into `src/` following these rules and the build picks them up.
// No config changes required.
//
// ENTRY POINTS — every file under src/js/ and src/styles/ is an entry and
// ships to build/ with its folder hierarchy mirrored. Two exceptions:
//   1. files beginning with `_` are helpers / partials (never entries)
//   2. these subtrees are excluded wholesale:
//        src/js/libs/**              vendor scripts — copied as-is
//        src/styles/partials/**      shared tools — `@use` only
//
// Examples:
//   src/js/app.js                  →  build/js/app.min.js
//   src/js/pages/shop/cart.js      →  build/js/pages/shop/cart.min.js
//   src/js/modules/header.js       →  build/js/modules/header.min.js
//   src/styles/general.scss        →  build/css/general.min.css
//   src/styles/pages/page-404.scss →  build/css/pages/page-404.min.css
//   src/styles/modules/header.scss →  build/css/modules/header.min.css
//   src/styles/_core.scss          →  (partial — imported via `@use 'core'`)
//
// STATIC / VENDOR (copied as-is, full recursive hierarchy preserved):
//   src/img/**      →  build/img/**
//   src/fonts/**    →  build/fonts/**
//   src/static/**   →  build/**
//   src/js/libs/**  →  build/js/libs/**     (vendor scripts, no bundling)
//   src/styles/**/*.css  →  build/css/**/*.css  (plain CSS, no compilation)
//
// STATIC ASSETS (copied as-is, full recursive hierarchy preserved):
//   src/img/**      →  build/img/**   (except src/img/sprite/ → SVG sprite)
//   src/fonts/**    →  build/fonts/**
//   src/static/**   →  build/**
// ═════════════════════════════════════════════════════════════════════════════

// Shared virtual-module id prefix for SCSS entries (both root and pages/).
// The suffix is the path relative to `src/styles/` without the `.scss` extension,
// e.g. `critical` → `src/styles/critical.scss`, `pages/page-404` → `src/styles/pages/page-404.scss`.
export const SCSS_PAGE_VIRT_PREFIX = '\0virtual:scss-entry:';
