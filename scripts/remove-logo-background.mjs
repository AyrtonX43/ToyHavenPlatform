/**
 * One-off script: Remove white/near-white background from logo.png
 * Run from project root: node scripts/remove-logo-background.mjs
 */

import { createRequire } from 'module';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const require = createRequire(import.meta.url);
const { Jimp } = require('jimp');

const __dirname = dirname(fileURLToPath(import.meta.url));
const logoPath = join(__dirname, '..', 'public', 'images', 'logo.png');

const threshold = 230; // Pixels with R,G,B all >= this become transparent (removes white, near-white, and light gray)

async function main() {
  const image = await Jimp.read(logoPath);
  const { width, height } = image.bitmap;

  image.scan(0, 0, width, height, (x, y, idx) => {
    const r = image.bitmap.data[idx];
    const g = image.bitmap.data[idx + 1];
    const b = image.bitmap.data[idx + 2];
    const a = image.bitmap.data[idx + 3];
    // Make white or near-white pixels fully transparent
    if (r >= threshold && g >= threshold && b >= threshold) {
      image.bitmap.data[idx + 3] = 0;
    }
  });

  await image.write(logoPath);
  console.log('Logo background removed and saved to:', logoPath);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
