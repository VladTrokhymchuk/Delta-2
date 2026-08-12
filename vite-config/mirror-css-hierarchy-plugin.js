// Rollup emits CSS assets flat as `css/<basename>.min.css`. This plugin
// renames them to mirror the originating entry's folder path, so an entry
// at `js/pages/shop/cart` ships its CSS at `css/pages/shop/cart.min.css`.
export function mirrorEntryHierarchyForCssPlugin() {
  return {
    name: 'mirror-entry-hierarchy-for-css',
    apply: 'build',
    generateBundle(_, bundle) {
      for (const chunk of Object.values(bundle)) {
        if (chunk.type !== 'chunk' || !chunk.isEntry) continue;
        // Accept both `js/…` (real JS entries) and `scss/…` (virtual SCSS entries).
        const firstSlash = chunk.name.indexOf('/');
        if (firstSlash === -1) continue;
        if (!['js', 'scss'].includes(chunk.name.slice(0, firstSlash))) continue;
        const cssNames = chunk.viteMetadata?.importedCss;
        if (!cssNames || cssNames.size === 0) continue;
        const rel = chunk.name.slice(firstSlash + 1);
        const slashIdx = rel.lastIndexOf('/');
        if (slashIdx === -1) continue;
        const dir = rel.slice(0, slashIdx);
        for (const cssName of [...cssNames]) {
          const asset = bundle[cssName];
          if (!asset) continue;
          const base = cssName.split('/').pop();
          const newName = `css/${dir}/${base}`;
          if (newName === cssName) continue;
          delete bundle[cssName];
          asset.fileName = newName;
          bundle[newName] = asset;
          cssNames.delete(cssName);
          cssNames.add(newName);
        }
      }
    },
  };
}
