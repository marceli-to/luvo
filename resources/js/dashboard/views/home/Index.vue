<template>
<div>
  <loading-indicator v-if="isLoading"></loading-indicator>
  <div :class="isFetched ? 'is-loaded' : 'is-loading'">
    <header class="content-header">
      <h1>Homepage</h1>
      <div v-if="!home.length">
        <router-link :to="{ name: 'home-create' }" class="feather-icon feather-icon--prepend">
          <plus-icon size="16"></plus-icon>
          <span>Hinzufügen</span>
        </router-link>
      </div>
    </header>
    <div class="listing" v-if="home.length">
      <div
        :class="[h.publish == 0 ? 'is-disabled' : '', 'listing__item']"
        v-for="h in home"
        :key="h.id"
      >
        <div class="listing__item-body">
          {{ h.title.de }} 
        </div>
        <list-actions 
          :id="h.id" 
          :record="h"
          :isDraggable="false"
          :routes="{edit: 'home-edit'}">
        </list-actions>
      </div>
    </div>
    <div v-else>
      <p class="no-records">Es sind noch keine Inhalte vorhanden...</p>
    </div>
  </div>
</div>
</template>
<script>

// Icons
import { PlusIcon } from 'vue-feather-icons';

// Components
import ListActions from "@/components/ui/ListActions.vue";

// Mixins
import ErrorHandling from "@/mixins/ErrorHandling";
import Helpers from "@/mixins/Helpers";

export default {

  components: {
    ListActions,
    PlusIcon,
  },

  mixins: [ErrorHandling, Helpers],

  data() {
    return {
      isLoading: false,
      isFetched: false,
      home: []
    };
  },

  created() {
    this.fetch();
  },

  methods: {

    fetch() {
      this.isLoading = true;
      this.axios.get(`/api/home`).then(response => {
        this.home = response.data.data;
        this.isFetched = true;
        this.isLoading = false;
      });
    },

    toggle(id,event) {
      let uri = `/api/home/state/${id}`;
      this.isLoading = true;
      this.axios.get(uri).then(response => {
        const index = this.home.findIndex(x => x.id === id);
        this.home[index].publish = response.data;
        this.$notify({ type: "success", text: "Status geändert" });
        this.isLoading = false;
      });
    },

    destroy(id, event) {
      if (confirm("Bitte löschen bestätigen!")) {
        let uri = `/api/home/${id}`;
        this.isLoading = true;
        this.axios.delete(uri).then(response => {
          this.fetch();
          this.isLoading = false;
        });
      }
    },
  }
}
</script>