export default {

  data() {
    return {
      hasOverlayEdit: false,

      overlayItem: {
        name: '',
        caption: null
      },

      defaults: {
        item: {
          name: '',
          caption: null
        }
      }
    }
  },

  mounted() {
    window.addEventListener("keyup", event => {
      if (event.which === 27) {
        this.hideEdit();
      }
    });
  },

  methods: {
    toggle(image, $event) {
      this.$parent.toggleImage(image, $event)
    },

    destroy(image, $event) {
      this.$parent.destroyImage(image, $event)
    },

    update(image, $event) {
      this.$parent.updateImage(image, $event)
      this.hideEdit();
    },

    showEdit(image, $event) {
      this.hasOverlayEdit = true;
      this.overlayItem = image;
    },
    
    hideEdit() {
      this.hasOverlayEdit = false;
      this.overlayItem = this.defaults.item;
    },
  }
};