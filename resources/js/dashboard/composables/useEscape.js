import { onMounted, onBeforeUnmount } from 'vue';

/**
 * Calls handler when Escape is pressed while the component is mounted.
 */
export function useEscape(handler) {
  const listener = event => event.key === 'Escape' && handler();
  onMounted(() => window.addEventListener('keyup', listener));
  onBeforeUnmount(() => window.removeEventListener('keyup', listener));
}
