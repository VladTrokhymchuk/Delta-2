import { SCSS_PAGE_VIRT_PREFIX } from './conventions.js';

// Virtual SCSS-page entries produce a JS chunk that only carries a CSS
// side-effect import. Once Rollup has extracted the CSS, the JS shell is
// dead weight — drop it so we ship only the stylesheet.
export function dropScssPageWrappersPlugin() {
  return {
    name: 'drop-scss-page-wrappers',
    apply: 'build',
    enforce: 'post',
    generateBundle(_, bundle) {
      for (const [name, chunk] of Object.entries(bundle)) {
        if (chunk.type !== 'chunk') continue;
        const fromVirtual =
          chunk.facadeModuleId?.startsWith(SCSS_PAGE_VIRT_PREFIX) ||
          (chunk.moduleIds || []).some((id) => id.startsWith(SCSS_PAGE_VIRT_PREFIX));
        if (!fromVirtual) continue;
        delete bundle[name];
        delete bundle[`${name}.map`];
      }
    },
  };
}

// `npm run css` mode: keep only the stylesheets, discard every JS chunk.
export function cssOnlyCleanupPlugin() {
  return {
    name: 'css-only-cleanup',
    apply: 'build',
    enforce: 'post',
    generateBundle(_, bundle) {
      for (const name of Object.keys(bundle)) {
        if (name.startsWith('js/')) delete bundle[name];
      }
    },
  };
}

// `npm run js` mode: keep only the scripts, discard every CSS asset.
// Also drops the virtual SCSS-page wrappers (same reasoning as full builds).
export function jsOnlyCleanupPlugin() {
  return {
    name: 'js-only-cleanup',
    apply: 'build',
    enforce: 'post',
    generateBundle(_, bundle) {
      for (const [name, item] of Object.entries(bundle)) {
        if (item.type === 'asset' && name.startsWith('css/')) delete bundle[name];
      }
    },
  };
}
