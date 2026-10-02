import axios from 'axios';
import { notify } from '@kyvg/vue3-notification';

const http = axios.create({
  withCredentials: true,
  withXSRFToken: true,
  headers: { 'X-Requested-With': 'XMLHttpRequest' },
});

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
  http.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

/**
 * App-wide handling of failed requests. Callers still get the rejection
 * (e.g. forms read 422 errors via validationErrors) but need not notify.
 */
export function handleErrors(router) {
  http.interceptors.response.use(response => response, error => {
    const response = error.response;

    switch (response?.status) {
      case 401:
        document.location.href = '/login';
        break;
      case 403:
        notify({ type: 'error', text: '403 - Zugriff verweigert!' });
        router.push({ name: 'forbidden' });
        break;
      case 404:
        notify({ type: 'error', text: '404 Not Found' });
        router.push({ name: 'not-found' });
        break;
      case 405:
        notify({ type: 'error', text: '405 Method Not Allowed' });
        break;
      case 419:
        // Stale XSRF token (e.g. after a long pause): nothing was saved
        notify({ type: 'error', text: 'Die Sitzung ist abgelaufen, es wurde nichts gespeichert. Bitte die Seite neu laden.' });
        break;
      case 422:
        notify({ type: 'error', text: 'Bitte alle mit * markierten Felder prüfen!' });
        break;
      case 500:
        notify({ type: 'error', text: `500 Internal Server Error<br>${response.data?.message ?? ''}` });
        break;
    }

    return Promise.reject(error);
  });
}

/**
 * Fields that failed validation, as { field: true }. The API returns
 * errors.{attribute}[] = { field, error } (see BaseFormRequest).
 */
export function validationErrors(error) {
  if (error.response?.status !== 422) {
    return null;
  }
  const fields = {};
  Object.values(error.response.data.errors ?? {}).flat().forEach(e => fields[e.field] = true);
  return fields;
}

export default http;
