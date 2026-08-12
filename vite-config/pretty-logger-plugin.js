// Друкує зручні кольорові статуси збірки в термінал:
// старт, перезапуск watch, тривалість, кількість entry, змінені файли, помилки.

const c = {
  reset: '\x1b[0m',
  dim: '\x1b[2m',
  bold: '\x1b[1m',
  gray: '\x1b[90m',
  red: '\x1b[31m',
  green: '\x1b[32m',
  yellow: '\x1b[33m',
  blue: '\x1b[34m',
  magenta: '\x1b[35m',
  cyan: '\x1b[36m',
  white: '\x1b[37m',
  bgRed: '\x1b[41m',
};
const paint = (color, s) => `${color}${s}${c.reset}`;

const now = () => {
  const d = new Date();
  const pad = (n) => String(n).padStart(2, '0');
  return `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
};

const fmtMs = (ms) => {
  if (ms < 1000) return `${ms} ms`;
  return `${(ms / 1000).toFixed(2)} s`;
};

const divider = () => paint(c.gray, '─'.repeat(60));

export function prettyLoggerPlugin() {
  let startedAt = 0;
  let buildCount = 0;
  let changedFiles = new Set();
  let isWatch = false;

  const header = (emoji, label, color) => {
    console.log('');
    console.log(divider());
    console.log(
      `${paint(c.gray, `[${now()}]`)} ${emoji}  ${paint(c.bold + color, label)}`,
    );
  };

  return {
    name: 'pretty-logger',
    apply: 'build',
    enforce: 'post',

    configResolved(config) {
      isWatch = !!config.build?.watch;
      const mode = config.mode;
      console.log('');
      console.log(divider());
      console.log(
        `${paint(c.bold + c.cyan, '⚡ Vite')} ${paint(c.gray, '·')} ` +
          `mode: ${paint(c.yellow, mode)}` +
          `${isWatch ? ` ${paint(c.gray, '·')} ${paint(c.magenta, 'watch')}` : ''}`,
      );
      console.log(divider());
    },

    watchChange(id, { event }) {
      const rel = id.replace(process.cwd() + '/', '');
      changedFiles.add(`${event}:${rel}`);
    },

    buildStart() {
      buildCount += 1;
      startedAt = Date.now();
      const label =
        buildCount === 1 ? 'Build started' : `Rebuild #${buildCount - 1}`;
      const emoji = buildCount === 1 ? '🚀' : '🔁';
      header(emoji, label, c.cyan);

      if (changedFiles.size > 0) {
        console.log(paint(c.gray, '   changed:'));
        for (const item of changedFiles) {
          const [event, file] = item.split(/:(.+)/);
          const tag =
            event === 'create'
              ? paint(c.green, '+')
              : event === 'delete'
                ? paint(c.red, '-')
                : paint(c.yellow, '~');
          console.log(`   ${tag} ${paint(c.white, file)}`);
        }
        changedFiles.clear();
      }
    },

    buildEnd(err) {
      if (err) {
        const took = fmtMs(Date.now() - startedAt);
        header('❌', `Build failed in ${took}`, c.red);
        const loc = err.loc
          ? ` ${paint(c.gray, `(${err.loc.file}:${err.loc.line}:${err.loc.column})`)}`
          : '';
        console.log(`   ${paint(c.bgRed + c.white, ' ERROR ')} ${err.message}${loc}`);
        if (err.frame) console.log(paint(c.gray, err.frame));
      }
    },

    writeBundle(_opts, bundle) {
      const took = fmtMs(Date.now() - startedAt);
      const files = Object.keys(bundle);
      const js = files.filter((f) => f.endsWith('.js')).length;
      const css = files.filter((f) => f.endsWith('.css')).length;
      const assets = files.length - js - css;

      header('✅', `Build ready in ${took}`, c.green);
      console.log(
        `   ${paint(c.gray, 'output:')} ` +
          `${paint(c.yellow, js)} js ${paint(c.gray, '·')} ` +
          `${paint(c.blue, css)} css ${paint(c.gray, '·')} ` +
          `${paint(c.magenta, assets)} assets`,
      );
      if (isWatch) {
        console.log(paint(c.gray, `   watching for changes…`));
      }
    },
  };
}
