<template>
  <div>
    <div v-if="$props.hasEdit">
      <a
        href="javascript:;"
        class="feather-icon"
        @click.prevent="showEdit(file)"
      >
        <edit-icon size="18"></edit-icon>
      </a>
    </div>
    <div v-if="$props.hasDestroy">
      <a
        href="javascript:;"
        class="feather-icon"
        @click.prevent="destroy(file,$event)"
      >
        <trash-2-icon size="18"></trash-2-icon>
      </a>
    </div>
  </div>
</template>
<script>
// Icons
import { 
  EditIcon,
  Trash2Icon,
} from 'vue-feather-icons';

export default {

  components: {
    EditIcon,
    Trash2Icon,
  },

  props: {
    file: Object,
    publish: Number,

    hasDestroy: {
      type: Boolean,
      default: true
    },

    hasEdit: {
      type: Boolean,
      default: true
    },
  },

  methods: {

    destroy(file, $event) {
      this.$parent.destroy(file.name,$event);
    },

    showEdit(file) {
      this.$parent.showEdit(file);
    },
  }
}
</script>
