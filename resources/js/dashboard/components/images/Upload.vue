<template>
  <div>
    <label>Upload</label>
    <dropzone
      ref="dropzone"
      id="dropzone"
      :options="config"
      @complete="complete"
    ></dropzone>
    <span class="bubble is-restriction">{{restrictions}}</span>
  </div>
</template>
<script>
import Dropzone from "@/components/ui/Dropzone.vue";
import config from "@/components/images/config/config.js";

export default {

  components: {
    Dropzone,
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