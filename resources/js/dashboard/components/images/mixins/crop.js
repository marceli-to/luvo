export default {

  data() {
    return {
      hasOverlayCropper: false,
      coords: {},
      cropW: null,
      cropH: null,
      cropImage: null,

      defaults: {
        w: 425,
        h: 510,
        x: 0,
        y: 0
      },

      isLoading: false,
    };
  },

  mounted() {
    window.addEventListener("keyup", event => {
      if (event.which === 27) {
        this.hideCropper();
      }
    });
  },

  methods: {

    change({ coordinates, canvas }) {
      this.coords.h = coordinates.height;
      this.coords.w = coordinates.width;
      this.coords.y = coordinates.top;
      this.coords.x = coordinates.left;
      this.cropW = Math.floor(coordinates.width);
      this.cropH = Math.floor(coordinates.height);
    },

    saveCoords(image) {
      image.coords_w = this.coords.w;
      image.coords_h = this.coords.h;
      image.coords_x = this.coords.x;
      image.coords_y = this.coords.y;
      this.$parent.saveImageCoords(image);
      this.hideCropper();
    },

    defaultPosition() {
      let x = this.overlayItem.coords_x || this.defaults.x;
      let y = this.overlayItem.coords_y || this.defaults.y;
      return {
        left: x,
        top: y
      };
    },

    defaultSize() {
      let w = this.overlayItem.coords_w || this.defaults.w;
      let h = this.overlayItem.coords_h || this.defaults.h;
      return {
        width: w,
        height: h
      };
    },
    
    showCropper(image) {
      this.isLoading = true;
      this.hasOverlayCropper = true;
      this.overlayItem = image;
      this.axios.get(this.getSource(image, "original")).then(response => {
        this.cropImage = response.config.url;
        this.isLoading = false;
      });
    },

    hideCropper() {
      this.hasOverlayCropper = false;
      this.overlayItem = this.defaults.item;
      this.cropImage = null;
    }
  }
};