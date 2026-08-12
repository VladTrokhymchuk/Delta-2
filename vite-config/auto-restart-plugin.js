import { relative } from 'node:path';
import { toPosix } from './utils.js';

// Rollup locks the `input` set at server startup, so a freshly-created entry
// file would be ignored until manual restart. This plugin watches for entry-
// eligible additions/removals in src/ and restarts the dev server for you.
export function autoRestartOnNewEntryPlugin(SRC) {
  const isEntryPath = (p) => {
    const rel = toPosix(relative(SRC, p));
    if (rel.startsWith('../') || rel.startsWith('/')) return false;
    const base = rel.split('/').pop();
    if (base.startsWith('_')) return false;
    if (rel.startsWith('js/') && rel.endsWith('.js') && !rel.startsWith('js/libs/')) return true;
    if (rel.startsWith('styles/') && rel.endsWith('.scss') && !rel.startsWith('styles/partials/')) return true;
    return false;
  };

  return {
    name: 'auto-restart-on-new-entry',
    apply: 'serve',
    configureServer(server) {
      const maybeRestart = (file) => {
        if (isEntryPath(file)) server.restart();
      };
      server.watcher.on('add', maybeRestart);
      server.watcher.on('unlink', maybeRestart);
    },
  };
}
