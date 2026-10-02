import { Node } from '@tiptap/vue-3';

/**
 * <div> lines as TinyMCE kept them (e.g. the Impressum's image credits):
 * a block without paragraph spacing. Without it Tiptap turns them into <p>,
 * which the site spaces apart. Nested divs are flattened, which looks the same.
 */
export const Div = Node.create({
  name: 'div',
  group: 'block',
  content: 'inline*',

  parseHTML() {
    return [{ tag: 'div' }];
  },

  renderHTML() {
    return ['div', 0];
  },
});
