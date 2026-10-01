import { ref } from 'vue';
import { notify } from '@kyvg/vue3-notification';
import http from '@/lib/http';
import { confirmDelete } from '@/lib/utils';

/**
 * A list of records with publish toggle and delete.
 *
 * list      url of the list, e.g. '/api/team/members'
 * resource  resource path below /api for state and delete, e.g. 'team/member'
 * loaded    (items) => void, after each fetch
 */
export function useListing({ list, resource, isLoading = ref(false), loaded = () => {} }) {
  const items = ref([]);
  const isFetched = ref(false);

  async function fetch() {
    isLoading.value = true;
    try {
      const { data } = await http.get(list);
      items.value = data.data;
      loaded(items.value);
      isFetched.value = true;
    }
    catch {
      // Notified by the http error handler
    }
    finally {
      isLoading.value = false;
    }
  }

  async function toggle(id) {
    isLoading.value = true;
    try {
      const { data } = await http.get(`/api/${resource}/state/${id}`);
      items.value.find(item => item.id === id).publish = data;
      notify({ type: 'success', text: 'Status geändert' });
    }
    catch {
      // Notified by the http error handler
    }
    finally {
      isLoading.value = false;
    }
  }

  async function destroy(id) {
    if (!confirmDelete()) {
      return;
    }
    isLoading.value = true;
    try {
      await http.delete(`/api/${resource}/${id}`);
    }
    catch {
      // Notified by the http error handler
    }
    await fetch();
  }

  fetch();

  return { items, isFetched, isLoading, fetch, toggle, destroy };
}
