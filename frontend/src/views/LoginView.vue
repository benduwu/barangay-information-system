<template>
  <div class="login-wrapper">
    <div class="login-card">
      <!-- Logo -->
      <div class="login-logo">
        <i class="bi bi-shield-lock-fill"></i>
      </div>
      <h1 class="login-title">Barangay Info System</h1>
      <p class="login-subtitle">Sign in to your account to continue</p>

      <!-- Error Alert -->
      <div v-if="errorMessage" class="alert alert-danger d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span>{{ errorMessage }}</span>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" id="login-form">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <div class="input-group">
            <span class="input-group-text bg-white">
              <i class="bi bi-person text-muted"></i>
            </span>
            <input
              type="text"
              class="form-control"
              id="username"
              v-model="form.username"
              placeholder="Enter your username"
              required
              autocomplete="username"
            />
          </div>
        </div>

        <div class="mb-4">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text bg-white">
              <i class="bi bi-lock text-muted"></i>
            </span>
            <input
              :type="showPassword ? 'text' : 'password'"
              class="form-control"
              id="password"
              v-model="form.password"
              placeholder="Enter your password"
              required
              autocomplete="current-password"
            />
            <button
              type="button"
              class="input-group-text bg-white btn-toggle-password"
              @click="showPassword = !showPassword"
              tabindex="-1"
            >
              <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
            </button>
          </div>
        </div>

        <button
          type="submit"
          class="btn btn-primary w-100 py-2"
          id="login-submit"
          :disabled="isLoading"
        >
          <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
          {{ isLoading ? 'Signing in...' : 'Sign In' }}
        </button>
      </form>

      <p class="text-center mt-3 mb-0" style="font-size: 0.75rem; color: #94a3b8;">
        Barangay Information System &copy; {{ new Date().getFullYear() }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();

const form = reactive({
  username: '',
  password: '',
});

const isLoading = ref(false);
const showPassword = ref(false);
const errorMessage = ref('');

async function handleLogin() {
  isLoading.value = true;
  errorMessage.value = '';

  try {
    await authStore.login({
      username: form.username,
      password: form.password,
    });
  } catch (error) {
    const status = error.response?.status;
    if (status === 401) {
      errorMessage.value = 'Invalid username or password.';
    } else if (status === 403) {
      errorMessage.value = error.response?.data?.message || 'Account deactivated.';
    } else if (status === 422) {
      const errors = error.response?.data?.errors;
      errorMessage.value = errors
        ? Object.values(errors).flat().join(' ')
        : 'Validation error.';
    } else {
      errorMessage.value = 'Unable to connect to the server. Please try again.';
    }
  } finally {
    isLoading.value = false;
  }
}
</script>

<style scoped>
.btn-toggle-password {
  cursor: pointer;
  border-left: none;
}
.btn-toggle-password:hover {
  color: var(--bs-primary);
}
.input-group-text {
  border-color: var(--bs-border-color);
}
</style>
