import { resolve, sep } from 'node:path';
import fs from 'node:fs';

export const toPosix = (p) => p.split(sep).join('/');

// Recursively collect files under `dir` whose basename matches `filter`.
// Hidden files (.DS_Store, .gitkeep, …) are skipped. Directories whose name
// is in `skipDirs` are not descended into. Returns absolute paths.
export function walk(dir, filter, skipDirs = []) {
  const out = [];
  if (!fs.existsSync(dir)) return out;
  for (const name of fs.readdirSync(dir)) {
    if (name.startsWith('.')) continue;
    const full = resolve(dir, name);
    if (fs.statSync(full).isDirectory()) {
      if (skipDirs.includes(name)) continue;
      out.push(...walk(full, filter, skipDirs));
    } else if (filter(name)) out.push(full);
  }
  return out;
}
