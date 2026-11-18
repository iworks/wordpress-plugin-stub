/**
 * Translations — wp-cli i18n make-pot / update-po / make-mo.
 * Replaces grunt-wp-i18n (makepot) and grunt-potomo.
 */

import { dirname, join, resolve } from "node:path";
import { existsSync, readFileSync, readdirSync } from "node:fs";
import { log } from "./consoleHelpers.mjs";
import { config } from "./config.mjs";
import { spawnAsync } from "./spawnAsync.mjs";
import { writeFileAtomic } from "./tools.mjs";

const root = resolve(dirname(new URL(import.meta.url).pathname), "..");
const WP_CLI_URL =
  "https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar";

/**
 * Make sure wp-cli.phar is available; download it when missing.
 */
async function ensureWpCli() {
  const phar = join(root, config.wpCli);
  if (existsSync(phar)) {
    return phar;
  }
  log("wp-cli.phar not found — downloading…", "w");
  const result = await spawnAsync(
    "curl",
    ["-sSfL", WP_CLI_URL, "-o", config.wpCli],
    "wp-cli download",
    false,
  );
  if (result.code !== 0 || !existsSync(phar)) {
    const err = new Error(
      "wp-cli.phar is missing and the download failed. Download it manually from https://wp-cli.org/ and place it in the project root.",
    );
    err.friendly = true;
    throw err;
  }
  return phar;
}

/**
 * Run a wp-cli i18n command.
 *
 * @param {string[]} args Command arguments.
 * @param {string}   label Log label.
 */
async function wpCliI18n(args, label) {
  const phar = await ensureWpCli();
  const result = await spawnAsync(
    "php",
    [config.wpCli, "i18n", ...args, "--no-color"],
    label,
    false,
  );
  if (result.code !== 0) {
    const err = new Error(
      `${label}\n${result.stderr.trim() || result.stdout.trim()}`,
    );
    err.friendly = true;
    throw err;
  }
}

/**
 * Build the .pot file and update all .po files.
 *
 * @param {boolean} makeMo Also compile .mo files (build:github).
 */
export async function makePot(makeMo = false) {
  const potFile = join(config.languagesDir, `${config.textdomain}.pot`);
  const potPath = join(root, potFile);
  // Remember the current POT-Creation-Date: if the regenerated file differs
  // only by that timestamp, the old one is restored (no VCS churn).
  // The date lives in a quoted PO string: "POT-Creation-Date: …\n".
  const datePattern = /^("POT-Creation-Date: ).*(\\n")$/m;
  let previousDateLine = null;
  let previousContent = null;
  if (existsSync(potPath)) {
    previousContent = readFileSync(potPath, "utf8");
    previousDateLine = previousContent.match(datePattern)?.[0] ?? null;
  }
  const excludes =
    "release,node_modules,.git,.opencode,bundler,tests,.sass-cache";
  await wpCliI18n(
    [
      "make-pot",
      ".",
      potFile,
      `--exclude=${excludes}`,
      `--slug=${config.textdomain}`,
    ],
    "make-pot",
  );
  const newDateLine = previousContent
    ? (readFileSync(potPath, "utf8").match(datePattern)?.[0] ?? null)
    : null;
  if (previousDateLine && newDateLine) {
    // Compare both files with the timestamp blanked out (keep the quoted
    // line shape, only the value differs): X stands for the blanked date.
    const blank = (s) => s.replace(datePattern, "$1X$2");
    const content = readFileSync(potPath, "utf8");
    if (blank(content) === blank(previousContent)) {
      writeFileAtomic(potPath, content.replace(datePattern, previousDateLine));
      log("make-pot — strings unchanged, POT-Creation-Date preserved", "i");
    } else {
      log(
        `make-pot — strings changed, POT-Creation-Date updated (${previousDateLine} → ${newDateLine})`,
        "i",
      );
    }
  }
  const poFiles = existsSync(join(root, config.languagesDir))
    ? readdirSync(join(root, config.languagesDir)).filter((f) =>
        f.endsWith(".po"),
      )
    : [];
  for (const po of poFiles) {
    await wpCliI18n(
      ["update-po", join(config.languagesDir, po)],
      `update-po ${po}`,
    );
  }
  if (makeMo) {
    if (poFiles.length) {
      await wpCliI18n(["make-mo", config.languagesDir], "make-mo");
    } else {
      log("make-mo — no .po files found, skipped", "w");
    }
  }
  log(
    `i18n — ${potFile}${poFiles.length ? ` + ${poFiles.length} .po updated` : ""}${makeMo && poFiles.length ? " + .mo compiled" : ""}`,
    "s",
  );
}
