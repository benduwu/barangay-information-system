import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../services/api';
import router from '../router';

export const useAuthStore = defineStore('auth', () => {
  // --- State ---
  const token = ref(localStorage.getItem('token') || null);
  const user = ref(JSON.parse(localStorage.getItem('user') || 'null'));

  // --- Getters ---
  const isAuthenticated = computed(() => !!token.value);
  const role = computed(() => user.value?.role || null);
  const fullName = computed(() => user.value?.full_name || '');
  const initials = computed(() => {
    if (!user.value?.full_name) return '?';
    return user.value.full_name
      .split(' ')
      .map((n) => n[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  });

  // --- Actions ---
  async function login(credentials) {
    const response = await api.post('/login', credentials);
    const data = response.data;

    token.value = data.token;
    user.value = data.user;

    localStorage.setItem('token', data.token);
    localStorage.setItem('user', JSON.stringify(data.user));

    // Redirect based on role
    if (data.user.role === 'admin') {
      router.push({ name: 'admin-dashboard' });
    } else {
      router.push({ name: 'staff-dashboard' });
    }

    return data;
  }

  async function logout() {
    try {
      await api.post('/logout');
    } catch (e) {
      // Token may already be invalid — proceed with cleanup
    } finally {
      clearAuth();
      router.push({ name: 'login' });
    }
  }

  function clearAuth() {
    token.value = null;
    user.value = null;
    localStorage.removeItem('token');
    localStorage.removeItem('user');
  }

  async function fetchUser() {
    try {
      const response = await api.get('/me');
      user.value = response.data.user;
      localStorage.setItem('user', JSON.stringify(response.data.user));
    } catch (e) {
      clearAuth();
    }
  }

  return {
    token,
    user,
    isAuthenticated,
    role,
    fullName,
    initials,
    login,
    logout,
    clearAuth,
    fetchUser,
  };
});
