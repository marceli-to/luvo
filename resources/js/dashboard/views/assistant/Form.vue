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
        <div :class="[this.errors.description ? 'has-error' : '', 'form-row']">
          <label>Beschreibung</label>
          <tinymce-editor
            :api-key="tinyApiKey"
            :init="tinyConfig"
            v-model="assistant.description.de"
          ></tinymce-editor>
        </div>
        <div class="form-row">
          <label>Assistenten</label>
          <tinymce-editor
            :api-key="tinyApiKey"
            :init="tinyConfig"
            v-model="assistant.assistants.de"
          ></tinymce-editor>
        </div>
      </div>
      <div v-show="languageTabs.fr.active">
        <div class="form-row">
          <label>Beschreibung</label>
          <tinymce-editor
            :api-key="tinyApiKey"
            :init="tinyConfig"
            v-model="assistant.description.fr"
          ></tinymce-editor>
        </div>
        <div class="form-row">
          <label>Assistenten</label>
          <tinymce-editor
            :api-key="tinyApiKey"
            :init="tinyConfig"
            v-model="assistant.assistants.fr"
          ></tinymce-editor>
        </div>
      </div>
      <div v-show="languageTabs.en.active">
        <div class="form-row">
          <label>Beschreibung</label>
          <tinymce-editor
            :api-key="tinyApiKey"
            :init="tinyConfig"
            v-model="assistant.description.en"
          ></tinymce-editor>
        </div>
        <div class="form-row">
          <label>Assistenten</label>
          <tinymce-editor
            :api-key="tinyApiKey"
            :init="tinyConfig"
            v-model="assistant.assistants.en"
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
            :images="assistant.images"
            :imagePreviewRoute="'cache'"
            :aspectRatioW="4"
            :aspectRatioH="3"
          ></image-edit>
        </div>
      </div>
    </div>
    <div v-show="tabs.settings.active">
      <div>
        <div :class="[this.errors.team_id ? 'has-error' : '', 'form-row']">
          <label>Team*</label>
          <div class="select-wrapper is-medium">
            <select v-model="assistant.team_id" name="layout">
              <option v-for="(team, index) in teams" :key="index" :value="team.id">{{ team.category.name }}</option>
            </select>
          </div>
        </div>  
        <div class="form-row is-last">
          <radio-button 
            :label="'Publizieren?'"
            v-bind:publish.sync="assistant.publish"
            :model="assistant.publish"
            :name="'publish'">
          </radio-button>
        </div>
      </div>
    </div>
    <footer class="module-footer">
      <div>
        <button type="submit" class="btn-primary">Speichern</button>
        <router-link :to="{ name: 'teams' }" class="btn-secondary">
          <span>Zurück</span>
        </router-link>
      </div>
    </footer>
  </form>
</div>
</template>
<script>

// Icons
import { ArrowLeftIcon, PlusIcon } from 'vue-feather-icons';

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
import ImageEdit from "@/views/assistant/images/Edit.vue";
import ListActions from "@/components/ui/ListActions.vue";
import draggable from "vuedraggable";

// Tabs config
import tabsConfig from "@/views/assistant/config/tabs.js";
import languageTabsConfig from "@/config/languageTabs.js";

export default {
  components: {
    ArrowLeftIcon,
    PlusIcon,
    TinymceEditor,
    RadioButton,
    LabelRequired,
    ImageUpload,
    ImageEdit,
    Tabs,
    LanguageTabs,
    ListActions,
    draggable
  },

  mixins: [ErrorHandling],

  props: {
    type: String
  },

  data() {
    return {
      
      // Model
      assistant: {
        description: {
          de: null,
          fr: null,
          en: null,
        },
        assistants: {
          de: null,
          fr: null,
          en: null,
        },
        team_id: 1,
        images: [],
        publish: 1,
      },

      teams: null,

      // Validation
      errors: {
        description: false,
        team_id: false
      },

      // Loading states
      isFetched: true,
      isLoading: false,
      isEdit: false,

      // Tabs config
      tabs: tabsConfig,
      languageTabs: languageTabsConfig,

      // TinyMCE
      tinyConfig: tinyConfig,
      tinyApiKey: 'vuaywur9klvlt3excnrd9xki1a5lj25v18b2j0d0nu5tbwro',
    };
  },

  created() {
    if (this.$props.type == "edit") {
      this.isEdit = true;
      this.isFetched = false;
      this.isLoading = true;

      // Get assistants
      this.axios.get(`/api/assistant/${this.$route.params.id}`)
        .then(response => {
          this.assistant = response.data;

          // Get teams
          this.axios.get(`/api/team`)
          .then(response => {
            this.teams = response.data.data;
            this.isFetched = true;
            this.isLoading = false;
          });
      });
    }
    else {
      this.isLoading = true;
      this.isFetched = false;
      let uri = `/api/team`;
      this.axios.get(uri).then(response => {
        this.teams = response.data.data;
        this.isFetched = true;
        this.isLoading = false;
      });
    }

    // Get files for tinymce
    let _this = this;
    this.tinyConfig.link_list = function(success) {
      _this.axios.get(`/api/files`).then(response => {
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

    store() {
      this.isLoading = true;
      this.axios.post('/api/assistant', this.assistant).then(response => {
        this.$router.push({ name: "teams" });
        this.$notify({ type: "success", text: "Daten erfasst!" });
        this.isLoading = false;
      });
    },

    update() {
      let uri = `/api/assistant/${this.$route.params.id}`;
      this.isLoading = true;
      this.axios.put(uri, this.assistant).then(response => {
        this.$router.push({ name: "teams" });
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
        device: 'desktop',
        order: 0,
        publish: 1,
      }

      if (this.$props.type == "edit") {
        image.assistant_id = this.$route.params.id;
        this.axios.post('/api/assistant/image', image).then(response => {
          this.$notify({ type: "success", text: "Bild gespeichert!" });
          image.id = response.data.assistantImageId;
          this.assistant.images.push(image);
        });
      }
      else {
        this.assistant.images.push(image);
      }
    },

    // Delete by name
    destroyImage(image, event) {
      if (confirm("Bitte löschen bestätigen!")) {
        let uri = `/api/assistant/image/${image}`;
        this.isLoading = true;
        this.axios.delete(uri).then(response => {
          const index = this.assistant.images.findIndex(x => x.name === image);
          this.assistant.images.splice(index, 1);
          this.isLoading = false;
        });
      }
    },

    // Toggle image status
    toggleImage(image, event) {
      if (image.id === null) {
        const index = this.assistant.images.findIndex(x => x.name === image.name);
        this.assistant.images[index].publish = image.publish == 1 ? 0 : 1;
      } else {
        let uri = `/api/assistant/image/state/${image.id}`;
        this.isLoading = true;
        this.axios.get(uri).then(response => {
          const index = this.assistant.images.findIndex(x => x.id === image.id);
          this.assistant.images[index].publish = response.data;
          this.isLoading = false;
        });
      }
    },

    // Save coords
    saveImageCoords(image) {
      if (image.id === null) {
        const index = this.assistant.images.findIndex(x => x.name === image.name);
        this.assistant.images[index].coords = image.coords;
      } 
      else {
        let uri = `/api/assistant/image/${image.id}`;
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
        ? "Assistenten bearbeiten" 
        : "Assistenten hinzufügen";
    }
  }
};
</script>
