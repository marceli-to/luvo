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
        <div :class="[this.errors.firstname ? 'has-error' : '', 'form-row']">
          <label>Vorname*</label>
          <input type="text" v-model="teamMember.firstname">
          <label-required />
        </div>
        <div :class="[this.errors.name ? 'has-error' : '', 'form-row']">
          <label>Name*</label>
          <input type="text" v-model="teamMember.name">
          <label-required />
        </div>
        <div class="form-row">
          <label>Beschreibung</label>
          <tinymce-editor
            :api-key="tinyApiKey"
            :init="tinyConfig"
            v-model="teamMember.description.de"
          ></tinymce-editor>
        </div>
        <div class="form-row">
          <label>Tätigkeitsgebiete</label>
          <tinymce-editor
            :api-key="tinyApiKey"
            :init="tinyConfig"
            v-model="teamMember.area.de"
          ></tinymce-editor>
        </div>
        <div class="form-row">
          <label>Werdegang</label>
          <tinymce-editor
            :api-key="tinyApiKey"
            :init="tinyConfig"
            v-model="teamMember.biography.de"
          ></tinymce-editor>
        </div>
        <div class="form-row">
          <label>Mitgliedschaften</label>
          <tinymce-editor
            :api-key="tinyApiKey"
            :init="tinyConfig"
            v-model="teamMember.membership.de"
          ></tinymce-editor>
        </div>
        <div class="form-row" v-if="isEdit">
          <header class="content-header" style="margin-bottom: 5px">
            <label>Publikationen</label>
            <router-link :to="{ name: 'publication-create', params: { memberId: teamMember.id }}" class="feather-icon feather-icon--prepend">
              <plus-icon size="16"></plus-icon>
              <span>Hinzufügen</span>
            </router-link>
          </header>
          <div class="listing" v-if="teamMember.publications.length">
            <draggable 
              :disabled="false"
              v-model="teamMember.publications" 
              @end="order()"
              ghost-class="draggable-ghost"
              draggable=".listing__item">
              <div
                :class="[p.publish == 0 ? 'is-disabled' : '', 'listing__item is-draggable']"
                v-for="p in teamMember.publications"
                :key="p.id"
              >
                <div class="listing__item-body">
                  {{ p.title.de }}
                </div>
                <list-actions 
                  :id="p.id" 
                  :record="p"
                  :isDraggable="true"
                  :routes="{edit: 'publication-edit'}">
                </list-actions>
              </div>
            </draggable>
          </div>
          <div v-if="!teamMember.publications.length">
            <p class="no-records">Es sind noch keine Publikationen vorhanden...</p>
          </div>
        </div>
        <div class="form-row" v-else>
          <label>Publikationen</label>
          <p>Publikationen können erst nach dem Speichern hinzugefügt werden.</p>
        </div>
      </div>
      <div v-show="languageTabs.fr.active">
        <div>
          <div class="form-row">
            <label>Beschreibung</label>
            <tinymce-editor
              :api-key="tinyApiKey"
              :init="tinyConfig"
              v-model="teamMember.description.fr"
            ></tinymce-editor>
          </div>
          <div class="form-row">
            <label>Tätigkeitsgebiete</label>
            <tinymce-editor
              :api-key="tinyApiKey"
              :init="tinyConfig"
              v-model="teamMember.area.fr"
            ></tinymce-editor>
          </div>
          <div class="form-row">
            <label>Werdegang</label>
            <tinymce-editor
              :api-key="tinyApiKey"
              :init="tinyConfig"
              v-model="teamMember.biography.fr"
            ></tinymce-editor>
          </div>
          <div class="form-row">
            <label>Mitgliedschaften</label>
            <tinymce-editor
              :api-key="tinyApiKey"
              :init="tinyConfig"
              v-model="teamMember.membership.fr"
            ></tinymce-editor>
          </div>
        </div>
      </div>
      <div v-show="languageTabs.en.active">
        <div>
          <div class="form-row">
            <label>Beschreibung</label>
            <tinymce-editor
              :api-key="tinyApiKey"
              :init="tinyConfig"
              v-model="teamMember.description.en"
            ></tinymce-editor>
          </div>
          <div class="form-row">
            <label>Tätigkeitsgebiete</label>
            <tinymce-editor
              :api-key="tinyApiKey"
              :init="tinyConfig"
              v-model="teamMember.area.en"
            ></tinymce-editor>
          </div>
          <div class="form-row">
            <label>Werdegang</label>
            <tinymce-editor
              :api-key="tinyApiKey"
              :init="tinyConfig"
              v-model="teamMember.biography.en"
            ></tinymce-editor>
          </div>
          <div class="form-row">
            <label>Mitgliedschaften</label>
            <tinymce-editor
              :api-key="tinyApiKey"
              :init="tinyConfig"
              v-model="teamMember.membership.en"
            ></tinymce-editor>
          </div>
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
            :images="teamMember.images"
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
            <select v-model="teamMember.team_id" name="layout">
              <option v-for="(team, index) in teams" :key="index" :value="team.id">{{ team.category.name }}</option>
            </select>
          </div>
        </div>  
        <div class="form-row is-last">
          <radio-button 
            :label="'Publizieren?'"
            v-bind:publish.sync="teamMember.publish"
            :model="teamMember.publish"
            :name="'publish'">
          </radio-button>
        </div>
      </div>
    </div>
    <footer class="module-footer">
      <div>
        <button type="submit" class="btn-primary">Speichern</button>
        <router-link :to="{ name: 'team-members' }" class="btn-secondary">
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
import ImageEdit from "@/views/team_member/images/Edit.vue";
import ListActions from "@/components/ui/ListActions.vue";
import draggable from "vuedraggable";

// Tabs config
import tabsConfig from "@/views/team_member/config/tabs.js";
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
      teamMember: {
        firstname: null,
        name: null,
        description: {
          de: null,
          fr: null,
          en: null,
        },
        area: {
          de: null,
          fr: null,
          en: null,
        },
        biography: {
          de: null,
          fr: null,
          en: null,
        },
        membership: {
          de: null,
          fr: null,
          en: null,
        },
        team_id: 1,
        images: [],
        publications: [],
        publish: 1,
      },

      teams: null,

      // Validation
      errors: {
        name: false,
        firstname: false,
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

      // Get team members
      this.axios.get(`/api/team/member/${this.$route.params.id}`)
        .then(response => {
          this.teamMember = response.data;

          // Get teams
          this.axios.get(`/api/team`)
          .then(response => {
            this.teams = response.data.data;
            this.isFetched = true;
          });
      });
    }
    else {
      this.isFetched = false;
      let uri = `/api/team`;
      this.axios.get(uri).then(response => {
        this.teams = response.data.data;
        this.isFetched = true;
      });
    }

    // Get files for tinymce
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

    store() {
      this.isLoading = true;
      this.axios.post('/api/team/member', this.teamMember).then(response => {
        this.$router.push({ name: "teams" });
        this.$notify({ type: "success", text: "Daten erfasst!" });
        this.isLoading = false;
      });
    },

    update() {
      let uri = `/api/team/member/${this.$route.params.id}`;
      this.isLoading = true;
      this.axios.put(uri, this.teamMember).then(response => {
        this.$router.push({ name: "teams" });
        this.$notify({ type: "success", text: "Änderungen gespeichert!" });
        this.isLoading = false;
      });
    },

    toggle(id,event) {
      let uri = `/api/publication/state/${id}`;
      this.isLoading = true;
      this.axios.get(uri).then(response => {
        const index = this.teamMember.publications.findIndex(x => x.id === id);
        this.teamMember.publications[index].publish = response.data;
        this.$notify({ type: "success", text: "Status geändert" });
        this.isLoading = false;
      });
    },

    destroy(id, event) {
      if (confirm("Bitte löschen bestätigen!")) {
        let uri = `/api/publication/${id}`;
        this.isLoading = true;
        this.axios.delete(uri).then(response => {
          const index = this.teamMember.publications.findIndex(x => x.id === id);
          this.teamMember.publications.splice(index, 1);
          this.isLoading = false;
        });
      }
    },

    order() {
      let publications = this.teamMember.publications.map(function(p, index) {
        p.order = index;
        return p;
      });
      if (this.debounce) return;
      this.debounce = setTimeout(function() {
        this.debounce = false 
        this.axios.post(`/api/publication/order`, {publications: publications}).then((response) => {
          this.$notify({type: 'success', text: 'Reihenfolge angepasst'});
        });
      }.bind(this, publications), 500);
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
        image.team_member_id = this.$route.params.id;
        this.axios.post('/api/team/member/image', image).then(response => {
          this.$notify({ type: "success", text: "Bild gespeichert!" });
          image.id = response.data.teamMemberImageId;
          this.teamMember.images.push(image);
        });
      }
      else {
        this.teamMember.images.push(image);
      }
    },

    // Delete by name
    destroyImage(image, event) {
      if (confirm("Bitte löschen bestätigen!")) {
        let uri = `/api/team/member/image/${image}`;
        this.isLoading = true;
        this.axios.delete(uri).then(response => {
          const index = this.teamMember.images.findIndex(x => x.name === image);
          this.teamMember.images.splice(index, 1);
          this.isLoading = false;
        });
      }
    },

    // Toggle image status
    toggleImage(image, event) {
      if (image.id === null) {
        const index = this.teamMember.images.findIndex(x => x.name === image.name);
        this.teamMember.images[index].publish = image.publish == 1 ? 0 : 1;
      } else {
        let uri = `/api/team/member/image/state/${image.id}`;
        this.isLoading = true;
        this.axios.get(uri).then(response => {
          const index = this.teamMember.images.findIndex(x => x.id === image.id);
          this.teamMember.images[index].publish = response.data;
          this.isLoading = false;
        });
      }
    },

    // Save coords
    saveImageCoords(image) {
      if (image.id === null) {
        const index = this.teamMember.images.findIndex(x => x.name === image.name);
        this.teamMember.images[index].coords = image.coords;
      } 
      else {
        let uri = `/api/team/member/image/${image.id}`;
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
        ? "Mitarbeiter bearbeiten" 
        : "Mitarbeiter hinzufügen";
    }
  }
};
</script>
