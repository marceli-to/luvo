<template>
  <nav class="language">
    <div>
      <label>Übersetzung:</label>
      <ul>
        <li v-for="language in languages" :key="language">
          <a
            href="javascript:;"
            :class="{ 'is-active': locale === language }"
            @click="locale = language"
          >{{ language.toUpperCase() }}</a>
        </li>
      </ul>
    </div>
  </nav>
</template>
<script setup>
const languages = ['de', 'fr', 'en'];

const locale = defineModel({ type: String, default: 'de' });
</script>
