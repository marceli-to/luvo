<template>
  <div>
    <div class="upload-listing">
      <div>
        <figure
          :class="[image.publish == 0 ? 'is-disabled' : '', 'upload-item']"
          v-for="image in images"
          :key="image.id"
        >
          <a :href="getSource(image, imagePreviewRoute)" target="_blank" class="upload__preview">
            <img :src="getSource(image, 'thumbnail')" height="300" width="300">
          </a>
          <div class="upload__actions">
            <image-actions :image="image" :publish="image.publish" :grid="image.is_grid" :imagePreviewRoute="imagePreviewRoute"></image-actions>
          </div>
        </figure>
      </div>
    </div>
    <div :class="[hasOverlayEdit ? 'is-visible' : '', 'upload-overlay-edit']">
      <div class="upload-overlay__close">
        <a
          href="javascript:;"
          class="feather-icon icon-close-overlay"
          @click.prevent="hideEdit()"
        >
          <x-icon size="24"></x-icon>
        </a>
      </div>
      <div class="upload-overlay__grid">
        <figure v-if="hasOverlayEdit">
            <img :src="getSource(overlayItem.name, 'large')" height="300" width="300">
            <figcaption v-if="overlayItem.caption">
              <span v-if="overlayItem.caption">{{overlayItem.caption}}</span>
            </figcaption>
          </figure>
        <div>
          <div class="form-row">
            <label>Bildlegende</label>
            <input type="text" 
              v-model="overlayItem.caption"
            />
          </div>
          <div class="form-row-button">
            <a
              href="javascript:;"
              class="btn-primary"
              @click.prevent="hideEdit()"
            >Schliessen</a>
          </div>
        </div>
      </div>
    </div>
    <div :class="[hasOverlayCropper ? 'is-visible' : '', 'upload-overlay-cropper']">
      <div class="upload-overlay__loader" v-if="isLoading">Bild wird geladen...</div>
      <div class="upload-overlay__close" v-if="!isLoading">
        <a
          href="javascript:;"
          class="feather-icon icon-close-overlay"
          @click.prevent="hideCropper()"
        >
          <x-icon size="24"></x-icon>
        </a>
      </div>
      <div class="upload-overlay-cropper__wrapper" v-if="!isLoading">
        <div :class="'is-' + overlayItem.orientation">
          <div class="cropper-info">{{ cropW }} x {{ cropH }}px</div>
          <cropper
            :src="cropImage"
            :defaultPosition="defaultPosition"
            :defaultSize="defaultSize"
            :stencilProps="{
              aspectRatio: this.ratioW/this.ratioH,
              linesClassnames: {
                default: 'line',
              },
              handlersClassnames: {
                default: 'handler'
              }
            }"
            @change="change"
          ></cropper>
          <div class="form-buttons">
            <a
              href="javascript:;"
              class="btn-primary"
              @click.prevent="saveCoords(overlayItem)"
            >Speichern</a>
            <a href="javascript:;"
                class="btn-secondary"
                @click.prevent="hideCropper()">
              Abbrechen
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>

// Image components
import ImageActions from "@/components/images/Actions.vue";

// Image mixins
import ImageEdit from "@/components/images/mixins/edit";
import ImageCrop from "@/components/images/mixins/crop";
import ImageUtils from "@/components/images/mixins/utils";

// Cropper
import { Cropper } from "vue-advanced-cropper";

// Icons
import { XIcon } from 'vue-feather-icons';

export default {
  components: {
    ImageActions,
    XIcon,
    Cropper
  },

  props: {
    images: Array,

    updateOnChange: {
      type: Boolean,
      default: false
    },

    categories: {
      type: Object,
      default: null
    },

    imagePreviewRoute: {
      type: String,
      default: "large"
    },

    ratioW: {
      type: Number,
      default: 3.33
    },

    ratioH: {
      type: Number,
      default: 4
    }
  },

  mixins: [ImageUtils, ImageEdit, ImageCrop]
};
</script>