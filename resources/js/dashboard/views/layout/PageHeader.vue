<template>
  <div>
    <header class="page-header">
      <div>
        <a href="/administration" class="brand">
          <img src="/assets/dashboard/img/logo.svg" width="300" height="100" alt="luksundvogt.ch">
        </a>
        <a href="javascript:;" @click="emit('open-menu')" class="feather-icon menu-open">
          <MenuIcon size="24" />
        </a>
      </div>
    </header>
  </div>
</template>
<script setup>
import { MenuIcon } from 'lucide-vue-next';

const emit = defineEmits(['open-menu']);
</script>
