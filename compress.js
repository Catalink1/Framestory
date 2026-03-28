const sharp = require("sharp");
const fs = require("fs");
const path = require("path");

const imgDir = "./img";
const files = fs
  .readdirSync(imgDir)
  .filter((f) => /\.(jpg|jpeg|png)$/i.test(f));

files.forEach((file) => {
  const input = path.join(imgDir, file);
  sharp(input)
    .resize({ width: 1920, withoutEnlargement: true })
    .jpeg({ quality: 75 })
    .toBuffer()
    .then((buf) => {
      fs.writeFileSync(input, buf);
      console.log(`✓ ${file} — ${(buf.length / 1024).toFixed(0)}KB`);
    })
    .catch((err) => console.error(`✗ ${file}:`, err.message));
});
