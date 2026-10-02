import { execFileSync, spawnSync } from 'node:child_process';
import fs from 'node:fs';

/** Pixel size of an image file. */
export function size(file: string): [number, number] {
  return execFileSync('magick', ['identify', '-format', '%w %h', `${file}[0]`], { encoding: 'utf8' }).split(' ').map(Number) as [number, number];
}

/**
 * Share of pixels that differ by more than `threshold` in any channel, and a
 * diff image (white = different). A pixel only counts if it also differs
 * when the other image is moved up or down by `shift` pixels: sub-pixel
 * layout rounds single text lines 1 CSS px differently, which isn't a
 * visible change; anything moved further still counts.
 */
export function differingShare(a: string, b: string, diff: string, shift = 0, threshold = '8%'): number {
  const [w, h] = size(a);
  const [w2, h2] = size(b);
  if (w !== w2 || h !== h2) return 1; // different page sizes: a layout difference

  const masks = [0, shift, -shift].filter((offset, index, all) => all.indexOf(offset) === index).map(offset => {
    const mask = `${diff}.${offset}.png`;
    execFileSync('magick', [a, '(', b, '-roll', `+0${offset < 0 ? offset : `+${offset}`}`, ')', '-compose', 'difference', '-composite',
      '-separate', '-evaluate-sequence', 'max', '-threshold', threshold, mask]);
    return mask;
  });
  const args = [masks[0]];
  for (const mask of masks.slice(1)) args.push(mask, '-compose', 'multiply', '-composite');
  execFileSync('magick', [...args, diff]);
  masks.forEach(mask => fs.rmSync(mask));
  return parseFloat(execFileSync('magick', [diff, '-format', '%[fx:mean]', 'info:'], { encoding: 'utf8' }));
}

/** ImageMagick prints the metric on stderr, exit 0 (same) or 1 (different). */
function metric(args: string[]): string {
  const result = spawnSync('magick', args, { encoding: 'utf8' });
  if (result.status !== 0 && result.status !== 1) throw new Error(`magick ${args.join(' ')}: ${result.stderr}`);
  return result.stderr.trim();
}

/**
 * PSNR in dB of `local` against `reference`, both scaled to 300 px on the
 * longer side. At that size different resampling (Glide at slot size vs
 * production's 2400 px) no longer counts, a shifted frame still does:
 * same framing ~38 dB, a 10 px shift in the source ~22 dB.
 */
export function psnr(local: string, reference: string, scaled: string): number {
  const [w, h] = size(local);
  const factor = 300 / Math.max(w, h);
  const target = `${Math.round(w * factor)}x${Math.round(h * factor)}!`;
  execFileSync('magick', [local, '-resize', target, `${scaled}.local.png`]);
  execFileSync('magick', [reference, '-auto-orient', '-resize', target, scaled]);
  const output = metric(['compare', '-metric', 'PSNR', `${scaled}.local.png`, scaled, 'null:']);
  return /inf/i.test(output) ? Infinity : parseFloat(output);
}
