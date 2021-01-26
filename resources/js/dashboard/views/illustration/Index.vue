<template>
<div>
  <loading-indicator v-if="isLoading"></loading-indicator>
  <div :class="isFetched ? 'is-loaded' : 'is-loading'">
    <header class="content-header">
      <h1>Illustrationen</h1>
      <router-link :to="{ name: 'illustration-create' }" class="feather-icon feather-icon--prepend">
        <plus-icon size="16"></plus-icon>
        <span>Hinzufügen</span>
      </router-link>
    </header>
    <div class="listing" v-if="illustrations.length">
      <draggable 
        :disabled="false"
        v-model="illustrations" 
        @end="order()"
        ghost-class="draggable-ghost"
        draggable=".listing__item">
        <div
          :class="[i.publish == 0 ? 'is-disabled' : '', 'listing__item is-draggable']"
          v-for="i in illustrations"
          :key="i.id"
        >
          <div class="listing__item-body">
            {{i.title.de }} <separator v-if="i.subtitle.de" />{{ i.subtitle.de }}
            <span v-if="!i.preview_image">
              <span class="bubble-warning">Vorschau fehlt</span>
            </span>
          </div>
          <list-actions 
            :id="i.id" 
            :record="i"
            :isDraggable="true"
            :routes="{edit: 'illustration-edit'}">
          </list-actions>
        </div>
      </draggable>
    </div>
    <div v-else>
      <p class="no-records">Es sind noch keine Illustrationen vorhanden...</p>
    </div>
  </div>
</div>
</template>
<script>

// Icons
import { PlusIcon } from 'vue-feather-icons';

// Components
import ListActions from "@/components/ui/ListActions.vue";
import draggable from "vuedraggable";

// Mixins
import ErrorHandling from "@/mixins/ErrorHandling";
import Helpers from "@/mixins/Helpers";

export default {

  components: {
    ListActions,
    PlusIcon,
    draggable
  },

  mixins: [ErrorHandling, Helpers],

  data() {
    return {
      isLoading: false,
      isFetched: false,
      illustrations: []
    };
  },

  created() {
    this.fetch();
  },

  methods: {

    fetch() {
      this.axios.get(`/api/illustrations`).then(response => {
        this.illustrations = response.data.data;
        this.isFetched = true;
      });
    },

    toggle(id,event) {
      let uri = `/api/illustration/state/${id}`;
      this.isLoading = true;
      this.axios.get(uri).then(response => {
        const index = this.illustrations.findIndex(x => x.id === id);
        this.illustrations[index].publish = response.data;
        this.$notify({ type: "success", text: "Status geändert" });
        this.isLoading = false;
      });
    },

    destroy(id, event) {
      if (confirm("Bitte löschen bestätigen!")) {
        let uri = `/api/illustration/${id}`;
        this.isLoading = true;
        this.axios.delete(uri).then(response => {
          this.fetch();
          this.isLoading = false;
        });
      }
    },

    order() {
      let illustrations = this.illustrations.map(function(illustration, index) {
        illustration.order = index;
        return illustration;
      });
      if (this.debounce) return;
      this.debounce = setTimeout(function() {
        this.debounce = false 
        this.axios.post(`/api/illustration/order`, {illustrations: illustrations}).then((response) => {
          this.$notify({type: 'success', text: 'Reihenfolge angepasst'});
        });
      }.bind(this, illustrations), 500);
    },
  }
}
</script>