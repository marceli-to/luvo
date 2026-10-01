import { onBeforeUnmount } from 'vue';
import { notify } from '@kyvg/vue3-notification';
import http from '@/lib/http';
import { withOrder } from '@/lib/utils';

/**
 * Saves the order of a list after dragging. Several drags in a row are sent
 * once, with the final order.
 *
 * url    e.g. '/api/team/member/order'
 * key    payload key, e.g. 'members'
 * saved  () => void, after saving
 */
export function useOrder({ url, key, delay = 500, saved = () => {} }) {
  let timer = null;

  function order(items) {
    withOrder(items);
    clearTimeout(timer);
    timer = setTimeout(async () => {
      try {
        await http.post(url, { [key]: items });
        notify({ type: 'success', text: 'Reihenfolge angepasst' });
        saved();
      }
      catch {
        // Notified by the http error handler
      }
    }, delay);
  }

  onBeforeUnmount(() => clearTimeout(timer));

  return order;
}
