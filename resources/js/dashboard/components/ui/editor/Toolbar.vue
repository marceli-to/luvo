<template>
  <div class="editor__toolbar">
    <button
      v-for="action in actions"
      :key="action.title"
      type="button"
      :class="['editor__button', { 'is-active': action.active?.() }]"
      :title="action.title"
      @click="action.run()"
    >
      <component :is="action.icon" size="16" />
    </button>
    <LinkDialog ref="linkDialog" :editor="editor" />
  </div>
</template>
<script setup>
import { ref } from 'vue';
import {
  Heading1Icon, Heading2Icon, Heading3Icon, BoldIcon, ListIcon, ListOrderedIcon,
  ALargeSmallIcon, LinkIcon, RemoveFormattingIcon,
} from 'lucide-vue-next';
import LinkDialog from './LinkDialog.vue';
import { isSmallText } from './smallText';

const props = defineProps({
  editor: { type: Object, required: true },
});

const linkDialog = ref(null);
const chain = () => props.editor.chain().focus();

const heading = (level, icon) => ({
  title: `Überschrift ${level}`,
  icon,
  active: () => props.editor.isActive('heading', { level }),
  run: () => chain().toggleHeading({ level }).run(),
});

const actions = [
  heading(1, Heading1Icon),
  heading(2, Heading2Icon),
  heading(3, Heading3Icon),
  {
    title: 'Fett',
    icon: BoldIcon,
    active: () => props.editor.isActive('bold'),
    run: () => chain().toggleBold().run(),
  },
  {
    title: 'Liste',
    icon: ListIcon,
    active: () => props.editor.isActive('bulletList'),
    run: () => chain().toggleBulletList().run(),
  },
  {
    title: 'Nummerierte Liste',
    icon: ListOrderedIcon,
    active: () => props.editor.isActive('orderedList'),
    run: () => chain().toggleOrderedList().run(),
  },
  {
    title: 'Kleine Schrift',
    icon: ALargeSmallIcon,
    active: () => isSmallText(props.editor),
    run: () => chain().toggleSmallText().run(),
  },
  {
    title: 'Link',
    icon: LinkIcon,
    active: () => props.editor.isActive('link'),
    run: () => linkDialog.value.open(),
  },
  {
    title: 'Formatierung entfernen',
    icon: RemoveFormattingIcon,
    run: () => chain().unsetAllMarks().clearNodes().run(),
  },
];
</script>
