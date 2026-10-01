<template>
<div>
  <loading-indicator v-if="isLoading"></loading-indicator>
  <form @submit.prevent="submit" class="half-width" v-if="isFetched">
    <header class="content-header">
      <h1>{{title}}</h1>
    </header>
    <language-tabs :languages="languageTabs"></language-tabs>
    <div>
      <div v-show="languageTabs.de.active">
        <div>
          <div :class="[errors.title ? 'has-error' : '', 'form-row']">
            <label>Titel*</label>
            <input type="text" v-model="publication.title.de">
            <label-required />
          </div>
          <div class="form-row">
            <label>Beschreibung</label>
            <tinymce-editor
                :init="tinyConfig"
              v-model="publication.description.de"
            ></tinymce-editor>
          </div>
          <div :class="[errors.articles ? 'has-error' : '', 'form-row']">
            <label>Artikel</label>
            <tinymce-editor
                :init="tinyConfig"
              v-model="publication.articles.de"
            ></tinymce-editor>
          </div>
          <div class="form-row is-last">
            <radio-button 
              :label="'Publizieren?'"
              v-model:publish="publication.publish"
              :model="publication.publish"
              :name="'publish'">
            </radio-button>
          </div>
        </div>
      </div>
      <div v-show="languageTabs.fr.active">
        <div>
          <div class="form-row">
            <label>Titel</label>
            <input type="text" v-model="publication.title.fr">
          </div>
          <div class="form-row">
            <label>Beschreibung</label>
            <tinymce-editor
                :init="tinyConfig"
              v-model="publication.description.fr"
            ></tinymce-editor>
          </div>
          <div class="form-row">
            <label>Artikel</label>
            <tinymce-editor
                :init="tinyConfig"
              v-model="publication.articles.fr"
            ></tinymce-editor>
          </div>
        </div>
      </div>
      <div v-show="languageTabs.en.active">
        <div>
          <div class="form-row">
            <label>Titel</label>
            <input type="text" v-model="publication.title.en">
          </div>
          <div class="form-row">
            <label>Beschreibung</label>
            <tinymce-editor
                :init="tinyConfig"
              v-model="publication.description.en"
            ></tinymce-editor>
          </div>
          <div class="form-row">
            <label>Artikel</label>
            <tinymce-editor
                :init="tinyConfig"
              v-model="publication.articles.en"
            ></tinymce-editor>
          </div>
        </div>
      </div>
    </div>
    <footer class="module-footer">
      <div>
        <button type="submit" class="btn-primary">Speichern</button>
        <router-link :to="{ name: 'team-member-edit', params: { id: publication.team_member_id } }" class="btn-secondary">
          <span>Zurück</span>
        </router-link>
      </div>
    </footer>
  </form>
</div>
</template>
<script>

// Icons
import { ArrowLeftIcon } from 'lucide-vue-next';

// Mixins
import ErrorHandling from "@/mixins/ErrorHandling";

// TinyMCE
import tinyConfig from "@/config/tiny.js";
import TinymceEditor from "@/components/ui/TinymceEditor.js";

// Components
import RadioButton from "@/components/ui/RadioButton.vue";
import LabelRequired from "@/components/ui/LabelRequired.vue";
import LanguageTabs from "@/components/ui/LanguageTabs.vue";

// LanguageTabs config
import languageTabsConfig from "@/config/languageTabs.js";

export default {
  components: {
    ArrowLeftIcon,
    TinymceEditor,
    RadioButton,
    LabelRequired,
    LanguageTabs
  },

  mixins: [ErrorHandling],

  props: {
    type: String
  },

  data() {
    return {
      
      // Model
      publication: {
        title: {
          de: null,
          fr: null,
          en: null,
        },
        description: {
          de: null,
          fr: null,
          en: null,
        },
        articles: {
          de: null,
          fr: null,
          en: null,
        },
        team_member_id: null,
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
      languageTabs: languageTabsConfig,

      // TinyMCE
      tinyConfig: tinyConfig,
      tinyApiKey: 'vuaywur9klvlt3excnrd9xki1a5lj25v18b2j0d0nu5tbwro',
    };
  },

  created() {

    if (this.$props.type == "edit") {
      this.isFetched = false;
      this.isLoading = true;
      let uri = `/api/publication/${this.$route.params.id}`;
      this.axios.get(uri).then(response => {
        this.publication = response.data;
        this.isFetched = true;
        this.isLoading = false;
      });
    }

    if (this.$props.type == 'create') {
      this.publication.team_member_id = this.$route.params.memberId;
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
      this.axios.post('/api/publication', this.publication).then(response => {
        this.$router.push({ name: 'team-member-edit', params: { id: this.publication.team_member_id } });
        this.$notify({ type: "success", text: "Daten erfasst!" });
        this.isLoading = false;
      });
    },

    update() {
      let uri = `/api/publication/${this.$route.params.id}`;
      this.isLoading = true;
      this.axios.put(uri, this.publication).then(response => {
        this.$router.push({ name: 'team-member-edit', params: { id: this.publication.team_member_id } });
        this.$notify({ type: "success", text: "Änderungen gespeichert!" });
        this.isLoading = false;
      });
    },

  },

  computed: {
    title: function() {
      return this.$props.type == "edit" 
        ? "Publikation bearbeiten" 
        : "Publikation hinzufügen";
    }
  }
};
</script>
