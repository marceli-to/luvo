import { createRouter, createWebHistory } from 'vue-router';

// Forms get type 'create' or 'edit' as a prop
const create = { type: 'create' };
const edit = { type: 'edit' };

const routes = [
  { name: 'dashboard', path: '/administration', component: () => import('@/views/dashboard/Index.vue') },

  // Home
  { name: 'home', path: '/administration/home', component: () => import('@/views/home/Index.vue') },
  { name: 'home-create', path: '/administration/home/create', component: () => import('@/views/home/Form.vue'), props: create },
  { name: 'home-edit', path: '/administration/home/edit/:id', component: () => import('@/views/home/Form.vue'), props: edit },

  // Contact
  { name: 'contact', path: '/administration/contact', component: () => import('@/views/contact/Index.vue') },
  { name: 'contact-create', path: '/administration/contact/create', component: () => import('@/views/contact/Form.vue'), props: create },
  { name: 'contact-edit', path: '/administration/contact/edit/:id', component: () => import('@/views/contact/Form.vue'), props: edit },

  // Teams, team members, assistants
  { name: 'teams', path: '/administration/teams', component: () => import('@/views/team/Index.vue') },
  { name: 'team-create', path: '/administration/team/create', component: () => import('@/views/team/Form.vue'), props: create },
  { name: 'team-edit', path: '/administration/team/edit/:id', component: () => import('@/views/team/Form.vue'), props: edit },
  { name: 'team-member-create', path: '/administration/team/member/create', component: () => import('@/views/team_member/Form.vue'), props: create },
  { name: 'team-member-edit', path: '/administration/team/member/edit/:id', component: () => import('@/views/team_member/Form.vue'), props: edit },
  { name: 'assistant-create', path: '/administration/assistant/create', component: () => import('@/views/assistant/Form.vue'), props: create },
  { name: 'assistant-edit', path: '/administration/assistant/edit/:id', component: () => import('@/views/assistant/Form.vue'), props: edit },

  // Publications
  { name: 'publication-create', path: '/administration/team/publication/create/:memberId', component: () => import('@/views/publication/Form.vue'), props: create },
  { name: 'publication-edit', path: '/administration/team/publication/edit/:id', component: () => import('@/views/publication/Form.vue'), props: edit },

  // Files
  { name: 'media', path: '/administration/media', component: () => import('@/views/media/Index.vue') },

  // User
  { name: 'user-edit', path: '/administration/user/edit/:id', component: () => import('@/views/user/Form.vue') },

  // Errors
  { name: 'forbidden', path: '/forbidden', component: () => import('@/views/errors/Forbidden.vue') },
  { name: 'not-found', path: '/not-found', component: () => import('@/views/errors/NotFound.vue') },
];

export default createRouter({ history: createWebHistory(), routes });
