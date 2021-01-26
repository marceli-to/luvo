<template>
<div>
  <loading-indicator v-if="isLoading"></loading-indicator>
  <div :class="isFetched ? 'is-loaded' : 'is-loading'">
    <header class="content-header">
      <h1>Teams</h1>
      <router-link :to="{ name: 'team-create' }" class="feather-icon feather-icon--prepend">
        <plus-icon size="16"></plus-icon>
        <span>Hinzufügen</span>
      </router-link>
    </header>
    <div class="listing" v-if="team.length">
      <div
        :class="[t.publish == 0 ? 'is-disabled' : '', 'listing__item']"
        v-for="t in team"
        :key="t.id"
      >
        <div class="listing__item-body">
          {{ t.title.de }} 
        </div>
        <list-actions 
          :id="t.id" 
          :record="t"
          :isDraggable="false"
          :routes="{edit: 'team-edit'}">
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
      team: []
    };
  },

  created() {
    this.fetch();
  },

  methods: {

    fetch() {
      this.axios.get(`/api/team`).then(response => {
        this.team = response.data.data;
        this.isFetched = true;
      });
    },

    toggle(id,event) {
      let uri = `/api/team/state/${id}`;
      this.isLoading = true;
      this.axios.get(uri).then(response => {
        const index = this.team.findIndex(x => x.id === id);
        this.team[index].publish = response.data;
        this.$notify({ type: "success", text: "Status geändert" });
        this.isLoading = false;
      });
    },

    destroy(id, event) {
      if (confirm("Bitte löschen bestätigen!")) {
        let uri = `/api/team/${id}`;
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