<template>
<div>
  <loading-indicator v-if="isLoading"></loading-indicator>
  <form @submit.prevent="submit" class="half-width" v-if="isFetched">
    <header class="content-header">
      <h1>{{title}}</h1>
    </header>
    <tabs :tabs="tabs" :errors="errors"></tabs>
    <div v-show="tabs.data.active">
      <div>
        <div :class="[this.errors.title ? 'has-error' : '', 'form-row']">
          <label>Titel*</label>
          <input type="text" v-model="news.title.de">
          <label-required />
        </div>
        <div class="form-row">
          <label>Subtitel</label>
          <input type="text" v-model="news.subtitle.de">
        </div>
        <div class="form-row">
          <label>Text</label>
          <tinymce-editor
            :api-key="tinyApiKey"
            :init="tinyConfig"
            v-model="news.text.de"
          ></tinymce-editor>
        </div>
        <div class="form-row is-last">
          <radio-button 
            :label="'Publizieren?'"
            v-bind:publish.sync="news.publish"
            :model="news.publish"
            :name="'publish'">
          </radio-button>
        </div>
      </div>
    </div>
    <div v-show="tabs.translation.active">
      <div>
        <div class="form-row">
          <label>Titel</label>
          <input type="text" v-model="news.title.en">
        </div>
        <div class="form-row">
          <label>Subtitel</label>
          <input type="text" v-model="news.subtitle.en">
        </div>
        <div class="form-row">
          <label>Text</label>
          <tinymce-editor
            :api-key="tinyApiKey"
            :init="tinyConfig"
            v-model="news.text.en"
          ></tinymce-editor>
        </div>
      </div>
    </div>
    <div v-show="tabs.image.active">
      <div>
        <div class="form-row">
          <image-upload
            :restrictions="'jpg, png | max. 8 MB'"
            :maxFiles="99"
            :maxFilesize="8"
            :acceptedFiles="'.png,.jpg'"
          ></image-upload>
        </div>
        <div class="form-row">
          <image-edit 
            :images="news.images"
            :imagePreviewRoute="'cache'"
            :aspectRatioW="4"
            :aspectRatioH="3"
          ></image-edit>
        </div>
      </div>
    </div>
    <footer class="module-footer">
      <div>
        <button type="submit" class="btn-primary">Speichern</button>
        <router-link :to="{ name: 'news' }" class="btn-secondary">
          <span>Zurück</span>
        </router-link>
      </div>
    </footer>
  </form>
</div>
</template>
<script>

// Icons
import { ArrowLeftIcon } from 'vue-feather-icons';

// Mixins
import ErrorHandling from "@/mixins/ErrorHandling";

// TinyMCE
import tinyConfig from "@/config/tiny.js";
import TinymceEditor from "@tinymce/tinymce-vue";

// Components
import RadioButton from "@/components/ui/RadioButton.vue";
import LabelRequired from "@/components/ui/LabelRequired.vue";
import Tabs from "@/components/ui/Tabs.vue";
import ImageUpload from "@/components/images/Upload.vue";
import ImageEdit from "@/views/news/images/Edit.vue";

// Tabs config
import tabsConfig from "@/views/news/config/tabs.js";

export default {
  components: {
    ArrowLeftIcon,
    TinymceEditor,
    RadioButton,
    LabelRequired,
    ImageUpload,
    ImageEdit,
    Tabs
  },

  mixins: [ErrorHandling],

  props: {
    type: String
  },

  data() {
    return {
      
      // Model
      news: {
        title: {
          de: null,
          en: null,
        },
        subtitle: {
          de: null,
          en: null,
        },
        text: {
          de: null,
          en: null,
        },
        images: [],
        publish: 1,
      },

      // Validation
      errors: {
        title: false,
      },

      // Loading states
      isFetched: true,
      isLoading: false,

      // Tabs config
      tabs: tabsConfig,

      // TinyMCE
      tinyConfig: tinyConfig,
      tinyApiKey: 'vuaywur9klvlt3excnrd9xki1a5lj25v18b2j0d0nu5tbwro',

      // Filelist for Tiny Links
      fileList: null,
    };
  },

  created() {
    if (this.$props.type == "edit") {
      this.isFetched = false;
      let uri = `/api/news/${this.$route.params.id}`;
      this.axios.get(uri).then(response => {
        this.news = response.data;
        this.isFetched = true;
      });
    }
    
    // 
    //this.fetchFiles();
    let _this = this;
    this.tinyConfig.link_list = function(success) {
      _this.axios.get(`/api/files/get`).then(response => {
        success(response.data);
      });
    }
  },

  methods: {

    // Submit form
    submit() {
      if (this.$props.type == "edit") {
        this.update();
      }

      if (this.$props.type == "create") {
        this.store();
      }
    },

    fetchFiles() {
      this.axios.get(`/api/files/get`).then(response => {
        this.fileList = response.data;
      });
    },

    store() {
      this.isLoading = true;
      this.axios.post('/api/news', this.news).then(response => {
        this.$router.push({ name: "news" });
        this.$notify({ type: "success", text: "Illustration erfasst!" });
        this.isLoading = false;
      });
    },

    update() {
      let uri = `/api/news/${this.$route.params.id}`;
      this.isLoading = true;
      this.axios.put(uri, this.news).then(response => {
        this.$router.push({ name: "news" });
        this.$notify({ type: "success", text: "Änderungen gespeichert!" });
        this.isLoading = false;
      });
    },

    // Store uploaded image
    storeImage(upload) {
      let image = {
        id: null,
        name: upload.name,
        caption: { de: null, en: null },
        coords_w: 0,
        coords_h: 0,
        coords_x: 0,
        coords_y: 0,
        orientation: upload.orientation,
        preview: 0,
        order: 0,
        publish: 1,
      }

      if (this.$props.type == "edit") {
        image.news_id = this.$route.params.id;
        this.axios.post('/api/news/image', image).then(response => {
          this.$notify({ type: "success", text: "Bild gespeichert!" });
          image.id = response.data.newsImageId;
          this.news.images.push(image);
        });
      }
      else {
        this.news.images.push(image);
      }
    },

    // Delete by name
    destroyImage(image, event) {
      if (confirm("Bitte löschen bestätigen!")) {
        let uri = `/api/news/image/${image}`;
        this.isLoading = true;
        this.axios.delete(uri).then(response => {
          const index = this.news.images.findIndex(x => x.name === image);
          this.news.images.splice(index, 1);
          this.isLoading = false;
        });
      }
    },

    // Toggle image status
    toggleImage(image, event) {
      if (image.id === null) {
        const index = this.news.images.findIndex(x => x.name === image.name);
        this.news.images[index].publish = image.publish == 1 ? 0 : 1;
      } else {
        let uri = `/api/news/image/state/${image.id}`;
        this.isLoading = true;
        this.axios.get(uri).then(response => {
          const index = this.news.images.findIndex(x => x.id === image.id);
          this.news.images[index].publish = response.data;
          this.isLoading = false;
        });
      }
    },

    // Save coords
    saveImageCoords(image) {
      if (image.id === null) {
        const index = this.news.images.findIndex(x => x.name === image.name);
        this.news.images[index].coords = image.coords;
      } 
      else {
        let uri = `/api/news/image/${image.id}`;
        this.isLoading = true;
        this.axios.put(uri, image).then(response => {
          this.$notify({ type: "success", text: "Änderungen gespeichert!" });
          this.isLoading = false;
        });
      }
    },
  },

  computed: {
    title: function() {
      return this.$props.type == "edit" 
        ? "News bearbeiten" 
        : "News hinzufügen";
    }
  }
};
</script>
