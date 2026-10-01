import axios from 'axios';

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

/**
 * Minimal stand-in for vue-axios-interceptors (Vue 2 only): emits
 * 'response:<status>' with { status, code, body } on window.intercepted.
 * For 422, body is the flat list of validation messages; the form requests
 * define them as { field, error }, which the ErrorHandling mixin reads.
 */
const handlers = {};

window.intercepted = {
  $on(event, handler) {
    (handlers[event] = handlers[event] || []).push(handler);
  },
  // Without a handler, all handlers of the event are removed (like Vue 2's $off)
  $off(event, handler) {
    handlers[event] = handler ? (handlers[event] || []).filter(h => h !== handler) : [];
  },
  $emit(event, data) {
    (handlers[event] || []).slice().forEach(h => h(data));
  },
};

const statusTexts = {
  401: 'Unauthorized',
  403: 'Forbidden',
  404: 'Not Found',
  405: 'Method Not Allowed',
  422: 'Unprocessable Entity',
  500: 'Internal Server Error',
};

function handleResponse(response) {
  if (!response || !statusTexts[response.status]) {
    return;
  }
  const status = response.status;
  let body = response.data;

  if (status === 422 && body && body.errors) {
    body = [];
    for (const field in response.data.errors) {
      response.data.errors[field].forEach(message => body.push(message));
    }
  }

  window.intercepted.$emit('response:' + status, { status, code: statusTexts[status], body });
}

axios.interceptors.response.use(response => {
  handleResponse(response);
  return response;
}, error => {
  handleResponse(error.response);
  return Promise.reject(error);
});

export default axios;
