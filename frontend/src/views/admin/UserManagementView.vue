<template>
  <div>
    <!-- Header Row -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h4 class="mb-1" style="font-weight: 700;">Users</h4>
        <p class="mb-0 text-muted" style="font-size: 0.8125rem;">
          Manage system accounts and role assignments
        </p>
      </div>
      <button class="btn btn-primary" @click="openCreateModal" id="add-user-btn">
        <i class="bi bi-plus-lg me-1"></i> Add User
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body py-3">
        <div class="row g-3 align-items-end">
          <div class="col-md-5">
            <label class="form-label">Search</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
              <input
                type="text"
                class="form-control"
                placeholder="Search by name, username, or email..."
                v-model="filters.search"
                @input="debouncedFetch"
                id="user-search"
              />
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Role</label>
            <select class="form-select" v-model="filters.role" @change="fetchUsers" id="filter-role">
              <option value="">All Roles</option>
              <option value="admin">Admin</option>
              <option value="staff">Staff</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select class="form-select" v-model="filters.is_active" @change="fetchUsers" id="filter-status">
              <option value="">All Status</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
          <div class="col-md-1">
            <button class="btn btn-outline-secondary w-100" @click="resetFilters" title="Reset Filters">
              <i class="bi bi-arrow-counterclockwise"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Success / Error Messages -->
    <transition name="fade">
      <div v-if="successMessage" class="alert alert-success d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i>{{ successMessage }}
        <button type="button" class="btn-close ms-auto" @click="successMessage = ''" style="font-size: 0.625rem;"></button>
      </div>
    </transition>

    <!-- Users Table -->
    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0" id="users-table">
            <thead>
              <tr>
                <th>User</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Last Login</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoading">
                <td colspan="6" class="text-center py-4">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                  Loading users...
                </td>
              </tr>
              <tr v-else-if="users.length === 0">
                <td colspan="6" class="text-center py-4 text-muted">
                  <i class="bi bi-person-x" style="font-size: 2rem;"></i>
                  <p class="mt-2 mb-0">No users found.</p>
                </td>
              </tr>
              <tr v-for="user in users" :key="user.id">
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="topbar-avatar" style="width:34px; height:34px; font-size: 0.75rem;">
                      {{ getInitials(user.full_name) }}
                    </div>
                    <div>
                      <div style="font-weight: 500;">{{ user.full_name }}</div>
                      <div style="font-size: 0.75rem; color: #94a3b8;">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <code style="font-size: 0.8125rem;">{{ user.username }}</code>
                </td>
                <td>
                  <span class="badge" :class="user.role === 'admin' ? 'badge-admin' : 'badge-staff'">
                    {{ user.role }}
                  </span>
                </td>
                <td>
                  <span class="badge" :class="user.is_active ? 'badge-active' : 'badge-inactive'">
                    {{ user.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td style="font-size: 0.8125rem; color: #64748b;">
                  {{ user.last_login ? formatDate(user.last_login) : 'Never' }}
                </td>
                <td class="text-end">
                  <div class="d-flex gap-1 justify-content-end">
                    <button
                      class="btn btn-ghost btn-sm"
                      title="Edit"
                      @click="openEditModal(user)"
                    >
                      <i class="bi bi-pencil-square text-primary"></i>
                    </button>
                    <button
                      class="btn btn-ghost btn-sm"
                      title="Reset Password"
                      @click="confirmResetPassword(user)"
                    >
                      <i class="bi bi-key text-warning"></i>
                    </button>
                    <button
                      class="btn btn-ghost btn-sm"
                      title="Deactivate"
                      @click="confirmDeactivate(user)"
                      :disabled="user.id === authStore.user?.id"
                    >
                      <i class="bi bi-trash text-danger"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="card-footer bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 py-3" v-if="meta.last_page > 1">
        <div style="font-size: 0.8125rem; color: #64748b;">
          Showing {{ (meta.current_page - 1) * meta.per_page + 1 }} to
          {{ Math.min(meta.current_page * meta.per_page, meta.total) }}
          of {{ meta.total }} users
        </div>
        <nav>
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
              <button class="page-link" @click="goToPage(meta.current_page - 1)">
                <i class="bi bi-chevron-left"></i>
              </button>
            </li>
            <li
              v-for="page in visiblePages"
              :key="page"
              class="page-item"
              :class="{ active: page === meta.current_page }"
            >
              <button class="page-link" @click="goToPage(page)">{{ page }}</button>
            </li>
            <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
              <button class="page-link" @click="goToPage(meta.current_page + 1)">
                <i class="bi bi-chevron-right"></i>
              </button>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- User Form Modal -->
    <UserFormModal
      :show="showModal"
      :mode="modalMode"
      :user="selectedUser"
      @close="showModal = false"
      @saved="onUserSaved"
    />

    <!-- Confirm Deactivate Modal -->
    <div class="modal fade" id="deactivateModal" tabindex="-1" ref="deactivateModalRef">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title text-danger">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>Deactivate User
            </h6>
          </div>
          <div class="modal-body" style="font-size: 0.875rem;">
            Are you sure you want to deactivate <strong>{{ selectedUser?.full_name }}</strong>?
            They will lose access to the system.
          </div>
          <div class="modal-footer border-0 pt-0">
            <button class="btn btn-sm btn-outline-secondary" @click="closeDeactivateModal">Cancel</button>
            <button class="btn btn-sm btn-danger" @click="deactivateUser" :disabled="isProcessing">
              <span v-if="isProcessing" class="spinner-border spinner-border-sm me-1"></span>
              Deactivate
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" ref="resetPasswordModalRef">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title text-warning">
              <i class="bi bi-key-fill me-2"></i>Reset Password
            </h6>
          </div>
          <div class="modal-body" style="font-size: 0.875rem;">
            <p class="mb-3">Reset password for <strong>{{ selectedUser?.full_name }}</strong>?</p>
            <div class="mb-0">
              <label class="form-label">New Password</label>
              <input
                type="text"
                class="form-control form-control-sm"
                v-model="newPassword"
                placeholder="Leave blank to auto-generate"
              />
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button class="btn btn-sm btn-outline-secondary" @click="closeResetModal">Cancel</button>
            <button class="btn btn-sm btn-warning" @click="resetPassword" :disabled="isProcessing">
              <span v-if="isProcessing" class="spinner-border spinner-border-sm me-1"></span>
              Reset
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue';
import { Modal } from 'bootstrap';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';
import UserFormModal from '../../components/UserFormModal.vue';

const authStore = useAuthStore();

// --- State ---
const users = ref([]);
const isLoading = ref(false);
const isProcessing = ref(false);
const successMessage = ref('');
const showModal = ref(false);
const modalMode = ref('create');
const selectedUser = ref(null);
const newPassword = ref('');

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const filters = reactive({
  search: '',
  role: '',
  is_active: '',
});

let debounceTimer = null;

// --- Computed ---
const visiblePages = computed(() => {
  const pages = [];
  const start = Math.max(1, meta.current_page - 2);
  const end = Math.min(meta.last_page, meta.current_page + 2);
  for (let i = start; i <= end; i++) pages.push(i);
  return pages;
});

// --- Methods ---
function getInitials(name) {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
}

function formatDate(dateStr) {
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function debouncedFetch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => fetchUsers(), 300);
}

async function fetchUsers(page = 1) {
  isLoading.value = true;
  try {
    const params = { page, per_page: meta.per_page };
    if (filters.search) params.search = filters.search;
    if (filters.role) params.role = filters.role;
    if (filters.is_active !== '') params.is_active = filters.is_active;

    const response = await api.get('/users', { params });
    users.value = response.data.users;
    Object.assign(meta, response.data.meta);
  } catch (e) {
    console.error('Failed to fetch users:', e);
  } finally {
    isLoading.value = false;
  }
}

function goToPage(page) {
  if (page < 1 || page > meta.last_page) return;
  fetchUsers(page);
}

function resetFilters() {
  filters.search = '';
  filters.role = '';
  filters.is_active = '';
  fetchUsers();
}

function openCreateModal() {
  modalMode.value = 'create';
  selectedUser.value = null;
  showModal.value = true;
}

function openEditModal(user) {
  modalMode.value = 'edit';
  selectedUser.value = { ...user };
  showModal.value = true;
}

function onUserSaved(message) {
  showModal.value = false;
  successMessage.value = message;
  fetchUsers(meta.current_page);
  setTimeout(() => (successMessage.value = ''), 4000);
}

// Deactivate modal
const deactivateModalRef = ref(null);
let deactivateModalInstance = null;

function confirmDeactivate(user) {
  selectedUser.value = user;
  deactivateModalInstance = new Modal(deactivateModalRef.value);
  deactivateModalInstance.show();
}

function closeDeactivateModal() {
  deactivateModalInstance?.hide();
}

async function deactivateUser() {
  isProcessing.value = true;
  try {
    await api.delete(`/users/${selectedUser.value.id}`);
    closeDeactivateModal();
    successMessage.value = `${selectedUser.value.full_name} has been deactivated.`;
    fetchUsers(meta.current_page);
    setTimeout(() => (successMessage.value = ''), 4000);
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to deactivate user.');
  } finally {
    isProcessing.value = false;
  }
}

// Reset password modal
const resetPasswordModalRef = ref(null);
let resetPasswordModalInstance = null;

function confirmResetPassword(user) {
  selectedUser.value = user;
  newPassword.value = '';
  resetPasswordModalInstance = new Modal(resetPasswordModalRef.value);
  resetPasswordModalInstance.show();
}

function closeResetModal() {
  resetPasswordModalInstance?.hide();
}

async function resetPassword() {
  isProcessing.value = true;
  try {
    const payload = {};
    if (newPassword.value) payload.password = newPassword.value;

    const response = await api.post(`/users/${selectedUser.value.id}/reset-password`, payload);
    closeResetModal();

    let msg = `Password reset for ${selectedUser.value.full_name}.`;
    if (response.data.new_password) {
      msg += ` New password: ${response.data.new_password}`;
    }
    successMessage.value = msg;
    setTimeout(() => (successMessage.value = ''), 8000);
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to reset password.');
  } finally {
    isProcessing.value = false;
  }
}

// --- Lifecycle ---
onMounted(() => {
  fetchUsers();
});
</script>
