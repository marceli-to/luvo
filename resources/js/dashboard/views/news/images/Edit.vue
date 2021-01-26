<template>
  <div>
    <div class="upload-listing">
      <a href="" class="icon-view" @click.prevent="toggleView()">
        <span v-if="view == 'grid'">Grid Ansicht</span>
        <span v-if="view == 'list'">Listen Ansicht</span>
      </a>
      <div class="is-list" v-if="view == 'list'">
        <template>
          <draggable 
            :disabled="false"
            v-model="imageData" 
            @end="order"
            ghost-class="draggable-ghost"
            draggable=".is-draggable">
            <div class="upload-item-row is-draggable" v-for="(image) in imageData" :key="image.id">
              <figure>
                <img :src="getSource(image, 'thumbnail')" height="300" width="300">
              </figure>
              <div>
                <span class="icon-move"></span>
              </div>
            </div>
          </draggable>
        </template>
      </div>
      <div v-if="view == 'grid'">
        <figure
          :class="[image.publish == 0 ? 'is-disabled' : '', 'upload-item']"
          v-for="image in images"
          :key="image.id"
        >
          <a :href="getSource(image, 'cache')" target="_blank" class="upload__preview">
            <img :src="getSource(image, 'thumbnail')" height="300" width="300">
            <span v-if="image.preview == 1" class="image-label">Vorschau</span>
          </a>
          <div class="upload__actions">
            <image-actions :image="image" :publish="image.publish" :grid="image.is_grid" :imagePreviewRoute="'cache'"></image-actions>
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
        <div>
          <figure v-if="hasOverlayEdit">
            <img :src="getSource(overlayItem, 'cache')" height="300" width="300">
            <figcaption v-if="overlayItem.caption.de">
              <span v-if="overlayItem.caption.de">{{overlayItem.caption.de}}</span>
            </figcaption>
          </figure>
        </div>
        <div>
          <div class="form-row">
            <label>Bildlegende</label>
            <input type="text" v-model="overlayItem.caption.de" />
          </div>
          <div class="form-row">
            <label>Bildlegende (EN)</label>
            <input type="text" v-model="overlayItem.caption.en" />
          </div>
          <div class="form-row">
            <label class="is-sm">Vorschaubild?</label>
            <div class="form-radio">
              <input
                v-model="overlayItem.preview"
                type="radio"
                name="preview"
                id="preview_1"
                value="1"
                class="visually-hidden"
              >
              <label for="preview_1" class="form-control">Ja</label>
              <input
                v-model="overlayItem.preview"
                type="radio"
                name="preview"
                id="preview_0"
                value="0"
                class="visually-hidden"
              >
              <label for="preview_0" class="form-control">Nein</label>
            </div>
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
          <div class="cropper-formats">
            <div>
              <a href="javascript:;" @click.prevent="changeRatio(1,1)" class="btn-cropper-format">Square</a>
            </div>
            <div>
              <a href="javascript:;" @click.prevent="changeRatio(0,0)" class="btn-cropper-format">Free</a>
            </div>
          </div>
          <div class="cropper-info">{{ cropW }} x {{ cropH }}px</div>
          <cropper
            :src="cropImage"
            :defaultPosition="defaultPosition"
            :defaultSize="defaultSize"
            :stencilProps="{
              aspectRatio: this.ratio.w/this.ratio.h,
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

// Draggable
import draggable from 'vuedraggable';

// Cropper
import { Cropper } from "vue-advanced-cropper";

// Icons
import { XIcon } from 'vue-feather-icons';

export default {
  components: {
    ImageActions,
    XIcon,
    Cropper,
    draggable
  },

  props: {
    images: Array,

    updateOnChange: {
      type: Boolean,
      default: false
    },

    imagePreviewRoute: {
      type: String,
      default: "cache"
    },

    ratioW: {
      type: Number,
      default: 0
    },

    ratioH: {
      type: Number,
      default: 0
    },
  },

  data() {
    return {
      imageData: null,
      view: 'grid',
      isLoading: false,
      overlayItem: {
        name: '',
        caption: { de: null, en: null },
      },
      ratio: {
        w: null,
        h: null,
      },
    };
  },

  mixins: [ImageUtils, ImageEdit, ImageCrop],

  mounted() {
    this.imageData = this.$props.images;
    this.ratio.w = this.$props.ratioW;
    this.ratio.h = this.$props.ratioH;
  },

  methods: {

    changeRatio(w,h) {
      this.ratio.w = w;
      this.ratio.h = h;
      this.resetCropper();
    },

    resetCropper() {
      let image = this.overlayItem;
      this.hideCropper();
      this.showCropper(image, true)
    },

    showCropper(image, reset = false) {
      if (!reset) {
        if (image.preview == 1) {
          this.ratio.w = 1;
          this.ratio.h = 1;
        }
      }

      this.isLoading = true;
      this.hasOverlayCropper = true;
      this.overlayItem = image;
      this.axios.get(this.getSource(image, "original")).then(response => {
        this.cropImage = response.config.url;
        this.isLoading = false;
      });
    },

    order() {
      let images = this.imageData.map(function(image, index) {
        image.order = index;
        return image;
      });
      if (this.debounce) return;
      this.debounce = setTimeout(function(images) {
        this.debounce = false 
        let uri = `/api/news/image/order`;
        this.axios.post(uri, {images: images}).then((response) => {
          this.$notify({type: 'success', text: 'Reihenfolge angepasst'});
        });
      }.bind(this, images), 1000);
    },

    toggleView() {
      this.imageData = this.$props.images;
      this.view = this.view == 'grid' ? 'list' : 'grid';
    },
  }

};
</script>