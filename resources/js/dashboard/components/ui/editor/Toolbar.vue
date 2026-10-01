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
      <component :is="action.icon" :size="16" weight="light" />
    </button>
    <LinkDialog ref="linkDialog" :editor="editor" />
  </div>
</template>
<script setup>
import { ref } from 'vue';
import {
  PhTextHOne, PhTextHTwo, PhTextHThree, PhTextB, PhListBullets, PhListNumbers,
  PhTextAa, PhLink, PhEraser,
} from '@phosphor-icons/vue';
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
  heading(1, PhTextHOne),
  heading(2, PhTextHTwo),
  heading(3, PhTextHThree),
  {
    title: 'Fett',
    icon: PhTextB,
    active: () => props.editor.isActive('bold'),
    run: () => chain().toggleBold().run(),
  },
  {
    title: 'Liste',
    icon: PhListBullets,
    active: () => props.editor.isActive('bulletList'),
    run: () => chain().toggleBulletList().run(),
  },
  {
    title: 'Nummerierte Liste',
    icon: PhListNumbers,
    active: () => props.editor.isActive('orderedList'),
    run: () => chain().toggleOrderedList().run(),
  },
  {
    title: 'Kleine Schrift',
    icon: PhTextAa,
    active: () => isSmallText(props.editor),
    run: () => chain().toggleSmallText().run(),
  },
  {
    title: 'Link',
    icon: PhLink,
    active: () => props.editor.isActive('link'),
    run: () => linkDialog.value.open(),
  },
  {
    title: 'Formatierung entfernen',
    icon: PhEraser,
    run: () => chain().unsetAllMarks().clearNodes().run(),
  },
];
</script>
