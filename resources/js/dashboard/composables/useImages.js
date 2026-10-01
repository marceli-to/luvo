import { useRoute } from 'vue-router';
import { notify } from '@kyvg/vue3-notification';
import http from '@/lib/http';
import { confirmDelete } from '@/lib/utils';

/**
 * Image actions of a form whose record has an images array.
 *
 * In edit mode every change goes to the API right away; in create mode the
 * images are kept on the record and saved with it.
 *
 * endpoint    resource path below /api, e.g. 'team/member'
 * foreignKey  the image's key of the record, e.g. 'team_member_id'
 * idKey       the response key with the new image's id, e.g. 'teamMemberImageId'
 * fields      () => extra fields of a new image (caption, device, preview)
 */
export function useImages({ record, isEdit, isLoading, endpoint, foreignKey, idKey, fields }) {
  const route = useRoute();
  const images = () => record.value.images;
  const base = `/api/${endpoint}/image`;

  async function store(upload) {
    const image = {
      id: null,
      name: upload.name,
      coords_w: 0,
      coords_h: 0,
      coords_x: 0,
      coords_y: 0,
      orientation: upload.orientation,
      order: 0,
      publish: 1,
      ...fields(),
    };

    if (!isEdit) {
      images().push(image);
      return;
    }

    image[foreignKey] = route.params.id;
    try {
      const { data } = await http.post(base, image);
      image.id = data[idKey];
      images().push(image);
      notify({ type: 'success', text: 'Bild gespeichert!' });
    }
    catch {
      // Notified by the http error handler
    }
  }

  async function destroy(image) {
    if (!confirmDelete()) {
      return;
    }
    isLoading.value = true;
    try {
      await http.delete(`${base}/${image.name}`);
      images().splice(images().indexOf(image), 1);
    }
    catch {
      // Notified by the http error handler
    }
    finally {
      isLoading.value = false;
    }
  }

  async function toggle(image) {
    if (image.id === null) {
      image.publish = image.publish == 1 ? 0 : 1;
      return;
    }
    isLoading.value = true;
    try {
      const { data } = await http.get(`${base}/state/${image.id}`);
      image.publish = data;
    }
    catch {
      // Notified by the http error handler
    }
    finally {
      isLoading.value = false;
    }
  }

  // The coords are already set on the image; unsaved images keep them
  // until the record is saved.
  async function saveCoords(image) {
    if (image.id === null) {
      return;
    }
    isLoading.value = true;
    try {
      await http.put(`${base}/${image.id}`, image);
      notify({ type: 'success', text: 'Änderungen gespeichert!' });
    }
    catch {
      // Notified by the http error handler
    }
    finally {
      isLoading.value = false;
    }
  }

  return { store, destroy, toggle, saveCoords };
}

/**
 * Uploader props for images.
 */
export const imageUpload = {
  url: '/api/image/upload',
  label: 'Upload',
  restrictions: 'jpg, png | max. 8 MB',
  acceptedFiles: '.png,.jpg,.jpeg',
  maxFiles: 99,
  maxFilesize: 8,
};
