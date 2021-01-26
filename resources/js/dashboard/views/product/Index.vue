<template>
<div>
  <loading-indicator v-if="isLoading"></loading-indicator>
  <div :class="isFetched ? 'is-loaded' : 'is-loading'">
    <header class="content-header">
      <h1>Shop Artikel</h1>
      <router-link :to="{ name: 'product-create' }" class="feather-icon feather-icon--prepend">
        <plus-icon size="16"></plus-icon>
        <span>Hinzufügen</span>
      </router-link>
    </header>
    <div class="listing" v-if="products.length">
      <draggable 
        :disabled="false"
        v-model="products" 
        @end="order()"
        ghost-class="draggable-ghost"
        draggable=".listing__item">
        <div
          :class="[p.publish == 0 ? 'is-disabled' : '', 'listing__item is-draggable']"
          v-for="p in products"
          :key="p.id"
        >
          <div class="listing__item-body">
            {{ p.title.de }} 
            <separator v-if="p.subtitle.de" /> 
            {{ p.subtitle.de }} 
            <separator /> 
            {{ moneyFormat(p.price) }}
            
            <span v-if="p.stock == 0">
              <span class="bubble-danger">Nicht Verfügbar</span>
            </span>
            <span v-else-if="p.stock < 5">
              <span class="bubble-warning"><strong>{{ p.stock }}</strong> Verfügbar</span>
            </span>
            <span v-else>
              <span class="bubble-info"><strong>{{ p.stock }}</strong> Verfügbar</span>
            </span>
            <span v-if="!p.preview_image">
              <span class="bubble-warning">Vorschau fehlt</span>
            </span>
          </div>
          <list-actions 
            :id="p.id" 
            :record="p"
            :hasCopy="true"
            :isDraggable="true"
            :routes="{edit: 'product-edit'}">
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
      products: []
    };
  },

  created() {
    this.fetch();
  },

  methods: {

    fetch() {
      this.axios.get(`/api/products`).then(response => {
        this.products = response.data.data;
        this.isFetched = true;
      });
    },

    toggle(id,event) {
      let uri = `/api/product/state/${id}`;
      this.isLoading = true;
      this.axios.get(uri).then(response => {
        const index = this.products.findIndex(x => x.id === id);
        this.products[index].publish = response.data;
        this.$notify({ type: "success", text: "Status geändert" });
        this.isLoading = false;
      });
    },

    copy(id, event) {
      let uri = `/api/product/copy/${id}`;
      this.isLoading = true;
      this.axios.get(uri).then(response => {
        this.fetch();
        this.isLoading = false;
      });
    },

    destroy(id, event) {
      if (confirm("Bitte löschen bestätigen!")) {
        let uri = `/api/product/${id}`;
        this.isLoading = true;
        this.axios.delete(uri).then(response => {
          this.fetch();
          this.isLoading = false;
        });
      }
    },

    order() {
      let products = this.products.map(function(product, index) {
          product.order = index;
          return product;
      });
      if (this.debounce) return;
      this.debounce = setTimeout(function() {
        this.debounce = false 
        this.axios.post(`/api/product/order`, {products: products}).then((response) => {
          this.$notify({type: 'success', text: 'Reihenfolge angepasst'});
        });
      }.bind(this, products), 500);
    },

  }
}
</script>