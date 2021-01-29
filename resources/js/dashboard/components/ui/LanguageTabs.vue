<template>
  <nav class="language">
    <div>
    <label>Übersetzung:</label>
    <ul>
      <li v-for="(language, index) in languages" :key="index">
        <a
          href="javascript:;"
          @click="change(language.key)"
          :class="[language.active ? 'is-active' : '']"
        >{{language.label}}</a>
      </li>
    </ul>
    </div>
  </nav>
</template>
<script>
export default {
  props: {
    languages: Object,
  },

  created() {
    this.init();
  },

  methods: {

    init() {
      for (let prop in this.languages) {
        this.languages[prop].active = false;
      }
      this.languages['de'].active = true;
    },

    change(tab) {
      for (let prop in this.languages) {
        this.languages[prop].active = false;
      }
      this.languages[tab].active = true;
    },
  }
}
</script>