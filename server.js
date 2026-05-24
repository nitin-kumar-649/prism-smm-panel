// Entry point for Hostinger Node.js hosting
// After running `npm run build`, this script starts the Next.js standalone server.
//
// Hostinger setup:
//   1. Set "Entry point" to: server.js
//   2. Set NODE_ENV to: production
//   3. Run `npm run build` after deploying

const { createServer } = require("http");
const { parse } = require("url");
const path = require("path");

// Try standalone server first (produced by `output: 'standalone'`)
const standalonePath = path.join(__dirname, ".next", "standalone", "server.js");

try {
  require(standalonePath);
} catch {
  // Fallback: start next directly
  const next = require("next");
  const app = next({ dev: false, dir: __dirname });
  const handle = app.getRequestHandler();

  const port = parseInt(process.env.PORT || "3000", 10);
  const hostname = process.env.HOSTNAME || "0.0.0.0";

  app.prepare().then(() => {
    createServer((req, res) => {
      const parsedUrl = parse(req.url || "", true);
      handle(req, res, parsedUrl);
    }).listen(port, hostname, () => {
      console.log(`> Ready on http://${hostname}:${port}`);
    });
  });
}
