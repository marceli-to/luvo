<template>
  <div :class="isFetched ? 'is-loaded' : 'is-loading'">
    <header class="content-header">
      <h1>Willkommen <strong>{{user.firstname}} {{user.name}}</strong></h1>
    </header>
    <div class="content">
    </div>
  </div>
</template>
<script>

// Mixins
import Helpers from "@/mixins/Helpers";

export default {

  mixins: [Helpers],

  data() {
    return {
      isFetched: false,
      user: {},
    };
  },

  created() {
    this.fetch();
  },

  methods: {
    fetch() {
      this.axios.get(`/api/user`).then(response => {
        this.user = response.data;
        this.isFetched = true;
      });
    }
  }
}
</script>