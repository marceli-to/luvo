<template>
<div>
  <loading-indicator v-if="isLoading"></loading-indicator>
  <div :class="isFetched ? 'is-loaded' : 'is-loading'">
    <header class="content-header">
      <h1>News</h1>
      <router-link :to="{ name: 'news-create' }" class="feather-icon feather-icon--prepend">
        <plus-icon size="16"></plus-icon>
        <span>Hinzufügen</span>
      </router-link>
    </header>
    <div class="listing" v-if="news.length">
      <draggable 
        :disabled="false"
        v-model="news" 
        @end="order()"
        ghost-class="draggable-ghost"
        draggable=".listing__item">
        <div
          :class="[n.publish == 0 ? 'is-disabled' : '', 'listing__item is-draggable']"
          v-for="n in news"
          :key="n.id"
        >
          <div class="listing__item-body">
            {{ n.title.de }} 
            <separator v-if="n.subtitle.de" /> 
            {{ n.subtitle.de }} 

          </div>
          <list-actions 
            :id="n.id" 
            :record="n"
            :isDraggable="true"
            :routes="{edit: 'news-edit'}">
          </list-actions>
        </div>
      </draggable>
    </div>
    <div v-else>
      <p class="no-records">Es sind noch keine Artikel vorhanden...</p>
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
      news: []
    };
  },

  created() {
    this.fetch();
  },

  methods: {

    fetch() {
      this.axios.get(`/api/news`).then(response => {
        this.news = response.data.data;
        this.isFetched = true;
      });
    },

    toggle(id,event) {
      let uri = `/api/news/state/${id}`;
      this.isLoading = true;
      this.axios.get(uri).then(response => {
        const index = this.news.findIndex(x => x.id === id);
        this.news[index].publish = response.data;
        this.$notify({ type: "success", text: "Status geändert" });
        this.isLoading = false;
      });
    },

    destroy(id, event) {
      if (confirm("Bitte löschen bestätigen!")) {
        let uri = `/api/news/${id}`;
        this.isLoading = true;
        this.axios.delete(uri).then(response => {
          this.fetch();
          this.isLoading = false;
        });
      }
    },

    order() {
      let news = this.news.map(function(n, index) {
          n.order = index;
          return n;
      });
      if (this.debounce) return;
      this.debounce = setTimeout(function() {
        this.debounce = false 
        this.axios.post(`/api/news/order`, {news: news}).then((response) => {
          this.$notify({type: 'success', text: 'Reihenfolge angepasst'});
        });
      }.bind(this, news), 500);
    },

  }
}
</script>