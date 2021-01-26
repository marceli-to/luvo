<template>
  <div class="listing__item-action">

    <div v-if="hasDownload">
      <a
        :href="routes.download"
        class="feather-icon"
        target="_blank"
      >
        <download-cloud-icon size="18"></download-cloud-icon>
      </a>
    </div>

    <div v-if="hasToggle">
      <a
        href="javascript:;"
        @click.prevent="toggle(id,$event)"
      >
        <span v-if="record.publish" class="feather-icon">
          <eye-icon size="18"></eye-icon>
        </span>
        <span v-else>
          <eye-off-icon size="18" class="feather-icon"></eye-off-icon>
        </span>
      </a>
    </div>

    <div v-if="hasEdit">
      <router-link
        :to="{name: routes.edit, params: { id: id }}"
        class="feather-icon"
      >
        <edit-icon size="18"></edit-icon>
      </router-link>
    </div>

    <div v-if="hasEditOverlay">
      <a
        href="javascript:;"
        class="feather-icon"
        @click.prevent="showOverlay(id,$event)"
      >
        <edit-icon size="18"></edit-icon>
      </a>
    </div>

    <div v-if="hasCopy">
      <a
        href="javascript:;"
        class="feather-icon"
        @click.prevent="copy(id,$event)"
      >
        <copy-icon size="18"></copy-icon>
      </a>
    </div>

    <div v-if="hasDestroy">
      <a
        href="javascript:;"
        class="feather-icon"
        @click.prevent="destroy(id,$event)"
      >
        <trash2-icon size="18"></trash2-icon>
      </a>
    </div>

  </div>
</template>
<script>

// Icons
import { 
  EyeIcon,
  EyeOffIcon,
  EditIcon,
  Trash2Icon,
  CopyIcon,
  DownloadCloudIcon
} from 'vue-feather-icons';

export default {

  components: {
    EyeIcon,
    EyeOffIcon,
    EditIcon,
    Trash2Icon,
    CopyIcon,
    DownloadCloudIcon
  },

  props: {

    id: {
      type: Number,
      default: null
    },

    hasEdit: {
      type: Boolean,
      default: true
    },
    
    hasDestroy: {
      type: Boolean,
      default: true
    },
    
    hasToggle: {
      type: Boolean,
      default: true
    },

    hasDownload: {
      type: Boolean,
      default: false
    },

    hasCopy: {
      type: Boolean,
      default: false
    },

    isDraggable: {
      type: Boolean,
      default: false
    },

    hasEditOverlay: {
      type: Boolean,
      default: false
    },

    routes: Object,
    record: Object,
  },

  methods: {

    toggle(id,$event) {
      let parent = this.$props.isDraggable ? this.$parent.$parent : this.$parent;
      parent.toggle(id,$event);
    },

    destroy(id,$event) {
      let parent = this.$props.isDraggable ? this.$parent.$parent : this.$parent;
      parent.destroy(id,$event);
    },

    copy(id,$event) {
      let parent = this.$props.isDraggable ? this.$parent.$parent : this.$parent;
      parent.copy(id,$event);
    },

    showOverlay(id, $event) {
      this.$parent.showOverlay(id);
    }
  },
}
</script>

