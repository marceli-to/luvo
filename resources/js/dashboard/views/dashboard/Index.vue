<template>
  <div :class="user ? 'is-loaded' : 'is-loading'">
    <header class="content-header">
      <h1>Willkommen <strong v-if="user">{{ user.firstname }} {{ user.name }}</strong></h1>
    </header>
    <div class="content">
    </div>
  </div>
</template>
<script setup>
import { useUser } from '@/composables/useUser';

const user = useUser();
</script>
