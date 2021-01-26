<template>
  <div>
    <div class="upload-listing is-files">
      <div>
        <div
          class="upload-item-file"
          v-for="file in files"
          :key="file.id"
        >
          <input type="text" v-model="file.caption" :placeholder="file.name" />
          <div class="upload__actions">
            <file-actions :file="file" :publish="file.publish" :hasEdit="false"></file-actions>
          </div>
        </div>
      </div>
    </div>
    <div :class="[hasOverlayEdit ? 'is-visible' : '', 'upload-overlay-edit']">
      <div>
        <a
          href="javascript:;"
          class="icon-close-overlay"
          @click.prevent="hideEdit()"
        ></a>
        <div>
          <figure v-if="hasOverlayEdit">
            <img src="/assets/backend/img/icons/file.svg" height="100" width="100">
            <figcaption v-if="overlayItem.caption">
              <span v-if="overlayItem.caption">{{overlayItem.caption}}</span>
            </figcaption>
          </figure>
        </div>
        <div>
          <div class="form-row">
            <label>Datei:</label>
            <span>{{overlayItem.name}}</span>
          </div>
          <div class="form-row">
            <input v-model="overlayItem.caption" />
          </div>
          <div class="form-row-button" v-if="updateOnChange">
            <a
              href="javascript:;"
              class="btn-secondary"
              @click.prevent="update(overlayItem, $event)"
            >Speichern</a>
          </div>
          <div class="form-row-button" v-else>
            <a
              href="javascript:;"
              class="btn-secondary"
              @click.prevent="hideEdit()"
            >Schliessen</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>

// Actions
import FileActions from "@/global/components/files/Actions.vue";

// Mixins
import FileEdit from "@/global/components/files/mixins/edit";
import FileUtils from "@/global/components/files/mixins/utils";

export default {
  components: {
    FileActions,
  },

  props: {
    files: Array,

    updateOnChange: {
      type: Boolean,
      default: false
    },

    categories: {
      type: Object,
      default: null,
    }
  },

  mixins: [FileEdit, FileUtils],

  data() {
    return {
      hasOverlayEdit: false,

      overlayItem: {
        name: '',
        caption: null
      },

      defaults: {
        item: {
          name: '',
          caption: null
        }
      }
    };
  },

  mounted() {
    window.addEventListener("keyup", event => {
      if (event.which === 27) {
        this.hideEdit();
      }
    });
  },

  methods: {
    toggle(file, $event) {
      this.$parent.toggleFile(file, $event)
    },

    destroy(file, $event) {
      this.$parent.destroy(file, $event)
    },

    update(file, $event) {
      this.$parent.updateFile(file, $event)
      this.hideEdit();
    },

    showEdit(file) {
      this.hasOverlayEdit = true;
      this.overlayItem = file;
    },

    hideEdit() {
      this.hasOverlayEdit = false;
      this.overlayItem = this.defaults.item;
    },
  }
};
</script>