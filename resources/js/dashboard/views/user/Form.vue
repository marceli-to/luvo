<template>
  <div>
    <loading-indicator v-if="isLoading"></loading-indicator>
    <form @submit.prevent="submit" class="half-width" v-if="isFetched">
      <header class="content-header">
        <h1>{{title}}</h1>
      </header>
      <div>
        <div :class="[this.errors.password ? 'has-error' : '', 'form-row']">
          <label>Neues Passwort (min. 6 Zeichen)</label>
          <input type="password" v-model="user.password">
        </div>
        <div :class="[this.errors.password_confirm ? 'has-error' : '', 'form-row']">
          <label>Neues Passwort wiederholen</label>
          <input type="password" v-model="user.password_confirm">
        </div>
      </div>
      <footer class="module-footer">
        <div>
          <button type="submit" class="btn-primary">Speichern</button>
          <router-link :to="{ name: 'home' }" class="btn-secondary">
            <span>Zurück</span>
          </router-link>
        </div>
      </footer>
    </form>
  </div>
</template>
<script>

// Icons
import { ArrowLeftIcon } from 'vue-feather-icons';

// Mixins
import ErrorHandling from "@/mixins/ErrorHandling";

// Components
import RadioButton from "@/components/ui/RadioButton.vue";
import LabelRequired from "@/components/ui/LabelRequired.vue";

// Tabs config

export default {
  components: {
    ArrowLeftIcon,
    RadioButton,
    LabelRequired,
  },

  mixins: [ErrorHandling],

  props: {
    type: String
  },

  data() {
    return {
      
      // Model
      user: {
        password: null,
        password_confirm: null,
      },

      // Validation
      errors: {
        password: false,
        password_confirm: false,
      },

      // Loading states
      isFetched: true,
      isLoading: false,

    };
  },

  created() {
    if (this.$props.type == "edit") {
      this.isFetched = false;
      this.isLoading = true;
      let uri = `/api/user`;
      this.axios.get(uri).then(response => {
        this.isFetched = true;
        this.isLoading = false;
      });
    }
  },

  methods: {

    // Submit form
    submit() {
      if (this.$props.type == "edit") {
        this.update();
      }
    },

    update() {
      let uri = `/api/user/password`;
      this.isLoading = true;
      this.axios.post(uri, this.user).then(response => {
        this.$router.push({ name: "dashboard" });
        this.$notify({ type: "success", text: "Änderungen gespeichert!" });
        this.isLoading = false;
      });
    },

  },

  computed: {
    title: function() {
      return this.$props.type == "edit" 
        ? "Passwort ändern" 
        : "Passwort hinzufügen";
    }
  }
};
</script>
