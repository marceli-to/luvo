<template>
  <div>
    <label class="is-sm">{{ label }}</label>
    <div class="form-radio">
      <input
        v-model="value"
        type="radio"
        :name="name + '_1'"
        :id="name + '_1'"
        :value="1"
        class="visually-hidden"
      >
      <label :for="name + '_1'" class="form-control">{{ labelTrue }}</label>
      <input
        v-model="value"
        type="radio"
        :name="name + '_0'"
        :id="name + '_0'"
        :value="0"
        class="visually-hidden"
      >
      <label :for="name + '_0'" class="form-control">{{ labelFalse }}</label>
    </div>
  </div>
</template>
<script setup>
defineProps({
  label: { type: String, default: '' },
  name: { type: String, default: '' },
  labelTrue: { type: String, default: 'Ja' },
  labelFalse: { type: String, default: 'Nein' },
});

// The API returns 0/1, sometimes as strings
const value = defineModel({
  get: value => value === null || value === undefined ? value : Number(value),
});
</script>
