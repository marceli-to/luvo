<template>
  <div class="vue-dropzone dropzone" ref="el"></div>
</template>
<script>
import Dropzone from 'dropzone';

Dropzone.autoDiscover = false;

/**
 * Thin wrapper around Dropzone 6, standing in for vue2-dropzone:
 * same `options` prop, emits `complete` (file), exposes removeFile().
 */
export default {

  props: {
    options: {
      type: Object,
      required: true,
    },
  },

  emits: ['complete'],

  mounted() {
    // vue2-dropzone defaults, merged under the given options
    this.dropzone = new Dropzone(this.$refs.el, {
      thumbnailWidth: 200,
      thumbnailHeight: 200,
      ...this.options,
    });
    this.dropzone.on('complete', file => this.$emit('complete', file));
  },

  beforeUnmount() {
    this.dropzone.destroy();
  },

  methods: {
    removeFile(file) {
      this.dropzone.removeFile(file);
    },
  },
};
</script>
