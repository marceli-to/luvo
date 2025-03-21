export default {
  methods: {
    getSource(image, template, maxWidth = 1600, maxHeight = 1000) {

      if (template == 'thumbnail') {
        return `/img/thumbnail/${image.name}`;
      }

      if (template == 'original') {
        return `/img/original/${image.name}`;
      }

      let coords = '';
      if (image.coords_w && image.coords_h) {
        coords = `/${maxWidth}/${maxHeight}/${image.coords_w},${image.coords_h},${image.coords_x},${image.coords_y}`;
        return `/img/crop/${image.name}${coords}`;
      }

      if (image.orientation && image.orientation == 'l') {
        return `/img/crop/${image.name}/${maxWidth}/${maxHeight}`;
      }

      if (image.orientation && image.orientation == 'p') {
        return `/img/crop/${image.name}/${maxHeight}/${maxWidth}`;
      }
    },
  }
};
