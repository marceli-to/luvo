import { Mark } from '@tiptap/vue-3';

/**
 * "Worttrennung deaktivieren": keeps words on one line, as the former
 * TinyMCE style format did (<span style="white-space: nowrap;">).
 */
export const NoWrap = Mark.create({
  name: 'noWrap',

  parseHTML() {
    return [{
      tag: 'span',
      getAttrs: element => element.style.whiteSpace === 'nowrap' && null,
    }];
  },

  renderHTML() {
    return ['span', { style: 'white-space: nowrap;' }, 0];
  },

  addCommands() {
    return {
      toggleNoWrap: () => ({ commands }) => commands.toggleMark(this.name),
    };
  },
});
