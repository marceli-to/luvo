<template>
  <div>
    <div class="upload-listing">
      <a href="" class="icon-view" @click.prevent="view = view === 'grid' ? 'list' : 'grid'">
        <PhLayout :size="18" weight="light" />
        <span v-if="view === 'grid'">Grid Ansicht</span>
        <span v-else>Listen Ansicht</span>
      </a>
      <div class="is-list" v-if="view === 'list'">
        <draggable
          v-model="images"
          item-key="id"
          ghost-class="draggable-ghost"
          draggable=".is-draggable"
          @end="order(images)"
        >
          <template #item="{ element: image }">
            <div class="upload-item-row is-draggable">
              <figure>
                <img :src="imageUrl(image, 'thumbnail')" height="300" width="300">
              </figure>
              <div>
                <span class="icon-move"></span>
              </div>
            </div>
          </template>
        </draggable>
      </div>
      <div v-else>
        <figure
          v-for="image in images"
          :key="image.id ?? image.name"
          :class="[image.publish == 0 ? 'is-disabled' : '', 'upload-item']"
        >
          <a :href="imageUrl(image, 'crop')" target="_blank" class="upload__preview">
            <img :src="imageUrl(image, 'thumbnail')" height="300" width="300">
            <span v-if="label(image)" class="image-label">{{ label(image) }}</span>
          </a>
          <div class="upload__actions">
            <div>
              <div>
                <a href="javascript:;" class="feather-icon" @click.prevent="emit('toggle', image)">
                  <PhEye v-if="image.publish == 1" :size="18" weight="light" />
                  <PhEyeSlash v-else :size="18" weight="light" />
                </a>
              </div>
              <div>
                <a href="javascript:;" class="feather-icon" @click.prevent="openEdit(image)">
                  <PhPencil :size="18" weight="light" />
                </a>
              </div>
              <div>
                <a :href="imageUrl(image, 'crop')" target="_blank" class="feather-icon">
                  <PhImage :size="18" weight="light" />
                </a>
              </div>
              <div>
                <a href="javascript:;" class="feather-icon" @click.prevent="emit('destroy', image)">
                  <PhTrash :size="18" weight="light" />
                </a>
              </div>
              <div>
                <a href="javascript:;" class="feather-icon" @click.prevent="openCropper(image)">
                  <PhCrop :size="18" weight="light" />
                </a>
              </div>
            </div>
          </div>
        </figure>
      </div>
    </div>

    <div :class="[isEditOpen ? 'is-visible' : '', 'upload-overlay-edit']">
      <a href="javascript:;" class="feather-icon upload-overlay__close" title="Schliessen" @click.prevent="isEditOpen = false">
        <PhX :size="24" weight="light" />
      </a>
      <div class="upload-overlay__body upload-overlay__grid" v-if="editItem">
        <div>
          <figure v-if="isEditOpen">
            <img :src="imageUrl(editItem, 'crop')" height="300" width="300">
            <figcaption v-if="caption(editItem)">
              <span>{{ caption(editItem) }}</span>
            </figcaption>
          </figure>
        </div>
        <div>
          <template v-if="!devices || editItem.device === 'mobile'">
            <template v-if="translatedCaptions">
              <div class="form-row">
                <label>Bildlegende</label>
                <input type="text" v-model="editItem.caption.de" />
              </div>
              <div class="form-row">
                <label>Bildlegende (FR)</label>
                <input type="text" v-model="editItem.caption.fr" />
              </div>
              <div class="form-row">
                <label>Bildlegende (EN)</label>
                <input type="text" v-model="editItem.caption.en" />
              </div>
            </template>
            <div class="form-row" v-else>
              <label>Bildlegende</label>
              <input type="text" v-model="editItem.caption" />
            </div>
          </template>
          <div class="form-row" v-if="devices && editItem.device === 'desktop'">
            <p style="color: #DA2C38">Info: Die Bildlegenden für die Desktop-Version (dt/en) müssen auf Grund der Änderung der Bilddarstellung direkt ins Bild integriert werden. Bivgrafik haben dafür eine Indesign-Vorlage erstellt. Die Bild-Seiten mit Legenden werden als JPG aus dem Indesign exportiert.</p>
          </div>
          <div class="form-row" v-if="devices">
            <label>Anwendung</label>
            <div class="select-wrapper is-medium">
              <select v-model="editItem.device" name="device">
                <option v-for="(label, device) in deviceLabels" :key="device" :value="device">{{ label }}</option>
              </select>
            </div>
          </div>
          <div class="form-row-button">
            <a href="javascript:;" class="btn-primary" @click.prevent="isEditOpen = false">Schliessen</a>
          </div>
        </div>
      </div>
    </div>

    <div :class="[isCropperOpen ? 'is-visible' : '', 'upload-overlay-cropper']">
      <div class="upload-overlay__loader" v-if="isCropperLoading">Bild wird geladen...</div>
      <template v-else-if="cropItem && isCropperOpen">
        <a href="javascript:;" class="feather-icon upload-overlay__close" title="Schliessen" @click.prevent="closeCropper()">
          <PhX :size="24" weight="light" />
        </a>
        <div class="upload-overlay__body upload-overlay-cropper__wrapper">
          <div :class="'is-' + cropItem.orientation">
            <div class="cropper-formats" v-if="devices">
              <div v-for="(label, device) in deviceLabels" :key="device">
                <a href="javascript:;" class="btn-cropper-format" @click.prevent="switchDevice(device)">{{ label }}</a>
              </div>
            </div>
            <div class="cropper-info">{{ cropSize.w }} x {{ cropSize.h }}px</div>
            <Cropper
              class="upload-overlay-cropper__cropper"
              :src="cropSrc"
              :default-position="defaultPosition"
              :default-size="defaultSize"
              :debounce="false"
              :stencil-props="{
                aspectRatio: cropRatio,
                linesClassnames: { default: 'line' },
                handlersClassnames: { default: 'handler' },
              }"
              @change="change"
            />
            <div class="form-buttons">
              <a href="javascript:;" class="btn-primary" @click.prevent="saveCrop()">Speichern</a>
              <a href="javascript:;" class="btn-secondary" @click.prevent="closeCropper()">Abbrechen</a>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive } from 'vue';
import draggable from 'vuedraggable';
import { Cropper } from 'vue-advanced-cropper';
import { PhLayout, PhEye, PhEyeSlash, PhPencil, PhImage, PhTrash, PhCrop, PhX } from '@phosphor-icons/vue';
import { imageUrl, preloadImage } from '@/lib/images';
import { useOrder } from '@/composables/useOrder';
import { useEscape } from '@/composables/useEscape';

const props = defineProps({
  // resource path below /api, for saving the order, e.g. 'team/member'
  endpoint: { type: String, required: true },

  // images are for 'desktop' (crop 10:12) or 'mobile' (crop 3:2)
  devices: { type: Boolean, default: false },

  // caption is { de, fr, en } instead of a string
  translatedCaptions: { type: Boolean, default: false },

  // images can be marked as preview (label "Vorschau", crop 1:1)
  preview: { type: Boolean, default: false },

  // crop ratio when neither devices nor preview apply
  ratio: { type: Object, default: () => ({ w: 16, h: 10 }) },
});

const images = defineModel('images', { type: Array, required: true });

const emit = defineEmits(['toggle', 'destroy', 'save-coords']);

const view = ref('grid');

const deviceLabels = { desktop: 'Desktop', mobile: 'Mobile' };
const deviceRatios = { desktop: 10 / 12, mobile: 3 / 2 };

// Images without id aren't saved yet; their order goes with the record
const saveOrder = useOrder({ url: `/api/${props.endpoint}/image/order`, key: 'images', delay: 1000 });
function order(list) {
  list.some(image => image.id === null) ? list.forEach((image, index) => image.order = index) : saveOrder(list);
}

function label(image) {
  if (props.devices) {
    return deviceLabels[image.device];
  }
  return props.preview && image.preview == 1 ? 'Vorschau' : null;
}

function caption(image) {
  return props.translatedCaptions ? image.caption?.de : image.caption;
}

// Edit overlay
const isEditOpen = ref(false);
const editItem = ref(null);

function openEdit(image) {
  editItem.value = image;
  isEditOpen.value = true;
}

// Cropper overlay
const isCropperOpen = ref(false);
const isCropperLoading = ref(false);
const cropItem = ref(null);
const cropSrc = ref(null);
const cropRatio = ref(1);
// Desktop / Mobile chosen in the cropper; applied to the image on save
const cropDevice = ref(null);
const coords = reactive({ w: 0, h: 0, x: 0, y: 0 });
const cropSize = reactive({ w: null, h: null });
const cropDefaults = { w: 425, h: 510, x: 0, y: 0 };

// The ratio of a saved crop, else the default for the image
function ratioFor(image) {
  if (props.devices) {
    return image.coords_w > 0 && image.coords_h > 0
      ? image.coords_w / image.coords_h
      : deviceRatios[image.device];
  }
  if (props.preview && image.preview == 1) {
    return 1;
  }
  return props.ratio.w / props.ratio.h;
}

async function openCropper(image) {
  cropItem.value = image;
  cropDevice.value = image.device;
  cropRatio.value = ratioFor(image);
  isCropperOpen.value = true;
  isCropperLoading.value = true;
  try {
    cropSrc.value = await preloadImage(imageUrl(image, 'original'));
  }
  finally {
    isCropperLoading.value = false;
  }
}

function closeCropper() {
  isCropperOpen.value = false;
}

// Desktop/Mobile: that format's ratio; the device itself changes on Speichern
function switchDevice(device) {
  cropDevice.value = device;
  cropRatio.value = deviceRatios[device];
}

function change({ coordinates }) {
  coords.w = coordinates.width;
  coords.h = coordinates.height;
  coords.x = coordinates.left;
  coords.y = coordinates.top;
  cropSize.w = Math.floor(coordinates.width);
  cropSize.h = Math.floor(coordinates.height);
}

function defaultPosition() {
  return {
    left: cropItem.value.coords_x || cropDefaults.x,
    top: cropItem.value.coords_y || cropDefaults.y,
  };
}

function defaultSize() {
  return {
    width: cropItem.value.coords_w || cropDefaults.w,
    height: cropItem.value.coords_h || cropDefaults.h,
  };
}

function saveCrop() {
  const image = cropItem.value;
  image.coords_w = coords.w;
  image.coords_h = coords.h;
  image.coords_x = coords.x;
  image.coords_y = coords.y;
  if (props.devices) {
    image.device = cropDevice.value;
  }
  emit('save-coords', image);
  closeCropper();
}

useEscape(() => {
  isEditOpen.value = false;
  closeCropper();
});
</script>
