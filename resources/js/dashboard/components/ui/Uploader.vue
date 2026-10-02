<template>
  <div>
    <label v-if="label">{{ label }}</label>
    <div ref="el" class="vue-dropzone dropzone"></div>
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
    dictInvalidFileType: `Dateityp nicht erlaubt (erlaubt: ${props.restrictions.split('|')[0].trim()}).`,
    dictFileTooBig: 'Datei ist zu gross ({{filesize}} MB, erlaubt: max. {{maxFilesize}} MB).',
    dictMaxFilesExceeded: 'Zu viele Dateien (max. {{maxFiles}} auf einmal).',
    filesizeBase: 1024,
    // Next to the drop zone rather than in <body>: inside a modal <dialog>
    // everything outside it is inert
    hiddenInputContainer: el.value.parentElement,
  });

  // Rejected in the browser (type, size, count) or by the server
  dropzone.on('error', (file, message, xhr) => {
    notify({ type: 'error', text: `«${file.name}»: ${xhr ? serverError(xhr, message) : message}` });
  });

  dropzone.on('complete', file => {
    if (file.status === 'success') {
      emit('uploaded', JSON.parse(file.xhr.response));
    }
    dropzone.removeFile(file);
  });
});

function serverError(xhr, response) {
  if (xhr.status === 413) {
    return 'Datei ist zu gross für den Server.';
  }
  const message = typeof response === 'object' ? response?.message : null;
  return `Upload fehlgeschlagen (${xhr.status}${message ? ': ' + message : ''}).`;
}

onBeforeUnmount(() => dropzone?.destroy());
</script>
