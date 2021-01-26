export default {

  data() {
    return { 
      errors: null,
    }
  },
  
  mounted() {

    window.intercepted.$on('response:401', data => {
      this.unauthorized(data);
    });

    window.intercepted.$on('response:403', data => {
      this.forbiddenError(data);
    });

    window.intercepted.$on('response:404', data => {
      this.notFoundError(data);
    });

    window.intercepted.$on('response:405', data => {
      this.notAllowed(data);
    });

    window.intercepted.$on('response:422', data => {
      this.validationError(data);
    });

    window.intercepted.$on('response:500', data => {
      this.serverError(data);
    });

  },

  beforeDestroy(){
    window.intercepted.$off('response:401', this.listener);
    window.intercepted.$off('response:403', this.listener);
    window.intercepted.$off('response:404', this.listener);
    window.intercepted.$off('response:405', this.listener);
    window.intercepted.$off('response:422', this.listener);
    window.intercepted.$off('response:500', this.listener);
  },

  methods: {

    validationError(data) {
      let errors = {};
      data.body.forEach(function(key) {
        errors[key.field] = true;
      });
      this.errors = errors;
      this.isLoading = false;
      this.$notify({ type: "error", text: `Bitte alle mit * markierten Felder prüfen!`});
    },

    serverError(data) {
      this.isLoading = false;
      this.$notify({ type: "error", text: `${data.status} ${data.code}<br>${data.body.message}`});
    },

    notFoundError(data) {
      this.$notify({ type: "error", text: `${data.status} ${data.code}`});
      this.$router.push({ name: 'not-found' });
    },

    notAllowed(data) {
      this.isLoading = false;
      this.$notify({ type: "error", text: `${data.status} ${data.code}`});
    },

    forbiddenError(data) {
      this.isLoading = false;
      this.$notify({ type: "error", text: `${data.status} - Zugriff verweigert!`});
      this.$router.push({ name: 'forbidden' });
    },

    unauthorized(data) {
      document.location.href = '/login';
    }
  },

};
