<template>
<div>
  <loading-indicator v-if="isLoading"></loading-indicator>
    <div v-if="hasOverlay">
      <edit-form :order="order"></edit-form>
    </div>
  <div :class="isFetched ? 'is-loaded' : 'is-loading'">
    <header class="content-header">
      <h1>Bestellungen</h1>
    </header>
    <div class="listing" v-if="orders.length">
      <div
        :class="[o.publish == 0 ? 'is-disabled' : '', 'listing__item']"
        v-for="o in orders"
        :key="o.id"
      >
        <div :class="o.deleted_at ? 'listing__item-body is-deleted' : 'listing__item-body'">
          {{ o.number }} 
          <separator /> 
          {{ o.firstname }} {{ o.name }} 
          <separator /> 
          {{ o.zip }} {{ o.city }}
          <separator /> 
          {{moneyFormat(o.grand_total)}}
          <separator />

          <span v-if="o.deleted_at">
            <span class="bubble-info">gelöscht</span>
          </span>
          <span v-if="o.state == 0">
            <span class="bubble-warning">{{states[o.state]}}</span>
          </span>
          <span v-if="o.state == 1">
            <span class="bubble-info">{{states[o.state]}}</span>
          </span>
          <span v-if="o.state == 2">
            <span class="bubble-info">{{states[o.state]}}</span>
          </span>
          <span v-if="o.state == 3">
            <span class="bubble-ok">{{states[o.state]}}</span>
          </span>
        </div>
        <list-actions 
          :id="o.id" 
          :record="o"
          :hasDownload="true"
          :hasToggle="false"
          :hasEdit="false"
          :hasEditOverlay="true"
          :routes="{edit: 'order-edit', download: '/order/download-bill/' + o.id}">
        </list-actions>
      </div>
    </div>
    <div v-else>
      <p class="no-records">Es sind noch keine Bestellungen vorhandeo...</p>
    </div>
  </div>
</div>
</template>
<script>

// Icons
import { PlusIcon } from 'vue-feather-icons';

// Components
import EditForm from '@/views/order/edit.vue';
import ListActions from "@/components/ui/ListActions.vue";

// Mixins
import ErrorHandling from "@/mixins/ErrorHandling";
import Helpers from "@/mixins/Helpers";

export default {

  components: {
    EditForm,
    ListActions,
    PlusIcon,
  },

  mixins: [ErrorHandling, Helpers],

  data() {
    return {
      isLoading: false,
      isFetched: false,
      hasOverlay: false,
      order: null,
      orders: [],

      states: {
         0: 'offen',
         1: 'bezahlt',
         2: 'geliefert',
         3: 'bezahlt & geliefert',
      }
    };
  },

  created() {
    this.fetch();
  },

  methods: {

    fetch() {
      this.axios.get(`/api/orders`).then(response => {
        this.orders = response.data.data;
        this.isFetched = true;
      });
    },

    toggle(id,event) {
      let uri = `/api/order/state/${id}`;
      this.isLoading = true;
      this.axios.get(uri).then(response => {
        const index = this.order.findIndex(x => x.id === id);
        this.order[index].publish = response.data;
        this.$notify({ type: "success", text: "Status geändert" });
        this.isLoading = false;
      });
    },

    destroy(id, event) {
      if (confirm("Bitte löschen bestätigen!")) {
        let uri = `/api/order/${id}`;
        this.isLoading = true;
        this.axios.delete(uri).then(response => {
          this.fetch();
          this.isLoading = false;
        });
      }
    },

    changeState(id, state) {
      let uri = `/api/order/${id}`;
      this.isLoading = true;
      this.axios.put(uri, {state: state}).then(response => {
        this.$notify({ type: "success", text: "Änderungen gespeichert!" });
        this.hideOverlay();
        this.fetch();
        this.isLoading = false;
      });
    },

    showOverlay(id) {
      let uri = `/api/order/${id}`;
      this.axios.get(uri).then(response => {
        this.order = response.data;
        this.hasOverlay = true;
      });
    },

    hideOverlay() {
      this.order = null;
      this.hasOverlay = false;
    }
  }
}
</script>