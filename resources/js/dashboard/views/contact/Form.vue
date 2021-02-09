<template>
  <div>
    <loading-indicator v-if="isLoading"></loading-indicator>
    <form @submit.prevent="submit" class="half-width" v-if="isFetched">
      <header class="content-header">
        <h1>{{title}}</h1>
      </header>
      <tabs :tabs="tabs" :errors="errors"></tabs>
      <div v-show="tabs.data.active">
        <language-tabs :languages="languageTabs"></language-tabs>
        <div v-show="languageTabs.de.active">
          <div :class="[this.errors.address ? 'has-error' : '', 'form-row']">
            <label>Adresse</label>
            <tinymce-editor
              :api-key="tinyApiKey"
              :init="tinyConfig"
              v-model="contact.address.de"
            ></tinymce-editor>
          </div>
          <div class="form-row">
            <label>Impressum</label>
            <tinymce-editor
              :api-key="tinyApiKey"
              :init="tinyConfig"
              v-model="contact.imprint.de"
            ></tinymce-editor>
          </div>
          <div class="form-row">
            <label>Google Maps Uri</label>
            <input type="text" v-model="contact.map_uri">
          </div>
        </div>
        <div v-show="languageTabs.fr.active">
          <div class="form-row">
            <label>Text</label>
            <tinymce-editor
              :api-key="tinyApiKey"
              :init="tinyConfig"
              v-model="contact.address.fr"
            ></tinymce-editor>
          </div>
          <div class="form-row">
            <label>Impressum</label>
            <tinymce-editor
              :api-key="tinyApiKey"
              :init="tinyConfig"
              v-model="contact.imprint.fr"
            ></tinymce-editor>
          </div>
        </div>
        <div v-show="languageTabs.en.active">
          <div class="form-row">
            <label>Text</label>
            <tinymce-editor
              :api-key="tinyApiKey"
              :init="tinyConfig"
              v-model="contact.address.en"
            ></tinymce-editor>
          </div>
          <div class="form-row">
            <label>Impressum</label>
            <tinymce-editor
              :api-key="tinyApiKey"
              :init="tinyConfig"
              v-model="contact.imprint.en"
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
              :images="contact.images"
              :imagePreviewRoute="'cache'"
              :aspectRatioW="4"
              :aspectRatioH="3"
            ></image-edit>
          </div>
        </div>
      </div>

      <div v-show="tabs.settings.active">
        <div>
          <div class="form-row is-last">
            <radio-button 
              :label="'Publizieren?'"
              v-bind:publish.sync="contact.publish"
              :model="contact.publish"
              :name="'publish'">
            </radio-button>
          </div>
        </div>
      </div>
      <footer class="module-footer">
        <div>
          <button type="submit" class="btn-primary">Speichern</button>
          <router-link :to="{ name: 'contact' }" class="btn-secondary">
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
import LanguageTabs from "@/components/ui/LanguageTabs.vue";
import ImageUpload from "@/components/images/Upload.vue";
import ImageEdit from "@/views/contact/images/Edit.vue";

// Tabs config
import tabsConfig from "@/views/contact/config/tabs.js";
import languageTabsConfig from "@/config/languageTabs.js";

export default {
  components: {
    ArrowLeftIcon,
    TinymceEditor,
    RadioButton,
    LabelRequired,
    ImageUpload,
    ImageEdit,
    Tabs,
    LanguageTabs
  },

  mixins: [ErrorHandling],

  props: {
    type: String
  },

  data() {
    return {
      
      // Model
      contact: {
        address: {
          de: null,
          fr: null,
          en: null,
        },
        imprint: {
          de: null,
          fr: null,
          en: null,
        },
        images: [],
        map_uri: null,
        publish: 1,
      },

      // Validation
      errors: {
        address: false,
      },

      // Loading states
      isFetched: true,
      isLoading: false,

      // Tabs config
      tabs: tabsConfig,
      languageTabs: languageTabsConfig,

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
      this.isLoading = true;
      let uri = `/api/contact/${this.$route.params.id}`;
      this.axios.get(uri).then(response => {
        this.contact = response.data;
        this.isFetched = true;
        this.isLoading = false;
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
      this.axios.get(`/api/files`).then(response => {
        this.fileList = response.data;
      });
    },

    store() {
      this.isLoading = true;
      this.axios.post('/api/contact', this.contact).then(response => {
        this.$router.push({ name: "contact" });
        this.$notify({ type: "success", text: "Daten erfasst!" });
        this.isLoading = false;
      });
    },

    update() {
      let uri = `/api/contact/${this.$route.params.id}`;
      this.isLoading = true;
      this.axios.put(uri, this.contact).then(response => {
        this.$router.push({ name: "contact" });
        this.$notify({ type: "success", text: "Änderungen gespeichert!" });
        this.isLoading = false;
      });
    },

    // Store uploaded image
    storeImage(upload) {
      let image = {
        id: null,
        name: upload.name,
        caption: null,
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
        image.contact_id = this.$route.params.id;
        this.axios.post('/api/contact/image', image).then(response => {
          this.$notify({ type: "success", text: "Bild gespeichert!" });
          image.id = response.data.contactImageId;
          this.contact.images.push(image);
        });
      }
      else {
        this.contact.images.push(image);
      }
    },

    // Delete by name
    destroyImage(image, event) {
      if (confirm("Bitte löschen bestätigen!")) {
        let uri = `/api/contact/image/${image}`;
        this.isLoading = true;
        this.axios.delete(uri).then(response => {
          const index = this.contact.images.findIndex(x => x.name === image);
          this.contact.images.splice(index, 1);
          this.isLoading = false;
        });
      }
    },

    // Toggle image status
    toggleImage(image, event) {
      if (image.id === null) {
        const index = this.contact.images.findIndex(x => x.name === image.name);
        this.contact.images[index].publish = image.publish == 1 ? 0 : 1;
      } else {
        let uri = `/api/contact/image/state/${image.id}`;
        this.isLoading = true;
        this.axios.get(uri).then(response => {
          const index = this.contact.images.findIndex(x => x.id === image.id);
          this.contact.images[index].publish = response.data;
          this.isLoading = false;
        });
      }
    },

    // Save coords
    saveImageCoords(image) {
      if (image.id === null) {
        const index = this.contact.images.findIndex(x => x.name === image.name);
        this.contact.images[index].coords = image.coords;
      } 
      else {
        let uri = `/api/contact/image/${image.id}`;
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
        ? "Kontakt bearbeiten" 
        : "Kontakt hinzufügen";
    }
  }
};
</script>
