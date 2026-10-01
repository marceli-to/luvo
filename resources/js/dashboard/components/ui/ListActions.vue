<template>
  <div class="listing__item-action">
    <div v-if="hasToggle">
      <a href="javascript:;" @click.prevent="emit('toggle', record.id)">
        <span v-if="record.publish" class="feather-icon">
          <EyeIcon size="18" />
        </span>
        <span v-else>
          <EyeOffIcon size="18" class="feather-icon" />
        </span>
      </a>
    </div>
    <div v-if="editRoute">
      <router-link :to="{ name: editRoute, params: { id: record.id } }" class="feather-icon">
        <EditIcon size="18" />
      </router-link>
    </div>
    <div v-if="hasDestroy">
      <a href="javascript:;" class="feather-icon" @click.prevent="emit('destroy', record.id)">
        <Trash2Icon size="18" />
      </a>
    </div>
  </div>
</template>
<script setup>
import { EyeIcon, EyeOffIcon, EditIcon, Trash2Icon } from 'lucide-vue-next';

defineProps({
  record: { type: Object, required: true },
  editRoute: { type: String, default: null },
  hasToggle: { type: Boolean, default: true },
  hasDestroy: { type: Boolean, default: true },
});

const emit = defineEmits(['toggle', 'destroy']);
</script>
