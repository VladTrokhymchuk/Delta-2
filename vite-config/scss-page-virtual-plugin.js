import { resolve } from 'node:path';
import { SCSS_PAGE_VIRT_PREFIX } from './conventions.js';

// Resolves virtual ids produced by findScssEntries into a one-line JS module
// that imports the matching SCSS file. Rollup then extracts the CSS as a
// sibling asset — so authors never need a stub JS file per stylesheet.
//
// The virtual id suffix is a path relative to `src/styles/`, e.g.
// `critical` → `src/styles/critical.scss`, `pages/page-404` → `src/styles/pages/page-404.scss`.
export function scssPageVirtualPlugin(SRC) {
  return {
    name: 'scss-page-virtual',
    resolveId(id) {
      return id.startsWith(SCSS_PAGE_VIRT_PREFIX) ? id : null;
    },
    load(id) {
      if (!id.startsWith(SCSS_PAGE_VIRT_PREFIX)) return null;
      const rel = id.slice(SCSS_PAGE_VIRT_PREFIX.length);
      const scss = resolve(SRC, 'styles', `${rel}.scss`).replace(/\\/g, '/');
      return `import ${JSON.stringify(scss)};`;
    },
  };
}
