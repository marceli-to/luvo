<template>
  <div class="listing__item-action">
    <div v-if="hasToggle">
      <a href="javascript:;" @click.prevent="emit('toggle', record.id)">
        <span v-if="record.publish" class="feather-icon">
          <PhEye :size="18" weight="light" />
        </span>
        <span v-else>
          <PhEyeSlash :size="18" weight="light" class="feather-icon" />
        </span>
      </a>
    </div>
    <div v-if="editRoute">
      <router-link :to="{ name: editRoute, params: { id: record.id } }" class="feather-icon">
        <PhPencil :size="18" weight="light" />
      </router-link>
    </div>
    <div v-if="hasDestroy">
      <a href="javascript:;" class="feather-icon" @click.prevent="emit('destroy', record.id)">
        <PhTrash :size="18" weight="light" />
      </a>
    </div>
  </div>
</template>
<script setup>
import { PhEye, PhEyeSlash, PhPencil, PhTrash } from '@phosphor-icons/vue';

defineProps({
  record: { type: Object, required: true },
  editRoute: { type: String, default: null },
  hasToggle: { type: Boolean, default: true },
  hasDestroy: { type: Boolean, default: true },
});

const emit = defineEmits(['toggle', 'destroy']);
</script>
