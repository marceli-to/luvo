import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { notify } from '@kyvg/vue3-notification';
import http, { validationErrors } from '@/lib/http';

/**
 * Create/edit form for an API resource.
 *
 * type      'create' | 'edit' (route prop)
 * endpoint  resource path below /api, e.g. 'team/member'
 * model     () => empty record; in edit mode, null values from the API
 *           fall back to these defaults (e.g. a missing translation object)
 * redirect  route to go to after saving, or (record) => route
 * titles    { create, edit }
 * load      extra requests (() => Promise) the form waits for
 */
export function useResourceForm({ type, endpoint, model, redirect, titles, load = [] }) {
  const route = useRoute();
  const router = useRouter();

  const isEdit = type === 'edit';
  const url = isEdit ? `/api/${endpoint}/${route.params.id}` : `/api/${endpoint}`;

  const record = ref(model());
  const errors = ref({});
  const isLoading = ref(false);
  const isFetched = ref(false);
  const title = computed(() => isEdit ? titles.edit : titles.create);

  async function fetch() {
    const { data } = await http.get(url);
    const defaults = model();
    for (const key in defaults) {
      data[key] ??= defaults[key];
    }
    record.value = data;
  }

  async function init() {
    isLoading.value = true;
    try {
      await Promise.all([isEdit ? fetch() : null, ...load.map(request => request())]);
      isFetched.value = true;
    }
    catch {
      // Notified by the http error handler
    }
    finally {
      isLoading.value = false;
    }
  }

  async function submit() {
    isLoading.value = true;
    try {
      await (isEdit ? http.put(url, record.value) : http.post(url, record.value));
      errors.value = {};
      router.push(typeof redirect === 'function' ? redirect(record.value) : redirect);
      notify({ type: 'success', text: isEdit ? 'Änderungen gespeichert!' : 'Daten erfasst!' });
    }
    catch (error) {
      errors.value = validationErrors(error) ?? errors.value;
    }
    finally {
      isLoading.value = false;
    }
  }

  init();

  return { record, errors, isEdit, isLoading, isFetched, title, submit };
}

/**
 * The tabs of the forms with images.
 */
export const formTabs = [
  { key: 'data', label: 'Daten' },
  { key: 'image', label: 'Bilder' },
  { key: 'settings', label: 'Einstellungen' },
];

/**
 * { de: null, fr: null, en: null }
 */
export function translations() {
  return { de: null, fr: null, en: null };
}
