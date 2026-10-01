import { Extension } from '@tiptap/vue-3';

const types = ['paragraph', 'heading'];

/**
 * "Kleine Schrift": class="fs-sm" on paragraphs and headings, as the
 * former TinyMCE style format set it.
 */
export const SmallText = Extension.create({
  name: 'smallText',

  addGlobalAttributes() {
    return [{
      types,
      attributes: {
        small: {
          default: false,
          parseHTML: element => element.classList.contains('fs-sm'),
          renderHTML: attributes => attributes.small ? { class: 'fs-sm' } : {},
        },
      },
    }];
  },

  addCommands() {
    return {
      toggleSmallText: () => ({ editor, commands }) => {
        const type = types.find(type => editor.isActive(type));
        return type ? commands.updateAttributes(type, { small: !editor.getAttributes(type).small }) : false;
      },
    };
  },
});

export function isSmallText(editor) {
  return types.some(type => editor.isActive(type, { small: true }));
}
