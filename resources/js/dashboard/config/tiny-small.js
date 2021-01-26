export default {
  skin_url: '/assets/dashboard/js/tinymce/skins/custom',
  branding: false,
  menubar: false,
  statusbar: false,
  external_plugins: {
    link: '/assets/dashboard/js/tinymce/plugins/link/plugin.min.js',
  },
  plugins: ["lists"],
  toolbar: 'undo redo | bold | bullist | link | superscript | removeformat | styleselect',
  paste_as_text: true,
  height: "160px",
  style_formats_merge: false,
  style_formats: [{
    title: 'Text',
    items: [
      { title: 'Worttrennung deaktivieren', inline: 'span', styles: { "white-space": 'nowrap' } },
      { title: 'Überschrift 1', block : 'h1'},
      { title: 'Überschrift 2', block : 'h2'},
      { title: 'Überschrift 3', block : 'h3'},
    ],
  }]
};