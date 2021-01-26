<template>
  <div>
    <label>Upload</label>
    <vue-dropzone
      ref="dropzone"
      id="dropzone"
      :options="config"
      @vdropzone-complete="complete"
    ></vue-dropzone>
    <span class="bubble is-restriction">{{restrictions}}</span>
  </div>
</template>
<script>
import vue2Dropzone from "vue2-dropzone";
import config from "@/components/images/config/config.js";

export default {

  components: {
    vueDropzone: vue2Dropzone,
  },

  props: {
    restrictions: String,
    acceptedFiles: String,
    maxFiles: Number,
    maxFilesize: Number,
  },

  data() {
    return {
      config: config,
      messages: {
        uploadError: 'Invalid format or file to big!'
      }
    };
  },

  created() {
    this.config.acceptedFiles = this.$props.acceptedFiles;
    this.config.maxFiles = this.$props.maxFiles;
    this.config.maxFilesize = this.$props.maxFilesize;
  },

  methods: {
    complete(image) {
      if (image.status == "error" && image.accepted == false) {
        this.$notify({ type: "error", text: this.messages.uploadError });
      } 
      else {
        let response = JSON.parse(image.xhr.response);
        this.$parent.storeImage(response);
      }
      this.$refs.dropzone.removeFile(image);
    },
  }
};
</script>