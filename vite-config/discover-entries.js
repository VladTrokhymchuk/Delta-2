import { resolve, relative } from 'node:path';
import { walk, toPosix } from './utils.js';
import { SCSS_PAGE_VIRT_PREFIX } from './conventions.js';

// Entry discovery rule (applies to both JS and SCSS):
//   • every file under the root is an entry — folder hierarchy is mirrored in build/
//   • files beginning with `_` are helpers / partials and are never entries
//   • certain subtrees are excluded wholesale:
//       JS:    libs/       (vendor scripts — copied as-is by copyAssetsPlugin)
//       SCSS:  partials/   (shared tools — imported via `@use`)

const JS_SKIP_DIRS = ['libs'];
const SCSS_SKIP_DIRS = ['partials'];

const notPartial = (ext) => (n) => n.endsWith(ext) && !n.startsWith('_');

export function findJsEntries(SRC) {
  const entries = {};
  const jsRoot = resolve(SRC, 'js');
  for (const full of walk(jsRoot, notPartial('.js'), JS_SKIP_DIRS)) {
    const rel = toPosix(relative(jsRoot, full)).replace(/\.js$/, '');
    entries[`js/${rel}`] = full;
  }
  return entries;
}

// SCSS entries: every *.scss (outside partials/, no leading _) becomes its own
// CSS bundle via a virtual JS wrapper. The wrapper chunk is dropped post-bundle
// by dropScssPageWrappersPlugin — only the CSS asset ships. SCSS entries are
// fully independent of JS (different key prefix: `scss/` vs `js/`).
export function findScssEntries(SRC) {
  const entries = {};
  const stylesRoot = resolve(SRC, 'styles');
  for (const full of walk(stylesRoot, notPartial('.scss'), SCSS_SKIP_DIRS)) {
    const rel = toPosix(relative(stylesRoot, full)).replace(/\.scss$/, '');
    entries[`scss/${rel}`] = `${SCSS_PAGE_VIRT_PREFIX}${rel}`;
  }
  return entries;
}

// Back-compat alias — kept so external imports (if any) don't break.
export const findScssPageEntries = findScssEntries;
