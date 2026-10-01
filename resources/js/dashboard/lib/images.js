/**
 * Url of an uploaded image, served by the Glide ImageController.
 *
 * template: 'thumbnail' | 'original' | anything else = crop (with the
 * image's coords if it has any, else scaled by orientation)
 */
export function imageUrl(image, template, maxWidth = 1600, maxHeight = 1000) {
  if (template === 'thumbnail') {
    return `/img/thumbnail/${image.name}`;
  }

  if (template === 'original') {
    return `/img/original/${image.name}`;
  }

  if (image.coords_w && image.coords_h) {
    return `/img/crop/${image.name}/${maxWidth}/${maxHeight}/${image.coords_w},${image.coords_h},${image.coords_x},${image.coords_y}`;
  }

  return image.orientation === 'p'
    ? `/img/crop/${image.name}/${maxHeight}/${maxWidth}`
    : `/img/crop/${image.name}/${maxWidth}/${maxHeight}`;
}

/**
 * Resolves once the browser has loaded the image at url.
 */
export function preloadImage(url) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.onload = () => resolve(url);
    img.onerror = reject;
    img.src = url;
  });
}
