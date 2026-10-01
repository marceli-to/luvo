<template>
  <div class="is-required">{{ text }}</div>
</template>
<script setup>
defineProps({
  text: { type: String, default: 'Pflichtfeld' },
});
</script>
