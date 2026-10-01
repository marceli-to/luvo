<template>
  <div>
    <LoadingIndicator v-if="isLoading" />
    <form class="half-width" @submit.prevent>
      <header class="content-header">
        <h1>Dateiverwaltung</h1>
      </header>
      <div>
        <div class="form-row">
          <Uploader v-bind="fileUpload" @uploaded="store" />
        </div>
      </div>
      <div class="listing" v-if="items.length">
        <div class="listing__item" v-for="file in items" :key="file.id">
          <div class="listing__item-body">
            <a :href="'/storage/uploads/files/' + file.name" target="_blank"> {{ file.name }}</a> <Separator /> {{ file.size }} <Separator /> {{ file.type }}
          </div>
          <ListActions :record="file" :has-toggle="false" @destroy="destroy" />
        </div>
      </div>
      <div v-else-if="isFetched">
        <p class="no-records">Es sind noch keine Inhalte vorhanden...</p>
      </div>
    </form>
  </div>
</template>
<script setup>
import { notify } from '@kyvg/vue3-notification';
import LoadingIndicator from '@/components/ui/LoadingIndicator.vue';
import ListActions from '@/components/ui/ListActions.vue';
import Separator from '@/components/ui/Separator.vue';
import Uploader from '@/components/ui/Uploader.vue';
import { useListing } from '@/composables/useListing';
import http from '@/lib/http';

const fileUpload = {
  url: '/api/file/upload',
  restrictions: 'pdf | max. 16 MB',
  acceptedFiles: '.pdf',
  maxFiles: 99,
  maxFilesize: 16,
};

const { items, isFetched, isLoading, destroy } = useListing({ list: '/api/files/fetch', resource: 'file' });

async function store(upload) {
  const file = { id: null, name: upload.name, size: upload.size, type: upload.type };
  try {
    const { data } = await http.post('/api/file/store', file);
    file.id = data.id;
    items.value.push(file);
    notify({ type: 'success', text: 'Datei gespeichert!' });
  }
  catch {
    // Notified by the http error handler
  }
}
</script>
