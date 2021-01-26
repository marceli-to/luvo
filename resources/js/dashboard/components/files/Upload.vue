<template>
  <div>
    <vue-dropzone
      ref="dropzone"
      id="dropzone"
      :options="fileConfig"
      @vdropzone-complete="complete"
    ></vue-dropzone>
    <span class="bubble is-restriction">{{restrictions}}</span>
  </div>
</template>
<script>
import vue2Dropzone from "vue2-dropzone";
import fileConfig from "@/global/components/files/config/config.js";

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
      fileConfig: fileConfig,
      messages: {
        uploadError: 'Invalid format or file to big!'
      }
    };
  },

  created() {
    this.fileConfig.acceptedFiles = this.$props.acceptedFiles;
    this.fileConfig.maxFiles = this.$props.maxFiles;
    this.fileConfig.maxFilesize = this.$props.maxFilesize;
  },

  methods: {
    complete(file) {
      if (file.status == "error" && file.accepted == false) {
        this.$notify({ type: "error", text: this.messages.uploadError });
      } 
      else {
        let response = JSON.parse(file.xhr.response);
        this.$parent.storeFile(response);
      }
      this.$refs.dropzone.removeFile(file);
    },
  }
};
</script>