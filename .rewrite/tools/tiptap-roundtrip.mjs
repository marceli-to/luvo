// Round-trips every stored rich-text value through Tiptap with luvo's editor
// config and compares what a browser would show before and after.
import fs from 'node:fs';
import { Window } from 'happy-dom';
import { generateJSON, generateHTML } from '@tiptap/html';
import { Extension } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';

// --- same as resources/js/dashboard/components/ui/editor ---
const types = ['paragraph', 'heading'];
const SmallText = Extension.create({
  name: 'smallText',
  addGlobalAttributes() {
    return [{ types, attributes: { small: {
      default: false,
      parseHTML: el => el.classList.contains('fs-sm'),
      renderHTML: a => a.small ? { class: 'fs-sm' } : {},
    } } }];
  },
});
const extensions = [
  StarterKit.configure({
    heading: { levels: [1, 2, 3] },
    blockquote: false, code: false, codeBlock: false, horizontalRule: false,
    strike: false, underline: false, link: false,
  }),
  Link.configure({ openOnClick: false, autolink: false, HTMLAttributes: { target: null, rel: null } }),
  SmallText,
];
// ------------------------------------------------------------

const window = new Window();
const parse = html => { const d = window.document.createElement('div'); d.innerHTML = html; return d; };

function facts(html) {
  const root = parse(html);
  root.querySelectorAll('style, xml, script, title, meta, link').forEach(e => e.remove());
  // comments (Word's conditional blocks) render nothing
  const walker = window.document.createTreeWalker(root, 128);
  const comments = []; while (walker.nextNode()) comments.push(walker.currentNode);
  comments.forEach(c => c.remove());
  const linksCount = root.querySelectorAll('a[href]').length;
  // a line break or block boundary reads as whitespace, wherever newlines sit in the source
  root.querySelectorAll('br').forEach(br => br.replaceWith(window.document.createTextNode(' ')));
  root.querySelectorAll('p, li, h1, h2, h3, h4, div, ul, ol, td, tr').forEach(el => el.append(window.document.createTextNode(' ')));
  const text = (root.textContent || '').replace(/ /g, ' ').replace(/\s+/g, ' ').trim();
  return {
    text,
    links: [...root.querySelectorAll('a[href]')].map(a => a.getAttribute('href')),
    h: ['h1', 'h2', 'h3', 'h4'].map(h => root.querySelectorAll(h).length).join('/'),
    small: root.querySelectorAll('.fs-sm').length,
    li: root.querySelectorAll('li').length,
    bold: root.querySelectorAll('strong, b').length,
    sup: root.querySelectorAll('sup').length,
    tables: root.querySelectorAll('table').length,
    imgs: root.querySelectorAll('img').length,
  };
}

const items = JSON.parse(fs.readFileSync(new URL('./content.json', import.meta.url)));
const problems = [];
let bytesBefore = 0, bytesAfter = 0;

for (const { key, html } of items) {
  const out = generateHTML(generateJSON(html, extensions), extensions);
  bytesBefore += html.length; bytesAfter += out.length;
  const a = facts(html), b = facts(out);
  const diffs = [];
  if (a.text !== b.text) diffs.push(`text (${a.text.length}→${b.text.length})`);
  if (JSON.stringify(a.links) !== JSON.stringify(b.links)) diffs.push(`links ${JSON.stringify(a.links)} → ${JSON.stringify(b.links)}`);
  for (const k of ['h', 'small', 'li', 'bold', 'sup', 'tables', 'imgs']) if (a[k] !== b[k]) diffs.push(`${k} ${a[k]}→${b[k]}`);
  if (diffs.length) problems.push({ key, diffs, a, b });
}

console.log(`${items.length} values, ${problems.length} with visible differences; ${bytesBefore} → ${bytesAfter} bytes`);
for (const p of problems) {
  console.log(`\n== ${p.key}: ${p.diffs.join('; ')}`);
  if (p.a.text !== p.b.text) {
    let i = 0; while (i < p.a.text.length && p.a.text[i] === p.b.text[i]) i++;
    console.log('   before: …' + p.a.text.slice(Math.max(0, i - 40), i + 80));
    console.log('   after : …' + p.b.text.slice(Math.max(0, i - 40), i + 80));
  }
}
