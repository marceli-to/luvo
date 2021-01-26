<template>
  <nav class="tabs">
    <ul>
      <li v-for="(tab, index) in tabs" :key="index">
        <a
          href="javascript:;"
          @click="change(tab.key)"
          :class="[tab.active ? 'is-active' : '', tab.error ? 'has-error' : '']"
        >{{tab.label}}</a>
      </li>
    </ul>
  </nav>
</template>
<script>
export default {
  props: {
    tabs: Object,
    errors: Object,
  },

  created() {
    this.init();
  },

  methods: {

    init() {
      for (let prop in this.tabs) {
        this.tabs[prop].active = false;
        this.tabs[prop].error = false;
      }
      this.tabs['data'].active = true;
    },

    change(tab) {
      for (let prop in this.tabs) {
        this.tabs[prop].active = false;
        this.tabs[prop].error = false;
      }
      this.tabs[tab].active = true;
    },
  }
}
</script>