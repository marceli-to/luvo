export default {
  license_key: 'gpl',
  skin_url: '/assets/dashboard/js/_tinymce/skins/custom',
  branding: false,
  menubar: false,
  statusbar: false,
  // external_plugins: {
  //   link: '/assets/dashboard/js/tinymce/plugins/link/plugin.min.js',
  // },
  plugins: ['lists', 'code', 'link'],
  toolbar: 'undo redo | bold | bullist | link | superscript | removeformat | styles | code',
  paste_as_text: true,
  height: "320px",
  style_formats_merge: false,
  style_formats: [{
    title: 'Text',
    items: [
      { title: 'Worttrennung deaktivieren', inline: 'span', styles: { "white-space": 'nowrap' } },
      { title: 'Kleine Schrift', block: 'p', classes: 'fs-sm' },
      { title: 'Überschrift 1', block : 'h1'},
      { title: 'Überschrift 2', block : 'h2'},
      { title: 'Überschrift 3', block : 'h3'},
    ],
  }],

  link_list: '/filelist',

};
