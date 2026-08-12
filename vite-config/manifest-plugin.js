// Emits build/manifest.json mapping each logical entry name to its output
// files. Consume it from WordPress PHP (or any template layer) to enqueue
// the exact files the build produced — no hardcoded paths.
//
// Shape:
//   {
//     "app":            { "js": "js/app.min.js",            "css": "css/app.min.css" },
//     "critical":       { "js": "js/critical.min.js",       "css": "css/critical.min.css" },
//     "pages/page-404": {                                   "css": "css/pages/page-404.min.css" },
//     "pages/shop/cart":{ "js": "js/pages/shop/cart.min.js","css": "css/pages/shop/cart.min.css" }
//   }
export function buildManifestPlugin() {
  return {
    name: 'build-manifest',
    apply: 'build',
    enforce: 'post',
    generateBundle(_, bundle) {
      const manifest = {};
      for (const [fileName, chunk] of Object.entries(bundle)) {
        if (chunk.type !== 'chunk' || !chunk.isEntry) continue;
        if (!chunk.name.startsWith('js/')) continue;
        const logical = chunk.name.slice(3);
        const entry = (manifest[logical] ||= {});
        entry.js = fileName;
        const css = chunk.viteMetadata?.importedCss;
        if (css && css.size) entry.css = [...css][0];
      }
      // Stylesheet-only entries were already stripped to CSS assets — pick them
      // up by walking remaining assets whose name hints a logical entry path.
      for (const [fileName, asset] of Object.entries(bundle)) {
        if (asset.type !== 'asset') continue;
        if (!fileName.startsWith('css/') || !fileName.endsWith('.min.css')) continue;
        const logical = fileName.slice(4, -'.min.css'.length);
        const entry = (manifest[logical] ||= {});
        if (!entry.css) entry.css = fileName;
      }
      this.emitFile({
        type: 'asset',
        fileName: 'manifest.json',
        source: JSON.stringify(manifest, null, 2) + '\n',
      });
    },
  };
}
