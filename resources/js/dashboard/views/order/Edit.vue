<template>
  <div class="overlay is-visible">
    <div class="overlay__inner">
      <div>
        <a href @click.prevent="hide()" class="feather-icon">
          <x-icon size="24"></x-icon>
        </a>
        <h2>Status ändern</h2>
        <div class="listing">
          <div class="listing__item">
            <div class="listing__item-body">
              <span>{{order.number}}</span>
              <separator />
              <span>{{order.firstname}} {{order.name}}</span>
           </div>
          </div>
        </div>
        <div class="form-row">
          <h2>Typ</h2>
          <div class="select-wrapper is-light is-wide">
            <select v-model="state_type" name="state_type">
              <option value="null">Bitte wählen...</option>
              <option v-for="(s, index) in state_types" :key="index" :value="s.value">{{ s.label }}</option>
            </select>
          </div>
        </div>
        <div class="sb-sm">
          <button class="btn-primary" @click.prevent="store()">Speichern</button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>

// Icons
import { XIcon } from "vue-feather-icons";

export default {
  components: {
    XIcon
  },

  props: {
    order: {
      type: Object,
      default: null
    },
  },

  data() {
    return {
      state_type: null,
      state_types: [
        {
          value: 0,
          label: 'Offen'
        },
        {
          value: 1,
          label: 'Bezahlt'
        },
        {
          value: 2,
          label: 'Geliefert'
        },
        {
          value: 3,
          label: 'Bezahlt & Geliefert'
        },
      ]
    }
  },

  methods: {

    store() {
      if (this.state_type == null) {
        this.$notify({
          type: "error",
          text: "Bitte Status auswählen!"
        });
        return false;
      }
      this.$parent.changeState(this.$props.order.id, this.state_type);
    },

    hide() {
      this.$parent.hideOverlay();
    },
  }
};
</script>