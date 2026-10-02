import { select } from '../e2e/support/db';

/**
 * Differences between this branch and production that are intended. Each
 * is masked (same box on both sides) in the layout screenshots, with the
 * reason; nothing is hidden by raising the tolerance.
 */
export type ExpectedDifference = {
  reason: string;
  pages: RegExp;
  /** Masked on both sides */
  selector: string;
  /** Only at this viewport width */
  width?: number;
  /** Compare these parts separately instead of the full page (when the difference moves what follows) */
  parts?: string[];
};

/** Saved crops anchored at x = 0, y = 0: production drops them (05-image-pipeline.md, bug 3). */
const topLeftCrops = select<{ name: string }>(`
  SELECT name FROM team_member_images WHERE coords_w > 0 AND COALESCE(coords_x, 0) = 0 AND COALESCE(coords_y, 0) = 0
  UNION ALL SELECT name FROM team_images WHERE coords_w > 0 AND COALESCE(coords_x, 0) = 0 AND COALESCE(coords_y, 0) = 0
  UNION ALL SELECT name FROM contact_images WHERE coords_w > 0 AND COALESCE(coords_x, 0) = 0 AND COALESCE(coords_y, 0) = 0
  UNION ALL SELECT name FROM assistant_images WHERE coords_w > 0 AND COALESCE(coords_x, 0) = 0 AND COALESCE(coords_y, 0) = 0
`).map(row => row.name);

export const expectedDifferences: ExpectedDifference[] = [
  {
    reason: 'Image pixels: AVIF at the slot size now, production scales a 2400 px JPEG in the browser (fix 2). '
      + 'The boxes stay in the comparison; the pixels are compared by the framing tests.',
    pages: /./,
    selector: 'picture',
  },
  {
    reason: 'Home hero: the saved crop is applied now; production ignores it (commit 39fbd61).',
    pages: /\/home$/,
    selector: 'figure.visual-wide',
  },
  {
    reason: 'Home hero at 375 px: with the saved crop (16:10) the hero is lower than production\'s uncropped image, '
      + 'which moves the text; header and text are compared on their own (the footer is hidden at 375 px).',
    pages: /\/home$/,
    width: 375,
    selector: 'figure.visual-wide',
    parts: ['header.site-header', 'section.content-wide'],
  },
  ...topLeftCrops.map(name => ({
    reason: `Crop anchored top left (${name}): applied now, ignored on production (fix 3).`,
    pages: /./,
    selector: `figure:has(img[src*="${name}"])`,
  })),
  // The member image on tall windows (production: HTTP 400) is outside these
  // viewports (375 and 1280 × 800); public-tall covers it (pp-member-tall).
];

export function masksFor(url: string, width: number) {
  return expectedDifferences.filter(difference => difference.pages.test(url) && (!difference.width || difference.width === width));
}
