<template>
  <div>
    <LoadingIndicator v-if="isLoading" />
    <form @submit.prevent="submit" class="half-width" v-if="isFetched">
      <header class="content-header">
        <h1>{{ title }}</h1>
      </header>
      <Tabs :tabs="formTabs" v-model="tab" />
      <div v-show="tab === 'data'">
        <LanguageTabs v-model="locale" />
        <div v-show="locale === 'de'">
          <div :class="[errors.title ? 'has-error' : '', 'form-row']">
            <label>Titel*</label>
            <input type="text" v-model="record.title.de">
            <LabelRequired />
          </div>
          <div class="form-row">
            <label>Text</label>
            <TinymceEditor :init="tinyConfig" v-model="record.text.de" />
          </div>
        </div>
        <div v-for="lang in ['fr', 'en']" :key="lang" v-show="locale === lang">
          <div class="form-row">
            <label>Titel</label>
            <input type="text" v-model="record.title[lang]">
          </div>
          <div class="form-row">
            <label>Text</label>
            <TinymceEditor :init="tinyConfig" v-model="record.text[lang]" />
          </div>
        </div>
      </div>
      <div v-show="tab === 'image'">
        <div class="form-row">
          <Uploader v-bind="imageUpload" @uploaded="images.store" />
        </div>
        <div class="form-row">
          <ImageManager
            v-model:images="record.images"
            endpoint="home"
            translated-captions
            preview
            @toggle="images.toggle"
            @destroy="images.destroy"
            @save-coords="images.saveCoords"
          />
        </div>
      </div>
      <div v-show="tab === 'settings'">
        <div class="form-row is-last">
          <RadioButton label="Publizieren?" name="publish" v-model="record.publish" />
        </div>
      </div>
      <footer class="module-footer">
        <div>
          <button type="submit" class="btn-primary">Speichern</button>
          <router-link :to="{ name: 'home' }" class="btn-secondary">
            <span>Zurück</span>
          </router-link>
        </div>
      </footer>
    </form>
  </div>
</template>
<script setup>
import { ref } from 'vue';
import LoadingIndicator from '@/components/ui/LoadingIndicator.vue';
import Tabs from '@/components/ui/Tabs.vue';
import LanguageTabs from '@/components/ui/LanguageTabs.vue';
import LabelRequired from '@/components/ui/LabelRequired.vue';
import RadioButton from '@/components/ui/RadioButton.vue';
import TinymceEditor from '@/components/ui/TinymceEditor.js';
import Uploader from '@/components/ui/Uploader.vue';
import ImageManager from '@/components/images/ImageManager.vue';
import { useResourceForm, formTabs, translations } from '@/composables/useResourceForm';
import { useImages, imageUpload } from '@/composables/useImages';
import { useTinyConfig } from '@/composables/useTinyConfig';

const props = defineProps({
  type: { type: String, required: true },
});

const { record, errors, isEdit, isLoading, isFetched, title, submit } = useResourceForm({
  type: props.type,
  endpoint: 'home',
  model: () => ({
    title: translations(),
    text: translations(),
    images: [],
    publish: 1,
  }),
  redirect: { name: 'home' },
  titles: { create: 'Homepage hinzufügen', edit: 'Homepage bearbeiten' },
});

const images = useImages({
  record, isEdit, isLoading,
  endpoint: 'home',
  foreignKey: 'home_id',
  idKey: 'homeImageId',
  fields: () => ({ caption: translations(), preview: 0 }),
});

const tab = ref('data');
const locale = ref('de');
const tinyConfig = useTinyConfig();
</script>
