import { defineConfig } from 'vite';
import { resolve } from 'node:path';
import browserslistToEsbuild from 'browserslist-to-esbuild';
import {
  findJsEntries,
  findScssEntries,
  scssPageVirtualPlugin,
  mirrorEntryHierarchyForCssPlugin,
  copyAssetsPlugin,
  autoRestartOnNewEntryPlugin,
  dropScssPageWrappersPlugin,
  cssOnlyCleanupPlugin,
  jsOnlyCleanupPlugin,
  buildManifestPlugin,
  prettyLoggerPlugin,
} from './vite-config/index.js';

const root = resolve(import.meta.dirname);
const SRC = resolve(root, 'src');
const OUT = 'build';

export default defineConfig(({ mode }) => {
  const isDev = mode === 'development';
  const cssOnly = mode === 'css-only';
  const jsOnly = mode === 'js-only';

  // Auto-discovered entries:
  //   • every *.js in src/js/ and src/js/pages/
  //   • every *.scss in src/styles/pages/ (via virtual JS wrapper)
  // Drop a new file into those folders — it ships on the next build.
  const jsEntries = findJsEntries(SRC);
  const scssEntries = findScssEntries(SRC, new Set(Object.keys(jsEntries)));
  const input = { ...jsEntries, ...scssEntries };

  return {
    root,
    base: './',
    publicDir: false,
    resolve: {
      alias: {
        '@': SRC,
        '@img': resolve(SRC, 'img'),
        '@styles': resolve(SRC, 'styles'),
        '@js': resolve(SRC, 'js'),
      },
    },
    css: {
      devSourcemap: isDev,
      preprocessorOptions: {
        scss: {
          api: 'modern',
          silenceDeprecations: ['legacy-js-api', 'import', 'global-builtin', 'color-functions', 'slash-div'],
        },
      },
    },
    build: {
      outDir: OUT,
      emptyOutDir: !isDev,
      cssCodeSplit: true,
      sourcemap: isDev,
      minify: isDev ? false : 'esbuild',
      target: browserslistToEsbuild(),
      assetsInlineLimit: 0,
      rollupOptions: {
        input,
        output: {
          entryFileNames: (chunk) => {
            if (cssOnly) return 'js/.tmp/[name].js';
            return `${chunk.name}.min.js`;
          },
          chunkFileNames: 'js/chunks/[name]-[hash].js',
          assetFileNames: (asset) => {
            const n = asset.names?.[0] || '';
            if (n.endsWith('.css')) return 'css/[name].min.css';
            if (/\.(woff2?|ttf|otf|eot)$/i.test(n)) return 'fonts/[name][extname]';
            if (/\.(png|jpe?g|gif|webp|avif|svg)$/i.test(n)) return 'img/[name][extname]';
            return 'assets/[name][extname]';
          },
        },
      },
      watch: isDev ? {} : null,
    },
    plugins: [
      prettyLoggerPlugin(),
      scssPageVirtualPlugin(SRC),
      mirrorEntryHierarchyForCssPlugin(),
      autoRestartOnNewEntryPlugin(SRC),
      copyAssetsPlugin(SRC),

      cssOnly && cssOnlyCleanupPlugin(),
      jsOnly && jsOnlyCleanupPlugin(),
      !cssOnly && dropScssPageWrappersPlugin(),
      !cssOnly && !jsOnly && buildManifestPlugin(),
    ].filter(Boolean),

    server: {
      port: 3000,
      cors: true,
      host: true,
    },
  };
});
