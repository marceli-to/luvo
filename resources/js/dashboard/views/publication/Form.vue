<template>
  <div>
    <LoadingIndicator v-if="isLoading" />
    <form @submit.prevent="submit" class="half-width" v-if="isFetched">
      <header class="content-header">
        <h1>{{ title }}</h1>
      </header>
      <LanguageTabs v-model="locale" />
      <div>
        <div v-for="lang in ['de', 'fr', 'en']" :key="lang" v-show="locale === lang">
          <div :class="[lang === 'de' && errors.title ? 'has-error' : '', 'form-row']">
            <label>Titel{{ lang === 'de' ? '*' : '' }}</label>
            <input type="text" v-model="record.title[lang]">
            <LabelRequired v-if="lang === 'de'" />
          </div>
          <div class="form-row">
            <label>Beschreibung</label>
            <Editor v-model="record.description[lang]" />
          </div>
          <div :class="[lang === 'de' && errors.articles ? 'has-error' : '', 'form-row']">
            <label>Artikel</label>
            <Editor v-model="record.articles[lang]" />
          </div>
          <div class="form-row is-last" v-if="lang === 'de'">
            <RadioButton label="Publizieren?" name="publish" v-model="record.publish" />
          </div>
        </div>
      </div>
      <footer class="module-footer">
        <div>
          <button type="submit" class="btn-primary">Speichern</button>
          <router-link :to="backRoute(record)" class="btn-secondary">
            <span>Zurück</span>
          </router-link>
        </div>
      </footer>
    </form>
  </div>
</template>
<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import LoadingIndicator from '@/components/ui/LoadingIndicator.vue';
import LanguageTabs from '@/components/ui/LanguageTabs.vue';
import LabelRequired from '@/components/ui/LabelRequired.vue';
import RadioButton from '@/components/ui/RadioButton.vue';
import Editor from '@/components/ui/editor/Editor.vue';
import { useResourceForm, translations } from '@/composables/useResourceForm';

const props = defineProps({
  type: { type: String, required: true },
});

const route = useRoute();

// Publications belong to a team member and lead back to them
const backRoute = publication => ({ name: 'team-member-edit', params: { id: publication.team_member_id } });

const { record, errors, isLoading, isFetched, title, submit } = useResourceForm({
  type: props.type,
  endpoint: 'publication',
  model: () => ({
    title: translations(),
    description: translations(),
    articles: translations(),
    team_member_id: route.params.memberId ?? null,
    publish: 1,
  }),
  redirect: backRoute,
  titles: { create: 'Publikation hinzufügen', edit: 'Publikation bearbeiten' },
});

const locale = ref('de');
</script>
