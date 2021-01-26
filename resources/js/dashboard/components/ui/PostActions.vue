<template>
  <div class="post__item-action">
    <div class="icons">
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
  </div>
</template>
<script>

// Icons
import { 
  EyeIcon,
  EyeOffIcon,
  EditIcon,
  Trash2Icon,
} from 'vue-feather-icons';

export default {

  components: {
    EyeIcon,
    EyeOffIcon,
    EditIcon,
    Trash2Icon,
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

    hasSize: {
      type: Boolean,
      default: false
    },

    routes: Object,
    record: Object,
  },

  methods: {

    toggle(id,$event) {
      this.$parent.$parent.toggle(id,$event);
    },

    destroy(id,$event) {
      this.$parent.$parent.destroy(id,$event);
    },

  },
}
</script>

