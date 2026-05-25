import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Lazy-loaded views
const LoginView = () => import('../views/LoginView.vue');
const AdminLayout = () => import('../layouts/AdminLayout.vue');
const AdminDashboard = () => import('../views/admin/DashboardView.vue');
const UserManagement = () => import('../views/admin/UserManagementView.vue');
const StaffDashboard = () => import('../views/staff/DashboardView.vue');
const ResidentListView = () => import('../views/residents/ResidentListView.vue');
const ResidentFormView = () => import('../views/residents/ResidentFormView.vue');
const ResidentProfileView = () => import('../views/residents/ResidentProfileView.vue');

// Documents
const RequestListView = () => import('../views/documents/RequestListView.vue');
const RequestFormView = () => import('../views/documents/RequestFormView.vue');
const PDFPreviewView = () => import('../views/documents/PDFPreviewView.vue');

// Blotters
const BlotterList = () => import('../views/blotters/BlotterList.vue');
const BlotterForm = () => import('../views/blotters/BlotterForm.vue');
const BlotterDetail = () => import('../views/blotters/BlotterDetail.vue');

// Reports
const ReportView = () => import('../views/reports/ReportView.vue');

// Announcements
const PublicFeed = () => import('../views/announcements/PublicFeed.vue');
const AdminAnnouncementList = () => import('../views/announcements/AdminAnnouncementList.vue');
const PostForm = () => import('../views/announcements/PostForm.vue');

// Workflow Automation Logs
const WorkflowLogs = () => import('../views/admin/WorkflowLogsView.vue');

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guest: true },
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      {
        path: 'dashboard',
        name: 'admin-dashboard',
        component: AdminDashboard,
      },
      {
        path: 'users',
        name: 'admin-users',
        component: UserManagement,
      },
      {
        path: 'residents',
        name: 'admin-residents',
        component: ResidentListView,
      },
      {
        path: 'residents/create',
        name: 'admin-residents-create',
        component: ResidentFormView,
      },
      {
        path: 'residents/:id',
        name: 'admin-residents-profile',
        component: ResidentProfileView,
      },
      {
        path: 'residents/:id/edit',
        name: 'admin-residents-edit',
        component: ResidentFormView,
      },
      {
        path: 'documents',
        name: 'admin-documents',
        component: RequestListView,
      },
      {
        path: 'documents/create',
        name: 'admin-documents-create',
        component: RequestFormView,
      },
      {
        path: 'documents/:id/preview',
        name: 'admin-documents-preview',
        component: PDFPreviewView,
      },
      {
        path: 'blotters',
        name: 'admin-blotters',
        component: BlotterList,
      },
      {
        path: 'blotters/create',
        name: 'admin-blotters-create',
        component: BlotterForm,
      },
      {
        path: 'blotters/:id',
        name: 'admin-blotters-detail',
        component: BlotterDetail,
      },
      {
        path: 'reports',
        name: 'admin-reports',
        component: ReportView,
      },
      {
        path: 'announcements',
        name: 'admin-announcements',
        component: AdminAnnouncementList,
      },
      {
        path: 'announcements/create',
        name: 'admin-announcements-create',
        component: PostForm,
      },
      {
        path: 'announcements/:id/edit',
        name: 'admin-announcements-edit',
        component: PostForm,
      },
      {
        path: 'workflow-logs',
        name: 'admin-workflow-logs',
        component: WorkflowLogs,
      },
    ],
  },
  {
    path: '/staff',
    component: AdminLayout,
    meta: { requiresAuth: true, role: 'staff' },
    children: [
      {
        path: 'dashboard',
        name: 'staff-dashboard',
        component: StaffDashboard,
      },
      {
        path: 'residents',
        name: 'staff-residents',
        component: ResidentListView,
      },
      {
        path: 'residents/create',
        name: 'staff-residents-create',
        component: ResidentFormView,
      },
      {
        path: 'residents/:id',
        name: 'staff-residents-profile',
        component: ResidentProfileView,
      },
      {
        path: 'residents/:id/edit',
        name: 'staff-residents-edit',
        component: ResidentFormView,
      },
      {
        path: 'documents',
        name: 'staff-documents',
        component: RequestListView,
      },
      {
        path: 'documents/create',
        name: 'staff-documents-create',
        component: RequestFormView,
      },
      {
        path: 'documents/:id/preview',
        name: 'staff-documents-preview',
        component: PDFPreviewView,
      },
      {
        path: 'blotters',
        name: 'staff-blotters',
        component: BlotterList,
      },
      {
        path: 'blotters/create',
        name: 'staff-blotters-create',
        component: BlotterForm,
      },
      {
        path: 'blotters/:id',
        name: 'staff-blotters-detail',
        component: BlotterDetail,
      },
      {
        path: 'announcements',
        name: 'staff-announcements',
        component: AdminAnnouncementList,
      },
      {
        path: 'announcements/create',
        name: 'staff-announcements-create',
        component: PostForm,
      },
      {
        path: 'announcements/:id/edit',
        name: 'staff-announcements-edit',
        component: PostForm,
      },
    ],
  },

  {
    path: '/public-feed',
    name: 'public-feed',
    component: PublicFeed,
  },
  {
    path: '/',
    redirect: '/login',
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/login',
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  // If route requires auth and user is not authenticated
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login' });
  }

  // If route is guest-only (login) and user is already authenticated
  if (to.meta.guest && authStore.isAuthenticated) {
    if (authStore.role === 'admin') {
      return next({ name: 'admin-dashboard' });
    }
    return next({ name: 'staff-dashboard' });
  }

  // If route requires a specific role
  if (to.meta.role && authStore.role !== to.meta.role) {
    if (authStore.role === 'admin') {
      return next({ name: 'admin-dashboard' });
    }
    return next({ name: 'staff-dashboard' });
  }

  next();
});

export default router;
