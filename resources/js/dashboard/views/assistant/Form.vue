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
          <div v-if="lang === 'de'" :class="[errors.team_id ? 'has-error' : '', 'form-row']">
            <label>Team*</label>
            <div class="select-wrapper is-medium">
              <select v-model="record.team_id" name="layout">
                <option v-for="team in teams" :key="team.id" :value="team.id">{{ team.capitalizedSlug }}</option>
              </select>
            </div>
          </div>
          <div :class="[lang === 'de' && errors.description ? 'has-error' : '', 'form-row']">
            <label>Beschreibung</label>
            <Editor v-model="record.description[lang]" />
          </div>
          <div class="form-row">
            <label>Assistenten</label>
            <Editor v-model="record.assistants[lang]" />
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
            endpoint="assistant"
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
          <router-link :to="{ name: 'teams' }" class="btn-secondary">
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
import http from '@/lib/http';

const props = defineProps({
  type: { type: String, required: true },
});

const teams = ref([]);

const { record, errors, isEdit, isLoading, isFetched, title, submit } = useResourceForm({
  type: props.type,
  endpoint: 'assistant',
  model: () => ({
    description: translations(),
    assistants: translations(),
    team_id: 1,
    images: [],
    publish: 1,
  }),
  redirect: { name: 'teams' },
  titles: { create: 'Assistenten hinzufügen', edit: 'Assistenten bearbeiten' },
  load: [() => http.get('/api/team').then(response => teams.value = response.data.data)],
});

const images = useImages({
  record, isEdit, isLoading,
  endpoint: 'assistant',
  foreignKey: 'assistant_id',
  idKey: 'assistantImageId',
  fields: () => ({ caption: null, device: 'desktop' }),
});

const tab = ref('data');
const locale = ref('de');
</script>
