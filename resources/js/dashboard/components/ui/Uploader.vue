<template>
  <div>
    <label v-if="label">{{ label }}</label>
    <div ref="el" id="dropzone" class="vue-dropzone dropzone"></div>
    <span class="bubble is-restriction">{{ restrictions }}</span>
  </div>
</template>
<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import Dropzone from 'dropzone';
import { notify } from '@kyvg/vue3-notification';

Dropzone.autoDiscover = false;

const props = defineProps({
  url: { type: String, required: true },
  label: { type: String, default: null },
  restrictions: { type: String, default: '' },
  acceptedFiles: { type: String, required: true },
  maxFiles: { type: Number, default: 99 },
  maxFilesize: { type: Number, required: true },
});

// The upload endpoint's JSON response (name, plus type/size or orientation)
const emit = defineEmits(['uploaded']);

const el = ref(null);
let dropzone = null;

onMounted(() => {
  dropzone = new Dropzone(el.value, {
    url: props.url,
    method: 'post',
    acceptedFiles: props.acceptedFiles,
    maxFiles: props.maxFiles,
    maxFilesize: props.maxFilesize,
    createImageThumbnails: false,
    thumbnailWidth: 200,
    thumbnailHeight: 200,
  });

  dropzone.on('complete', file => {
    if (file.status === 'error' && !file.accepted) {
      notify({ type: 'error', text: 'Invalid format or file to big!' });
    }
    else if (file.status === 'success') {
      emit('uploaded', JSON.parse(file.xhr.response));
    }
    else {
      notify({ type: 'error', text: `Upload fehlgeschlagen (${file.xhr?.status ?? '-'})` });
    }
    dropzone.removeFile(file);
  });
});

onBeforeUnmount(() => dropzone?.destroy());
</script>
