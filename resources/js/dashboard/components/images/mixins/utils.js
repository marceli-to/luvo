export default {
  methods: {
    getSource(image, template, maxWidth = 2000, maxHeight = 1250) {

      if (template == 'thumbnail') {
        return `/img/thumbnail/${image.name}`;
      }

      if (template == 'original') {
        return `/img/original/${image.name}`;
      }

      let coords = '';
      if (image.coords_w && image.coords_h) {
        coords = `?w=${maxWidth}&h=${maxHeight}&c=${image.coords_w},${image.coords_h},${image.coords_x},${image.coords_y}`;
        return `/img/cache/${image.name}${coords}`;
      }

      if (image.orientation && image.orientation == 'l') {
        return `/img/cache/${image.name}?w=${maxWidth}&h=${maxHeight}&c`;
      }

      if (image.orientation && image.orientation == 'p') {
        return `/img/cache/${image.name}?w=${maxHeight}&h=${maxWidth}&c`;
      }
    },
  }
};
