/**
 * Salvează toate URL-urile din presa.html pe Wayback Machine (archive.org)
 *
 * Rulează: node archive-urls.js
 *
 * Wayback Machine are rate limiting, așa că scriptul trimite câte un request
 * la fiecare 5 secunde pentru a nu fi blocat.
 */

const fs = require("fs");
const https = require("https");
const http = require("http");

// Extrage URL-urile din presa.html
const html = fs.readFileSync("presa.html", "utf-8");
const urls = [];
const regex = /url:\s*"(https?:\/\/[^"]+)"/g;
let match;
while ((match = regex.exec(html)) !== null) {
  urls.push(match[1]);
}

console.log(`Găsite ${urls.length} URL-uri de arhivat.\n`);

const DELAY_MS = 5000; // 5 secunde între requesturi

function saveToWayback(url, index) {
  return new Promise((resolve) => {
    const saveUrl = `https://web.archive.org/save/${url}`;

    const req = https.get(saveUrl, { timeout: 30000 }, (res) => {
      const status = res.statusCode;
      if (status >= 200 && status < 400) {
        console.log(`[${index + 1}/${urls.length}] OK (${status}) — ${url}`);
      } else {
        console.log(`[${index + 1}/${urls.length}] WARN (${status}) — ${url}`);
      }
      res.resume();
      res.on("end", resolve);
    });

    req.on("error", (err) => {
      console.log(`[${index + 1}/${urls.length}] ERR — ${url} — ${err.message}`);
      resolve();
    });

    req.on("timeout", () => {
      console.log(`[${index + 1}/${urls.length}] TIMEOUT — ${url}`);
      req.destroy();
      resolve();
    });
  });
}

async function main() {
  console.log("Încep arhivarea pe Wayback Machine...\n");

  let ok = 0, fail = 0;

  for (let i = 0; i < urls.length; i++) {
    await saveToWayback(urls[i], i);

    // Pauză între requesturi
    if (i < urls.length - 1) {
      await new Promise((r) => setTimeout(r, DELAY_MS));
    }
  }

  console.log(`\nGata! Toate ${urls.length} URL-urile au fost trimise la Wayback Machine.`);
  console.log("Verifică pe https://web.archive.org/ că s-au salvat.");
}

main();
