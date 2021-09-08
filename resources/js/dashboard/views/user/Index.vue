<template>
<div>
  <loading-indicator v-if="isLoading"></loading-indicator>
  <div :class="isFetched ? 'is-loaded' : 'is-loading'">
    <header class="content-header">
      <h1>Benutzer</h1>
    </header>
    <div class="listing" v-if="user">
      <div class="listing__item">
        <div class="listing__item-body">
          {{ user.firstname }} {{ user.name }} 
        </div>
        <list-actions 
          :id="user.id" 
          :record="user"
          :isDraggable="false"
          :hasDestroy="false"
          :hasToggle="false"
          :routes="{edit: 'user-edit'}">
        </list-actions>
      </div>
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
      user: {}
    };
  },

  created() {
    this.fetch();
  },

  methods: {

    fetch() {
      this.isLoading = true;
      this.axios.get(`/api/user`).then(response => {
        this.user = response.data;
        this.isFetched = true;
        this.isLoading = false;
      });
    },

    // toggle(id,event) {
    //   let uri = `/api/home/state/${id}`;
    //   this.isLoading = true;
    //   this.axios.get(uri).then(response => {
    //     const index = this.home.findIndex(x => x.id === id);
    //     this.home[index].publish = response.data;
    //     this.$notify({ type: "success", text: "Status geändert" });
    //     this.isLoading = false;
    //   });
    // },

  }
}
</script>