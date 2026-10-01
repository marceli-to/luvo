<template>
  <dialog ref="dialog" class="editor-dialog" @close="onClose()" @click.self="close()">
    <form method="dialog" @submit.prevent="apply()">
      <header class="editor-dialog__header">
        <h2>Link</h2>
        <a href="javascript:;" class="feather-icon" @click.prevent="close()">
          <XIcon size="18" />
        </a>
      </header>

      <div class="form-row">
        <label>Typ</label>
        <div class="select-wrapper">
          <select v-model="link.type">
            <option v-for="(label, type) in types" :key="type" :value="type">{{ label }}</option>
          </select>
        </div>
      </div>

      <div class="form-row" v-if="link.type === 'url'">
        <label>URL</label>
        <div class="editor-dialog__url">
          <div class="select-wrapper">
            <select v-model="link.protocol">
              <option value="https://">https://</option>
              <option value="http://">http://</option>
            </select>
          </div>
          <input type="text" v-model="link.value" placeholder="www.example.com">
        </div>
      </div>

      <div class="form-row" v-if="link.type === 'email'">
        <label>E-Mail-Adresse</label>
        <input type="text" v-model="link.value" placeholder="info@luksundvogt.ch">
      </div>

      <div class="form-row" v-if="link.type === 'tel'">
        <label>Telefonnummer</label>
        <input type="text" v-model="link.value" placeholder="+41 44 000 00 00">
      </div>

      <div class="form-row" v-if="link.type === 'file'">
        <label>Datei</label>
        <div class="select-wrapper">
          <select v-model="link.value">
            <option :value="''" disabled>Datei wählen</option>
            <option v-for="file in files" :key="file.value" :value="file.value">{{ file.title }}</option>
            <option v-if="isUnlistedFile" :value="link.value">{{ link.value.slice(FILE_PATH.length) }} (nicht in «Dateien»)</option>
          </select>
        </div>
        <p class="editor-dialog__hint" v-if="filesLoaded && !files.length">
          Noch keine Dateien vorhanden. Dateien werden unter «Dateien» hochgeladen.
        </p>
      </div>

      <div class="form-row">
        <label>Titel (optional)</label>
        <input type="text" v-model="link.title">
      </div>

      <div class="form-row" v-if="link.type === 'url' || link.type === 'file'">
        <label class="editor-dialog__checkbox">
          <input type="checkbox" v-model="link.blank">
          <span>In neuem Fenster öffnen</span>
        </label>
      </div>

      <footer class="form-buttons">
        <button type="submit" class="btn-primary">Übernehmen</button>
        <a href="javascript:;" class="btn-secondary" v-if="isEditing" @click.prevent="remove()">Entfernen</a>
        <a href="javascript:;" class="btn-secondary" @click.prevent="close()">Abbrechen</a>
      </footer>
    </form>
  </dialog>
</template>
<script setup>
import { ref, reactive, computed } from 'vue';
import { XIcon } from 'lucide-vue-next';
import http from '@/lib/http';

const props = defineProps({
  editor: { type: Object, required: true },
});

const types = { url: 'URL', email: 'E-Mail', tel: 'Telefon', file: 'Datei' };

// Uploaded files live here; older content links them relatively (../../../storage/...)
const FILE_PATH = '/storage/uploads/files/';

const dialog = ref(null);
const isEditing = ref(false);
const files = ref([]);
const filesLoaded = ref(false);
const link = reactive({ type: 'url', protocol: 'https://', value: '', title: '', blank: false });

// A linked file that was removed from the file list stays selectable
const isUnlistedFile = computed(() =>
  link.type === 'file' && link.value.startsWith(FILE_PATH) && filesLoaded.value && !files.value.some(file => file.value === link.value)
);

async function loadFiles() {
  try {
    const { data } = await http.get('/api/files');
    files.value = data.sort((a, b) => a.title.localeCompare(b.title, 'de'));
  }
  catch {
    // Notified by the http error handler
  }
  filesLoaded.value = true;
}

// Fill the form from an existing link's href
function parse(href) {
  if (href.startsWith('mailto:')) {
    return { type: 'email', value: href.slice(7) };
  }
  if (href.startsWith('tel:')) {
    return { type: 'tel', value: href.slice(4) };
  }
  const file = href.indexOf(FILE_PATH.slice(1));
  if (file !== -1 && !/^https?:\/\/(?!(www\.)?luksundvogt\.ch)/.test(href)) {
    return { type: 'file', value: FILE_PATH + href.slice(file + FILE_PATH.length - 1) };
  }
  if (/^[^\s/:@]+@[^\s/:@]+\.\w+$/.test(href)) {
    return { type: 'email', value: href };
  }
  const match = href.match(/^(https?:\/\/)(.*)$/);
  return { type: 'url', protocol: match?.[1] ?? 'https://', value: match?.[2] ?? href };
}

function open() {
  const attributes = props.editor.getAttributes('link');
  isEditing.value = !!attributes.href;
  Object.assign(link, { type: 'url', protocol: 'https://', value: '', title: '', blank: false });

  if (attributes.href) {
    Object.assign(link, parse(attributes.href), {
      title: attributes.title ?? '',
      blank: attributes.target === '_blank',
    });
  }

  if (!filesLoaded.value) {
    loadFiles();
  }
  dialog.value.showModal();
}

function href() {
  const value = link.value.trim();
  if (!value) {
    return null;
  }
  switch (link.type) {
    case 'email': return `mailto:${value}`;
    case 'tel': return `tel:${value.replace(/\s+/g, '')}`;
    case 'file': return value;
    default: return /^https?:\/\//.test(value) ? value : link.protocol + value;
  }
}

function apply() {
  const url = href();
  if (!url) {
    return;
  }
  const blank = link.blank && (link.type === 'url' || link.type === 'file');
  props.editor.chain().focus().extendMarkRange('link').setLink({
    href: url,
    title: link.title.trim() || null,
    target: blank ? '_blank' : null,
    rel: blank ? 'noopener' : null,
  }).run();
  close();
}

function remove() {
  props.editor.chain().focus().extendMarkRange('link').unsetLink().run();
  close();
}

function close() {
  dialog.value.close();
}

// Also fired when the dialog is torn down with an already destroyed editor
function onClose() {
  if (!props.editor.isDestroyed) {
    props.editor.commands.focus();
  }
}

defineExpose({ open });
</script>
