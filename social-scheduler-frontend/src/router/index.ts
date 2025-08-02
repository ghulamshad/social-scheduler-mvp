// 📁 src/router/index.ts
import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/store/auth';
import Login from '@/pages/Login.vue';
import Dashboard from '@/pages/Dashboard.vue';

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name: 'Login', component: Login },
  { 
    path: '/dashboard', 
    name: 'Dashboard', 
    component: Dashboard,
    meta: { requiresAuth: true }
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guard
router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore();
  
  // Check if route requires authentication
  if (to.meta.requiresAuth) {
    // Check if user is authenticated
    if (!auth.user) {
      await auth.checkAuth();
    }
    
    if (!auth.user) {
      next('/login');
      return;
    }
  }
  
  // If user is authenticated and trying to access login, redirect to dashboard
  if (to.path === '/login' && auth.user) {
    next('/dashboard');
    return;
  }
  
  next();
});

export default router;


