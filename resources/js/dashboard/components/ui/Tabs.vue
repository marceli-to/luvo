<template>
  <nav class="tabs">
    <ul>
      <li v-for="tab in tabs" :key="tab.key">
        <a
          href="javascript:;"
          :class="{ 'is-active': active === tab.key }"
          @click="active = tab.key"
        >{{ tab.label }}</a>
      </li>
    </ul>
  </nav>
</template>
<script setup>
defineProps({
  tabs: { type: Array, required: true },
});

const active = defineModel({ type: String, required: true });
</script>
