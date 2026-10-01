// Former Vue 2 filters; use as $filters.truncate(...) in templates
export default {
  truncate(text, length, suffix) {
    let t = text.replace(/(<([^>]+)>)/ig,"");
    if (t.length > length) {
      return t.substring(0, length) + suffix;
    }
    else {
      return t;
    }
  },

  capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
  },
};
