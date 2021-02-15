Vue.filter('truncate', function (text, length, suffix) {
  let t = text.replace(/(<([^>]+)>)/ig,"");
  if (t.length > length) {
    return t.substring(0, length) + suffix;
  }
  else {
    return t;
  }
});

Vue.filter('capitalizeFirst', function (str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
});