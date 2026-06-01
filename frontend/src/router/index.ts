import { createRouter, createWebHistory } from 'vue-router';
import DefaultLayout from '@/layouts/DefaultLayout.vue';
import LoginView from '@/views/LoginView.vue';
import DashboardView from '@/views/DashboardView.vue';
import SectorList from '@/views/sectors/SectorList.vue';
import UserList from '@/views/users/UserList.vue';
import FamilyList from '@/views/families/FamilyList.vue';
import FamilyDetail from '@/views/families/FamilyDetail.vue';
import FamilyForm from '@/views/families/FamilyForm.vue';
import MemberList from '@/views/members/MemberList.vue';
import OfferingList from '@/views/offerings/OfferingList.vue';
import OfferingReport from '@/views/offerings/OfferingReport.vue';
import SintuaList from '@/views/sintua/SintuaList.vue';
import AttendanceView from '@/views/attendance/AttendanceView.vue';
import { useAuthStore } from '@/stores/auth';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', name: 'login', component: LoginView, meta: { public: true } },
    {
      path: '/',
      component: DefaultLayout,
      children: [
        { path: '', redirect: '/dashboard' },
        { path: 'dashboard', name: 'dashboard', component: DashboardView },
        { path: 'sectors', name: 'sectors', component: SectorList, meta: { roles: ['admin'] } },
        { path: 'users', name: 'users', component: UserList, meta: { roles: ['admin'] } },
        { path: 'families', name: 'families', component: FamilyList },
        { path: 'families/new', name: 'family-new', component: FamilyForm },
        { path: 'families/:id', name: 'family-detail', component: FamilyDetail, props: true },
        { path: 'members', name: 'members', component: MemberList },
        { path: 'offerings', name: 'offerings', component: OfferingList },
        { path: 'offerings/report', name: 'offering-report', component: OfferingReport },
        { path: 'sintua', name: 'sintua', component: SintuaList },
        { path: 'attendance', name: 'attendance', component: AttendanceView }
      ]
    }
  ]
});

router.beforeEach(async (to) => {
  const auth = useAuthStore();
  if (to.meta.public) {
    return auth.isAuthenticated && to.path === '/login' ? '/dashboard' : true;
  }
  if (!auth.isAuthenticated) {
    return { path: '/login', query: { redirect: to.fullPath } };
  }
  if (!auth.currentUser) {
    try {
      await auth.loadMe();
    } catch {
      auth.logout();
      return { path: '/login', query: { redirect: to.fullPath } };
    }
  }
  const roles = to.meta.roles as string[] | undefined;
  if (roles?.length && !roles.includes(auth.currentUser?.role_name ?? '')) {
    return '/dashboard';
  }
  return true;
});

export default router;
