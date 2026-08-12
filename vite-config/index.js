// Barrel export — vite.config.js imports from here and nowhere else.
// See conventions.js for the drop-a-file → ships-automatically contract.
export { findJsEntries, findScssEntries, findScssPageEntries } from './discover-entries.js';
export { scssPageVirtualPlugin } from './scss-page-virtual-plugin.js';
export { mirrorEntryHierarchyForCssPlugin } from './mirror-css-hierarchy-plugin.js';
export { copyAssetsPlugin } from './copy-assets-plugin.js';
export { autoRestartOnNewEntryPlugin } from './auto-restart-plugin.js';
export {
  dropScssPageWrappersPlugin,
  cssOnlyCleanupPlugin,
  jsOnlyCleanupPlugin,
} from './drop-scss-wrappers-plugin.js';
export { buildManifestPlugin } from './manifest-plugin.js';
export { prettyLoggerPlugin } from './pretty-logger-plugin.js';
