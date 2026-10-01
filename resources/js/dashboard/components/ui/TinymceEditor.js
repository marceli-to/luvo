import Editor from '@tinymce/tinymce-vue';

// tinymce-vue 6 sets license_key from its licenseKey prop and so overrides
// the init config's license_key; default the prop to the GPL key instead.
export default {
  ...Editor,
  props: {
    ...Editor.props,
    licenseKey: { type: String, default: 'gpl' },
  },
};
