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
        <div v-for="lang in ['de', 'fr', 'en']" :key="lang" v-show="locale === lang">
          <div :class="[lang === 'de' && errors.address ? 'has-error' : '', 'form-row']">
            <label>{{ lang === 'de' ? 'Adresse' : 'Text' }}</label>
            <Editor v-model="record.address[lang]" />
          </div>
          <div class="form-row">
            <label>Impressum</label>
            <Editor v-model="record.imprint[lang]" />
          </div>
          <div class="form-row">
            <label>Datenschutz</label>
            <Editor v-model="record.privacy[lang]" />
          </div>
          <div class="form-row" v-if="lang === 'de'">
            <label>Google Maps Uri</label>
            <input type="text" v-model="record.map_uri">
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
            endpoint="contact"
            devices
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
          <router-link :to="{ name: 'contact' }" class="btn-secondary">
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
import RadioButton from '@/components/ui/RadioButton.vue';
import Editor from '@/components/ui/editor/Editor.vue';
import Uploader from '@/components/ui/Uploader.vue';
import ImageManager from '@/components/images/ImageManager.vue';
import { useResourceForm, formTabs, translations } from '@/composables/useResourceForm';
import { useImages, imageUpload } from '@/composables/useImages';

const props = defineProps({
  type: { type: String, required: true },
});

const { record, errors, isEdit, isLoading, isFetched, title, submit } = useResourceForm({
  type: props.type,
  endpoint: 'contact',
  model: () => ({
    address: translations(),
    imprint: translations(),
    privacy: translations(),
    images: [],
    map_uri: null,
    publish: 1,
  }),
  redirect: { name: 'contact' },
  titles: { create: 'Kontakt hinzufügen', edit: 'Kontakt bearbeiten' },
});

const images = useImages({
  record, isEdit, isLoading,
  endpoint: 'contact',
  foreignKey: 'contact_id',
  idKey: 'contactImageId',
  fields: () => ({ caption: null, device: 'desktop' }),
});

const tab = ref('data');
const locale = ref('de');
</script>
