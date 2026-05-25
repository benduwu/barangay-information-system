<template>
  <teleport to="body">
    <div
      v-if="show"
      class="modal fade show d-block"
      tabindex="-1"
      @click.self="$emit('close')"
      style="background: rgba(0,0,0,0.5);"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" style="font-weight: 600;">
              <i class="bi me-2" :class="mode === 'create' ? 'bi-person-plus-fill' : 'bi-pencil-square'"></i>
              {{ mode === 'create' ? 'Add New User' : 'Edit User' }}
            </h5>
            <button type="button" class="btn-close" @click="$emit('close')"></button>
          </div>

          <form @submit.prevent="handleSubmit" id="user-form">
            <div class="modal-body">
              <!-- Error Alert -->
              <div v-if="errorMessage" class="alert alert-danger" style="font-size: 0.8125rem;">
                <i class="bi bi-exclamation-circle me-1"></i>{{ errorMessage }}
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label for="form-fullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    class="form-control"
                    id="form-fullname"
                    v-model="form.full_name"
                    placeholder="e.g. Juan Dela Cruz"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label for="form-username" class="form-label">Username <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    class="form-control"
                    id="form-username"
                    v-model="form.username"
                    placeholder="e.g. jdelacruz"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label for="form-email" class="form-label">Email <span class="text-danger">*</span></label>
                  <input
                    type="email"
                    class="form-control"
                    id="form-email"
                    v-model="form.email"
                    placeholder="e.g. juan@email.com"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label for="form-role" class="form-label">Role <span class="text-danger">*</span></label>
                  <RoleSelect v-model="form.role" id="form-role" required />
                </div>
                <div class="col-12">
                  <label for="form-password" class="form-label">
                    Password
                    <span v-if="mode === 'create'" class="text-danger">*</span>
                    <span v-else style="font-size: 0.75rem; color: #94a3b8;">(leave blank to keep current)</span>
                  </label>
                  <div class="input-group">
                    <input
                      :type="showPw ? 'text' : 'password'"
                      class="form-control"
                      id="form-password"
                      v-model="form.password"
                      placeholder="Minimum 8 characters"
                      :required="mode === 'create'"
                      minlength="8"
                    />
                    <button
                      type="button"
                      class="input-group-text bg-white"
                      @click="showPw = !showPw"
                      tabindex="-1"
                    >
                      <i class="bi" :class="showPw ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" @click="$emit('close')">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary" :disabled="isSaving" id="user-form-submit">
                <span v-if="isSaving" class="spinner-border spinner-border-sm me-1"></span>
                {{ mode === 'create' ? 'Create User' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import api from '../services/api';
import RoleSelect from './RoleSelect.vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  mode: { type: String, default: 'create' },
  user: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const form = reactive({
  full_name: '',
  username: '',
  email: '',
  password: '',
  role: 'staff',
});

const isSaving = ref(false);
const showPw = ref(false);
const errorMessage = ref('');

// Watch for modal open / user changes
watch(
  () => props.show,
  (val) => {
    if (val) {
      errorMessage.value = '';
      showPw.value = false;
      if (props.mode === 'edit' && props.user) {
        form.full_name = props.user.full_name;
        form.username = props.user.username;
        form.email = props.user.email;
        form.role = props.user.role;
        form.password = '';
      } else {
        form.full_name = '';
        form.username = '';
        form.email = '';
        form.role = 'staff';
        form.password = '';
      }
    }
  }
);

async function handleSubmit() {
  isSaving.value = true;
  errorMessage.value = '';

  try {
    if (props.mode === 'create') {
      await api.post('/users', { ...form });
      emit('saved', 'User created successfully.');
    } else {
      const payload = {
        full_name: form.full_name,
        username: form.username,
        email: form.email,
        role: form.role,
      };
      if (form.password) payload.password = form.password;
      await api.put(`/users/${props.user.id}`, payload);
      emit('saved', 'User updated successfully.');
    }
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data.errors;
      errorMessage.value = errors
        ? Object.values(errors).flat().join(' ')
        : 'Validation error.';
    } else {
      errorMessage.value = error.response?.data?.message || 'An error occurred.';
    }
  } finally {
    isSaving.value = false;
  }
}
</script>

<style scoped>
.modal.show {
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>
