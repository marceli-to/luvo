<template>
<div>
  <label class="is-sm">{{label}}</label>
    <div class="form-radio">
      <input
        v-model="value"
        type="radio"
        :name="name + '_1'"
        :id="name + '_1'"
        value="1"
        class="visually-hidden"
        @change="change($event.target.value)"
      >
      <label :for="name + '_1'" class="form-control">{{labelTrue}}</label>
      <input
        v-model="value"
        type="radio"
        :name="name + '_0'"
        :id="name + '_0'"
        value="0"
        class="visually-hidden"
        @change="change($event.target.value)"
      >
      <label :for="name + '_0'" class="form-control">{{labelFalse}}</label>
  </div>
</div>
</template>

<script>
export default {
  props: {
    label: {
      type: String,
      default: ''
    },
    model: {
      type: Number,
      default: null,
    },
    name: {
      type: String,
      default: ''
    },
    labelTrue: {
      type: String,
      default: 'Ja',
    },
    labelFalse: {
      type: String,
      default: 'Nein',
    }
  },

  data() {
    return {
      value: null,
    }
  },

  mounted() {
    this.value = this.$props.model;
  },

  methods: {
    change(value) {
      this.$emit('update:' + this.$props.name, parseInt(value));
    }
  }
}
</script>
