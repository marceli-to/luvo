<template>
  <div>
    <LoadingIndicator v-if="isLoading" />
    <div :class="isFetched ? 'is-loaded' : 'is-loading'">
      <header class="content-header">
        <h1>Kontakt</h1>
        <div v-if="!items.length">
          <router-link :to="{ name: 'contact-create' }" class="feather-icon feather-icon--prepend">
            <PlusIcon size="16" />
            <span>Hinzufügen</span>
          </router-link>
        </div>
      </header>
      <div class="listing" v-if="items.length">
        <div
          v-for="item in items"
          :key="item.id"
          :class="[item.publish == 0 ? 'is-disabled' : '', 'listing__item']"
        >
          <div class="listing__item-body">
            <div class="listing__item-body__html" v-html="item.address.de"></div>
          </div>
          <ListActions :record="item" edit-route="contact-edit" @toggle="toggle" @destroy="destroy" />
        </div>
      </div>
      <div v-else>
        <p class="no-records">Es sind noch keine Inhalte vorhanden...</p>
      </div>
    </div>
  </div>
</template>
<script setup>
import { PlusIcon } from 'lucide-vue-next';
import LoadingIndicator from '@/components/ui/LoadingIndicator.vue';
import ListActions from '@/components/ui/ListActions.vue';
import { useListing } from '@/composables/useListing';

const { items, isFetched, isLoading, toggle, destroy } = useListing({ list: '/api/contact', resource: 'contact' });
</script>
