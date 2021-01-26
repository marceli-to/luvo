<template>
<div>
  <loading-indicator v-if="isLoading"></loading-indicator>
  <div :class="[hasSelector ? 'is-visible' : '', 'upload-overlay-posts']">
    <div class="upload-overlay__close">
      <a href="javascript:;" class="feather-icon icon-close-overlay" @click.prevent="hideOverlay()">
        <x-icon size="24"></x-icon>
      </a>
    </div>
    <div class="post-selector">

      <h1 v-if="selectorType == 'products'">Shop Artikel</h1>
      <h1 v-if="selectorType == 'illustrations'">Illustration</h1>
      <h1 v-if="selectorType == 'news'">news</h1>

      <div class="post-selector-listing" v-if="selectorType == 'products' && products.length">
        <div v-for="p in products" :key="p.id">
          <h2>{{p.title.de}}</h2>
          <div class="post-selector-listing__items">
            <figure v-for="image in p.images" :key="image.id">
              <a href="javascript:;" @click.prevent="store(image.id, 'product')">
                <img :src="getSource(image, 'thumbnail')" height="300" width="300">
                <figcaption v-if="image.preview == 1">Vorschau</figcaption>
              </a>
            </figure>
          </div>
        </div>
      </div>

      <div class="post-selector-listing" v-if="selectorType == 'illustrations' && illustrations.length">
        <div v-for="i in illustrations" :key="i.id">
          <h2>{{i.title.de}}</h2>
          <div class="post-selector-listing__items">
            <figure v-for="image in i.images" :key="image.id">
              <a href="javascript:;" @click.prevent="store(image.id, 'illustration')">
                <img :src="getSource(image, 'thumbnail')" height="300" width="300">
                <figcaption v-if="image.preview == 1">Vorschau</figcaption>
              </a>
            </figure>
          </div>
        </div>
      </div>

      <div class="post-selector-listing" v-if="selectorType == 'news' && news.length">
        <div v-for="n in news" :key="n.id">
          <h2>{{n.title.de}}</h2>
          <div class="post-selector-listing__items">
            <figure v-for="image in n.images" :key="image.id">
              <a href="javascript:;" @click.prevent="store(image.id, 'news')">
                <img :src="getSource(image, 'thumbnail')" height="300" width="300">
                <figcaption v-if="image.preview == 1">Vorschau</figcaption>
              </a>
            </figure>
          </div>
        </div>
      </div>
      <div>
        <div class="form-row-button">
          <a href="javascript:;" class="btn-primary" @click.prevent="hideOverlay()">Schliessen</a>
        </div>
      </div>
    </div>
  </div>
  <div :class="isFetched ? 'is-loaded' : 'is-loading'">
    <header class="content-header">
      <h1>Posts</h1>
      <a href="" @click.prevent="toggleMini()" class="feather-icon feather-icon--prepend">
        <grid-icon size="16"></grid-icon>
        <span>Toggle mini</span>
      </a>
    </header>
    <div :class="[isMini ? 'is-mini' : '', 'posts']">
      <draggable 
        :disabled="false"
        v-model="posts" 
        @end="order()"
        ghost-class="draggable-ghost"
        draggable=".post__item">
        <div
          :class="[p.publish == 0 ? 'is-disabled' : '', 'post__item is-draggable']"
          v-for="p in posts"
          :key="p.id"
        >
          <figure v-if="p.product_image">
            <img :src="getSource(p.product_image, 'cache')" height="300" width="300">
            <figcaption>{{p.product_image.product.title.de}}</figcaption>
            <post-actions :id="p.id" :record="p"></post-actions>
          </figure>
          <figure v-if="p.illustration_image">
            <img :src="getSource(p.illustration_image, 'cache')" height="300" width="300">
            <figcaption>{{p.illustration_image.illustration.title.de}}</figcaption>
            <post-actions :id="p.id" :record="p"></post-actions>
          </figure>
          <figure v-if="p.news_image">
            <img :src="getSource(p.news_image, 'thumbnail')" height="300" width="300">
            <figcaption>{{p.news_image.news.title.de}}</figcaption>
            <post-actions :id="p.id" :record="p"></post-actions>
          </figure>
        </div>
      </draggable>
    </div>
    <footer class="module-footer ">
      <div>
        <a href="javascript:;" @click.prevent="showOverlay('products')" class="btn-primary btn-add-post has-icon">
          <file-plus-icon size="18"></file-plus-icon>
          <span>Shop Artikel</span>
        </a>
        <a href="javascript:;" @click.prevent="showOverlay('illustrations')" class="btn-primary btn-add-post has-icon">
          <file-plus-icon size="18"></file-plus-icon>
          <span>Illustration</span>
        </a>
        <a href="javascript:;" @click.prevent="showOverlay('news')" class="btn-primary btn-add-post has-icon">
          <file-plus-icon size="18"></file-plus-icon>
          <span>News</span>
        </a>
      </div>
    </footer>
  </div>
</div>
</template>
<script>

// Icons
import { FilePlusIcon, XIcon, GridIcon } from 'vue-feather-icons';

// Components
import PostActions from "@/components/ui/PostActions.vue";
import draggable from "vuedraggable";

// Mixins
import ErrorHandling from "@/mixins/ErrorHandling";
import Helpers from "@/mixins/Helpers";
import ImageUtils from "@/components/images/mixins/utils";

export default {

  components: {
    PostActions,
    FilePlusIcon,
    GridIcon,
    XIcon,
    draggable
  },

  mixins: [ErrorHandling, Helpers, ImageUtils],

  data() {
    return {
      isLoading: false,
      isFetched: false,
      isMini: false,
      posts: [],

      // selector
      hasSelector: false,
      selectorType: null,
      illustrations: [],
      products: [],
      news: [],
    };
  },

  created() {
    this.fetch();
  },

  methods: {

    fetch() {
      this.axios.get(`/api/posts`).then(response => {
        this.posts = response.data.data;
        this.isFetched = true;
        this.isLoading = false;
      });
    },

    store(id, type) {

      let data = {
        product_image_id: type == 'product' ? id : null,
        illustration_image_id: type == 'illustration' ? id : null,
        news_image_id: type == 'news' ? id : null,
        publish: 1,
      };

      this.isLoading = true;
      this.axios.post('/api/post', data).then(response => {
        this.$notify({ type: "success", text: "Post erfasst!" });
        this.fetch();
        this.hideOverlay();
      });
    },

    destroy(id, event) {
      if (confirm("Bitte löschen bestätigen!")) {
        let uri = `/api/post/${id}`;
        this.isLoading = true;
        this.axios.delete(uri).then(response => {
          this.fetch();
          this.isLoading = false;
        });
      }
    },

    toggle(id,event) {
      let uri = `/api/post/state/${id}`;
      this.isLoading = true;
      this.axios.get(uri).then(response => {
        const index = this.posts.findIndex(x => x.id === id);
        this.posts[index].publish = response.data;
        this.$notify({ type: "success", text: "Status geändert" });
        this.isLoading = false;
      });
    },

    order() {
      let posts = this.posts.map(function(post, index) {
        post.order = index;
        return post;
      });
      if (this.debounce) return;
      this.debounce = setTimeout(function() {
        this.debounce = false 
        this.axios.post(`/api/posts/order`, {posts: posts}).then((response) => {
          this.$notify({type: 'success', text: 'Reihenfolge angepasst'});
        });
      }.bind(this, posts), 500);
    },

    showOverlay(type) {

      if (type == 'products') {
        this.isLoading = true;
        this.axios.get(`/api/products`).then(response => {
          this.products = response.data.data;
          this.hasSelector = true;
          this.selectorType = type;
          this.isLoading = false;
        });
      }
      
      if (type == 'illustrations') {
        this.isLoading = true;
        this.axios.get(`/api/illustrations`).then(response => {
          this.illustrations = response.data.data;
          this.hasSelector = true;
          this.selectorType = type;
          this.isLoading = false;
        });
      }

      if (type == 'news') {
        this.isLoading = true;
        this.axios.get(`/api/news`).then(response => {
          this.news = response.data.data;
          this.hasSelector = true;
          this.selectorType = type;
          this.isLoading = false;
        });
      }
    },

    hideOverlay() {
      this.hasSelector = false;
      this.selectorType = null;
    },

    toggleMini() {
      this.isMini = this.isMini ? false : true;
    }
  }
}
</script>