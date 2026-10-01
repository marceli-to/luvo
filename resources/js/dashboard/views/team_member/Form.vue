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
          <template v-if="lang === 'de'">
            <div :class="[errors.team_id ? 'has-error' : '', 'form-row']">
              <label>Team*</label>
              <div class="select-wrapper is-medium">
                <select v-model="record.team_id" name="layout">
                  <option v-for="team in teams" :key="team.id" :value="team.id">{{ team.capitalizedSlug }}</option>
                </select>
              </div>
            </div>
            <div :class="[errors.firstname ? 'has-error' : '', 'form-row']">
              <label>Vorname*</label>
              <input type="text" v-model="record.firstname">
              <LabelRequired />
            </div>
            <div :class="[errors.name ? 'has-error' : '', 'form-row']">
              <label>Name*</label>
              <input type="text" v-model="record.name">
              <LabelRequired />
            </div>
          </template>
          <div class="form-row">
            <label>Info</label>
            <Editor v-model="record.credits[lang]" />
          </div>
          <div class="form-row">
            <label>Beschreibung</label>
            <Editor v-model="record.description[lang]" />
          </div>
          <div class="form-row">
            <label>SEO Beschreibung</label>
            <textarea v-model="record.meta_description[lang]"></textarea>
          </div>
          <div class="form-row" v-for="field in textFields" :key="field.key">
            <label>{{ field.label }}</label>
            <Editor v-model="record[field.key][lang]" />
          </div>
          <template v-if="lang === 'de'">
            <div class="form-row" v-if="isEdit">
              <header class="content-header" style="margin-bottom: 5px">
                <label>Publikationen (Artikel)</label>
                <router-link :to="{ name: 'publication-create', params: { memberId: record.id } }" class="feather-icon feather-icon--prepend">
                  <PlusIcon size="16" />
                  <span>Hinzufügen</span>
                </router-link>
              </header>
              <div class="listing" v-if="record.publications.length">
                <draggable
                  v-model="record.publications"
                  item-key="id"
                  ghost-class="draggable-ghost"
                  draggable=".listing__item"
                  @end="orderPublications(record.publications)"
                >
                  <template #item="{ element: publication }">
                    <div :class="[publication.publish == 0 ? 'is-disabled' : '', 'listing__item is-draggable']">
                      <div class="listing__item-body">
                        {{ publication.title.de }}
                      </div>
                      <ListActions
                        :record="publication"
                        edit-route="publication-edit"
                        @toggle="togglePublication"
                        @destroy="destroyPublication"
                      />
                    </div>
                  </template>
                </draggable>
              </div>
              <div v-else>
                <p class="no-records">Es sind noch keine Publikationen vorhanden...</p>
              </div>
            </div>
            <div class="form-row" v-else>
              <label>Publikationen</label>
              <p>Publikationen können erst nach dem Speichern hinzugefügt werden.</p>
            </div>
          </template>
        </div>
      </div>
      <div v-show="tab === 'image'">
        <div class="form-row">
          <Uploader v-bind="imageUpload" @uploaded="images.store" />
        </div>
        <div class="form-row">
          <ImageManager
            v-model:images="record.images"
            endpoint="team/member"
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
import draggable from 'vuedraggable';
import { notify } from '@kyvg/vue3-notification';
import { PlusIcon } from 'lucide-vue-next';
import LoadingIndicator from '@/components/ui/LoadingIndicator.vue';
import Tabs from '@/components/ui/Tabs.vue';
import LanguageTabs from '@/components/ui/LanguageTabs.vue';
import LabelRequired from '@/components/ui/LabelRequired.vue';
import RadioButton from '@/components/ui/RadioButton.vue';
import ListActions from '@/components/ui/ListActions.vue';
import Editor from '@/components/ui/editor/Editor.vue';
import Uploader from '@/components/ui/Uploader.vue';
import ImageManager from '@/components/images/ImageManager.vue';
import { useResourceForm, formTabs, translations } from '@/composables/useResourceForm';
import { useImages, imageUpload } from '@/composables/useImages';
import { useOrder } from '@/composables/useOrder';
import http from '@/lib/http';
import { confirmDelete } from '@/lib/utils';

const props = defineProps({
  type: { type: String, required: true },
});

const textFields = [
  { key: 'area', label: 'Tätigkeitsgebiete' },
  { key: 'languages', label: 'Sprachen' },
  { key: 'biography', label: 'Werdegang' },
  { key: 'membership', label: 'Mitgliedschaften' },
  { key: 'publication', label: 'Publikationen (Liste)' },
];

const teams = ref([]);

const { record, errors, isEdit, isLoading, isFetched, title, submit } = useResourceForm({
  type: props.type,
  endpoint: 'team/member',
  model: () => ({
    firstname: null,
    name: null,
    credits: translations(),
    description: translations(),
    meta_description: translations(),
    area: translations(),
    languages: translations(),
    biography: translations(),
    membership: translations(),
    publication: translations(),
    team_id: 1,
    images: [],
    publications: [],
    publish: 1,
  }),
  redirect: { name: 'teams' },
  titles: { create: 'Mitarbeiter hinzufügen', edit: 'Mitarbeiter bearbeiten' },
  load: [() => http.get('/api/team').then(response => teams.value = response.data.data)],
});

const images = useImages({
  record, isEdit, isLoading,
  endpoint: 'team/member',
  foreignKey: 'team_member_id',
  idKey: 'teamMemberImageId',
  fields: () => ({ caption: null, device: 'desktop' }),
});

// Publications (edit only)
const orderPublications = useOrder({ url: '/api/publication/order', key: 'publications' });

async function togglePublication(id) {
  isLoading.value = true;
  try {
    const { data } = await http.get(`/api/publication/state/${id}`);
    record.value.publications.find(p => p.id === id).publish = data;
    notify({ type: 'success', text: 'Status geändert' });
  }
  catch {
    // Notified by the http error handler
  }
  finally {
    isLoading.value = false;
  }
}

async function destroyPublication(id) {
  if (!confirmDelete()) {
    return;
  }
  isLoading.value = true;
  try {
    await http.delete(`/api/publication/${id}`);
    const publications = record.value.publications;
    publications.splice(publications.findIndex(p => p.id === id), 1);
  }
  catch {
    // Notified by the http error handler
  }
  finally {
    isLoading.value = false;
  }
}

const tab = ref('data');
const locale = ref('de');
</script>
