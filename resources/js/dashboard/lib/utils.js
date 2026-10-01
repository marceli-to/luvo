export function capitalizeFirst(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

export function confirmDelete() {
  return window.confirm('Bitte löschen bestätigen!');
}

/**
 * Items grouped by the value of key, keeping the item objects (so changes
 * to an item show in every list that holds it).
 */
export function groupBy(items, key) {
  return items.reduce((groups, item) => {
    (groups[item[key]] ??= []).push(item);
    return groups;
  }, {});
}

/**
 * Sets each item's order to its index and returns the list.
 */
export function withOrder(items) {
  items.forEach((item, index) => item.order = index);
  return items;
}
