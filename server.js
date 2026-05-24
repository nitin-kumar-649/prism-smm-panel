// Entry point for Hostinger Node.js hosting
// Hostinger setup:
//   1. Set "Entry point" to: server.js
//   2. Set NODE_ENV to: production
//   3. Set DATABASE_URL to: file:./prisma/dev.db
//   4. Set JWT_SECRET to: any-random-secret

const { execSync } = require("child_process");
const path = require("path");
const fs = require("fs");

const standalonePath = path.join(__dirname, ".next", "standalone", "server.js");

// Auto-build if standalone server doesn't exist yet
if (!fs.existsSync(standalonePath)) {
  console.log("> Build not found. Running npm run build...");
  try {
    execSync("npm run build", { stdio: "inherit", cwd: __dirname });
  } catch (err) {
    console.error("Build failed:", err.message);
    process.exit(1);
  }
}

// Auto-run prisma migrate if DB doesn't exist
const dbPath = path.join(__dirname, "prisma", "dev.db");
if (!fs.existsSync(dbPath)) {
  console.log("> Database not found. Running migrations...");
  try {
    execSync("npx prisma migrate deploy", { stdio: "inherit", cwd: __dirname });
    execSync("npx tsx prisma/seed.ts", { stdio: "inherit", cwd: __dirname });
  } catch (err) {
    console.error("DB setup failed:", err.message);
  }
}

// Set PORT and HOSTNAME for the standalone server
process.env.PORT = process.env.PORT || "3000";
process.env.HOSTNAME = process.env.HOSTNAME || "0.0.0.0";

console.log(`> Starting server on port ${process.env.PORT}...`);
require(standalonePath);
