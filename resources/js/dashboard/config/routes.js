import ErrorForbidden from '@/views/errors/Forbidden.vue';
import ErrorNotFound from '@/views/errors/NotFound.vue';

// Dashboard
import DashboardIndex from '@/views/dashboard/Index.vue';

// Home
import HomeIndex from '@/views/home/Index.vue';
import HomeCreate from '@/views/home/Create.vue';
import HomeEdit from '@/views/home/Edit.vue';

// Teams
import TeamIndex from '@/views/team/Index.vue';
import TeamCreate from '@/views/team/Create.vue';
import TeamEdit from '@/views/team/Edit.vue';

const routes = [

  // Dashboard
  {
    name: 'dashboard',
    path: '/administration',
    component: DashboardIndex,
  },

  // Home
  {
    name: 'home',
    path: '/administration/home',
    component: HomeIndex,
  },
  {
    name: 'home-create',
    path: '/administration/home/create',
    component: HomeCreate,
  },
  {
    name: 'home-edit',
    path: '/administration/home/edit/:id',
    component: HomeEdit,
  },

  // Teams
  {
    name: 'teams',
    path: '/administration/teams',
    component: TeamIndex,
  },
  {
    name: 'team-create',
    path: '/administration/team/create',
    component: TeamCreate,
  },
  {
    name: 'team-edit',
    path: '/administration/team/edit/:id',
    component: TeamEdit,
  },

  // Authorization
  {
    name: 'forbidden',
    path: '/forbidden',
    component: ErrorForbidden,
  },
  {
    name: 'not-found',
    path: '/not-found',
    component: ErrorNotFound,
  }
];

export default routes