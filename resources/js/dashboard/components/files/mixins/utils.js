export default {

  methods: {
    getSource(asset, size) {
      return `/image/${size}/${asset}`;
    },
  }
};
