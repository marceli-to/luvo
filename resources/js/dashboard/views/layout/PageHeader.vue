<template>
  <div>
    <header class="page-header">
      <div>
        <a href="/administration" class="brand">
          <img src="/assets/dashboard/img/logo.svg" width="300" height="100" alt="luksundvogt.ch">
        </a>
        <a href="javascript:;" @click="emit('open-menu')" class="feather-icon menu-open">
          <PhList :size="24" weight="light" />
        </a>
      </div>
    </header>
  </div>
</template>
<script setup>
import { PhList } from '@phosphor-icons/vue';

const emit = defineEmits(['open-menu']);
</script>
