import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { root } from './env';

export const locales = ['de', 'fr', 'en'] as const;
export type Locale = typeof locales[number];

const contact = { de: 'kontakt', fr: 'contacter', en: 'contact' };
const assistant = { de: 'assistenz', fr: 'assistence', en: 'assistenz' };

export type Member = { id: number; fullname: string; publish: number; order: number; team: 'luks' | 'vogt'; team_id: number; path: string };

/** Team members in luvo_e2e, ordered as in the admin, with their public path. */
export function members(): Member[] {
  return JSON.parse(execFileSync('php', [path.join(root, 'tests/e2e/support/db.php'), 'members'], { encoding: 'utf8' }));
}

export type PublicPage = { name: string; type: 'home' | 'team' | 'member' | 'assistant' | 'contact'; locale: Locale; url: string };

/** Every public page in every locale (published members only). */
export function publicPages(): PublicPage[] {
  const published = members().filter(m => m.publish);
  return locales.flatMap(locale => [
    { name: `home ${locale}`, type: 'home', locale, url: `/${locale}/home` },
    { name: `team luks ${locale}`, type: 'team', locale, url: `/${locale}/team-luks` },
    { name: `team vogt ${locale}`, type: 'team', locale, url: `/${locale}/team-vogt` },
    ...published.map(m => ({ name: `member ${m.fullname} ${locale}`, type: 'member', locale, url: `/${locale}/${m.path}` } as PublicPage)),
    { name: `assistant luks ${locale}`, type: 'assistant', locale, url: `/${locale}/team-luks/${assistant[locale]}` },
    { name: `assistant vogt ${locale}`, type: 'assistant', locale, url: `/${locale}/team-vogt/${assistant[locale]}` },
    { name: `contact ${locale}`, type: 'contact', locale, url: `/${locale}/${contact[locale]}` },
  ] as PublicPage[]);
}

export const contactPath = (locale: Locale) => `/${locale}/${contact[locale]}`;
export const assistantPath = (locale: Locale, team: string) => `/${locale}/team-${team}/${assistant[locale]}`;
