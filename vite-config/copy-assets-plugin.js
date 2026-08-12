import { resolve, posix } from 'node:path';
import fs from 'node:fs';

// Copies plain static assets into build/ recursively, preserving folder
// hierarchy. New subfolders are picked up automatically.
//
//   src/img/**           →  build/img/**
//   src/fonts/**         →  build/fonts/**
//   src/static/**        →  build/**
//   src/styles/**/*.css  →  build/css/**/*.css   (plain CSS only — SCSS is compiled)
//   src/js/libs/**/*.js  →  build/js/libs/**/*.js (vendor scripts shipped as-is)
export function copyAssetsPlugin(SRC) {
  const crawl = (dir, base = '', { skip = [], filter } = {}) => {
    if (!fs.existsSync(dir)) return [];
    const out = [];
    for (const name of fs.readdirSync(dir)) {
      if (skip.includes(name)) continue;
      if (name.startsWith('.')) continue;
      const full = resolve(dir, name);
      const rel = base ? posix.join(base, name) : name;
      if (fs.statSync(full).isDirectory()) out.push(...crawl(full, rel, { filter }));
      else if (!filter || filter(name, rel)) out.push({ full, rel });
    }
    return out;
  };

  const targets = [
    { from: resolve(SRC, 'img'), to: 'img' },
    { from: resolve(SRC, 'fonts'), to: 'fonts' },
    { from: resolve(SRC, 'static'), to: '' },
    { from: resolve(SRC, 'styles'), to: 'css', filter: (name) => name.endsWith('.css') },
    { from: resolve(SRC, 'js/libs'), to: 'js/libs' },
  ];

  return {
    name: 'copy-static-assets',
    apply: 'build',
    // Статика не входить у граф модулів Rollup, тому в режимі --watch правки в
    // src/static, src/img тощо не тригерили перезбірку. Реєструємо їх як watch-файли.
    buildStart() {
      if (!this.meta.watchMode) return;
      for (const t of targets) {
        for (const f of crawl(t.from, '', { skip: t.skip, filter: t.filter })) {
          this.addWatchFile(f.full);
        }
      }
    },
    generateBundle() {
      for (const t of targets) {
        for (const f of crawl(t.from, '', { skip: t.skip, filter: t.filter })) {
          this.emitFile({
            type: 'asset',
            fileName: t.to ? posix.join(t.to, f.rel) : f.rel,
            source: fs.readFileSync(f.full),
          });
        }
      }
    },
  };
}
