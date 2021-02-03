<template>
<div>
  <loading-indicator v-if="isLoading"></loading-indicator>
  <div :class="isFetched ? 'is-loaded' : 'is-loading'">
    <header class="content-header">
      <h1>Kontakt</h1>
      <div v-if="!contact.length">
        <router-link :to="{ name: 'contact-create' }" class="feather-icon feather-icon--prepend">
          <plus-icon size="16"></plus-icon>
          <span>Hinzufügen</span>
        </router-link>
      </div>
    </header>
    <div class="listing" v-if="contact.length">
      <div
        :class="[c.publish == 0 ? 'is-disabled' : '', 'listing__item']"
        v-for="c in contact"
        :key="c.id"
      >
        <div class="listing__item-body">
          <div class="listing__item-body__html" v-html="c.address.de"></div> 
        </div>
        <list-actions 
          :id="c.id" 
          :record="c"
          :isDraggable="false"
          :routes="{edit: 'contact-edit'}">
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
      contact: []
    };
  },

  created() {
    this.fetch();
  },

  methods: {

    fetch() {
      this.axios.get(`/api/contact`).then(response => {
        this.contact = response.data.data;
        this.isFetched = true;
      });
    },

    toggle(id,event) {
      let uri = `/api/contact/state/${id}`;
      this.isLoading = true;
      this.axios.get(uri).then(response => {
        const index = this.contact.findIndex(x => x.id === id);
        this.contact[index].publish = response.data;
        this.$notify({ type: "success", text: "Status geändert" });
        this.isLoading = false;
      });
    },

    destroy(id, event) {
      if (confirm("Bitte löschen bestätigen!")) {
        let uri = `/api/contact/${id}`;
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