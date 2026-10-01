<template>
  <div :class="['loading-indicator', classNames]">
    <PhCircleNotch :size="iconSize" weight="light" class="loading-indicator__icon" />
  </div>
</template>
<script setup>
import { PhCircleNotch } from '@phosphor-icons/vue';

defineProps({
  classNames: { type: String, default: null },
  iconSize: { type: String, default: '36' },
});
</script>
<style>
.loading-indicator {
  align-items: center;
  background-color: rgba(255,255,255,.8);
  display: flex;
  justify-content: center;
  height: 100%;
  min-height: 100px;
  left: 0;
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 501;
}

.loading-indicator.is-widget {
  position: absolute;
}

.loading-indicator__icon {
  animation: rotation 2s infinite linear;
  color: #2a2a2a;
}

@keyframes rotation {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(359deg);
  }
}
</style>
