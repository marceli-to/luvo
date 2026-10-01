import { ref } from 'vue';
import http from '@/lib/http';

const user = ref(null);
let request = null;

/**
 * The logged-in user, fetched once and shared.
 */
export function useUser() {
  request ??= http.get('/api/user').then(response => user.value = response.data).catch(() => {});
  return user;
}
