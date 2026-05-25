<template>
  <div>
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h4 class="mb-1" style="font-weight: 700;">Residents Registry</h4>
        <p class="mb-0 text-muted" style="font-size: 0.8125rem;">
          Browse, search, and manage resident profiles and household structures.
        </p>
      </div>
      <button class="btn btn-primary" @click="navigateCreate" id="add-resident-btn">
        <i class="bi bi-person-plus-fill me-1"></i> Add Resident
      </button>
    </div>

    <!-- Filters Panel -->
    <div class="card mb-4 border-0 shadow-sm">
      <div class="card-body py-3">
        <div class="row g-3 align-items-end">
          <!-- Search -->
          <div class="col-md-4">
            <label class="form-label text-muted small fw-semibold">Search Name</label>
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
              </span>
              <input
                type="text"
                class="form-control border-start-0 ps-0"
                placeholder="Search by first name or last name..."
                v-model="filters.search"
                @input="debouncedFetch"
              />
            </div>
          </div>

          <!-- Purok -->
          <div class="col-md-3">
            <label class="form-label text-muted small fw-semibold">Purok</label>
            <select class="form-select" v-model="filters.purok_id" @change="fetchResidents">
              <option value="">All Puroks</option>
              <option v-for="purok in puroks" :key="purok.id" :value="purok.id">
                {{ purok.purok_name }} ({{ purok.zone }})
              </option>
            </select>
          </div>

          <!-- Quick Badges Filters -->
          <div class="col-md-4">
            <label class="form-label text-muted small fw-semibold">Classification Filters</label>
            <div class="d-flex flex-wrap gap-2">
              <button
                class="btn btn-sm btn-outline-secondary flex-fill"
                :class="{ active: filters.is_voter === '1' }"
                @click="toggleFilter('is_voter')"
              >
                Voter
              </button>
              <button
                class="btn btn-sm btn-outline-secondary flex-fill"
                :class="{ active: filters.is_indigent === '1' }"
                @click="toggleFilter('is_indigent')"
              >
                Indigent
              </button>
              <button
                class="btn btn-sm btn-outline-secondary flex-fill"
                :class="{ active: filters.is_pwd === '1' }"
                @click="toggleFilter('is_pwd')"
              >
                PWD
              </button>
              <button
                class="btn btn-sm btn-outline-secondary flex-fill"
                :class="{ active: filters.is_senior_citizen === '1' }"
                @click="toggleFilter('is_senior_citizen')"
              >
                Senior
              </button>
            </div>
          </div>

          <!-- Reset -->
          <div class="col-md-1">
            <button class="btn btn-outline-secondary w-100" @click="resetFilters" title="Reset Filters">
              <i class="bi bi-arrow-counterclockwise"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Message -->
    <transition name="fade">
      <div v-if="successMessage" class="alert alert-success d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i>{{ successMessage }}
        <button type="button" class="btn-close ms-auto" @click="successMessage = ''" style="font-size: 0.625rem;"></button>
      </div>
    </transition>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="residents-table">
            <thead class="table-light text-muted" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">
              <tr>
                <th class="ps-4">Resident</th>
                <th>Age / Gender</th>
                <th>Purok</th>
                <th>Household Head</th>
                <th>Classification</th>
                <th class="text-end pe-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Loading state -->
              <tr v-if="isLoading">
                <td colspan="6" class="text-center py-5 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                  Loading residents registry...
                </td>
              </tr>
              <!-- Empty state -->
              <tr v-else-if="residents.length === 0">
                <td colspan="6" class="text-center py-5 text-muted">
                  <i class="bi bi-person-x fs-1"></i>
                  <p class="mt-2 mb-0">No resident records found.</p>
                </td>
              </tr>
              <!-- Row data -->
              <tr v-for="resident in residents" :key="resident.id">
                <td class="ps-4">
                  <div class="d-flex align-items-center gap-3">
                    <!-- Avatar Thumbnail -->
                    <div class="avatar-thumbnail shadow-sm">
                      <img v-if="resident.photo_url" :src="resident.photo_url" alt="Photo" />
                      <span v-else>{{ getInitials(resident.full_name) }}</span>
                    </div>
                    <div>
                      <div class="fw-bold text-dark">{{ resident.full_name }}</div>
                      <div class="text-muted small" style="font-size: 0.75rem;">ID: #{{ resident.id }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <div>{{ calculateAge(resident.date_of_birth) }} yrs old</div>
                  <div class="text-muted small">{{ resident.gender }}</div>
                </td>
                <td>
                  <span class="purok-tag">{{ resident.purok_name }}</span>
                </td>
                <td>
                  <span class="text-secondary" style="font-size: 0.875rem;">
                    {{ resident.head_of_household_name === 'N/A' ? 'Self (Head)' : resident.head_of_household_name }}
                  </span>
                </td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <span v-if="resident.is_voter" class="badge-custom badge-voter">Voter</span>
                    <span v-if="resident.is_indigent" class="badge-custom badge-indigent">Indigent</span>
                    <span v-if="resident.is_pwd" class="badge-custom badge-pwd">PWD</span>
                    <span v-if="resident.is_senior_citizen" class="badge-custom badge-senior">Senior</span>
                  </div>
                </td>
                <td class="text-end pe-4">
                  <div class="d-flex gap-1 justify-content-end">
                    <button class="btn btn-ghost btn-sm" title="View Profile" @click="navigateProfile(resident.id)">
                      <i class="bi bi-eye text-info"></i>
                    </button>
                    <button class="btn btn-ghost btn-sm" title="Edit Profile" @click="navigateEdit(resident.id)">
                      <i class="bi bi-pencil text-primary"></i>
                    </button>
                    <button class="btn btn-ghost btn-sm" title="Deactivate" @click="confirmDeactivate(resident)">
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
      <div class="card-footer bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 py-3 border-0 border-top" v-if="meta.last_page > 1">
        <div style="font-size: 0.8125rem; color: #64748b;">
          Showing {{ (meta.current_page - 1) * meta.per_page + 1 }} to
          {{ Math.min(meta.current_page * meta.per_page, meta.total) }}
          of {{ meta.total }} residents
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

    <!-- Confirm Deactivate Modal -->
    <div class="modal fade" id="deactivateResModal" tabindex="-1" ref="deactivateResModalRef">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title text-danger">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>Deactivate Profile
            </h6>
          </div>
          <div class="modal-body" style="font-size: 0.875rem;">
            Are you sure you want to deactivate and archive <strong>{{ selectedResident?.full_name }}</strong>?
            This will mark them as inactive in all modules.
          </div>
          <div class="modal-footer border-0 pt-0">
            <button class="btn btn-sm btn-outline-secondary" @click="closeDeactivateModal">Cancel</button>
            <button class="btn btn-sm btn-danger" @click="deactivateResident" :disabled="isProcessing">
              <span v-if="isProcessing" class="spinner-border spinner-border-sm me-1"></span>
              Deactivate
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Modal } from 'bootstrap';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';

const router = useRouter();
const authStore = useAuthStore();

// --- State ---
const residents = ref([]);
const puroks = ref([]);
const isLoading = ref(false);
const isProcessing = ref(false);
const successMessage = ref('');
const selectedResident = ref(null);

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const filters = reactive({
  search: '',
  purok_id: '',
  is_voter: '',
  is_indigent: '',
  is_pwd: '',
  is_senior_citizen: '',
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

function calculateAge(dobString) {
  if (!dobString) return '0';
  const dob = new Date(dobString);
  const diffMs = Date.now() - dob.getTime();
  const ageDate = new Date(diffMs);
  return Math.abs(ageDate.getUTCFullYear() - 1970);
}

function toggleFilter(key) {
  filters[key] = filters[key] === '1' ? '' : '1';
  fetchResidents();
}

function debouncedFetch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => fetchResidents(), 300);
}

async function fetchPuroks() {
  try {
    const response = await api.get('/puroks');
    puroks.value = response.data.puroks || [];
  } catch (e) {
    console.error('Failed to fetch puroks list:', e);
  }
}

async function fetchResidents(page = 1) {
  isLoading.value = true;
  try {
    const params = { page, per_page: meta.per_page };
    if (filters.search) params.search = filters.search;
    if (filters.purok_id) params.purok_id = filters.purok_id;
    if (filters.is_voter !== '') params.is_voter = filters.is_voter;
    if (filters.is_indigent !== '') params.is_indigent = filters.is_indigent;
    if (filters.is_pwd !== '') params.is_pwd = filters.is_pwd;
    if (filters.is_senior_citizen !== '') params.is_senior_citizen = filters.is_senior_citizen;

    const response = await api.get('/residents', { params });
    residents.value = response.data.residents;
    Object.assign(meta, response.data.meta);
  } catch (e) {
    console.error('Failed to fetch residents list:', e);
  } finally {
    isLoading.value = false;
  }
}

function goToPage(page) {
  if (page < 1 || page > meta.last_page) return;
  fetchResidents(page);
}

function resetFilters() {
  filters.search = '';
  filters.purok_id = '';
  filters.is_voter = '';
  filters.is_indigent = '';
  filters.is_pwd = '';
  filters.is_senior_citizen = '';
  fetchResidents();
}

// Navigation helpers depending on role prefix
function navigateCreate() {
  router.push(`/${authStore.role}/residents/create`);
}

function navigateProfile(id) {
  router.push(`/${authStore.role}/residents/${id}`);
}

function navigateEdit(id) {
  router.push(`/${authStore.role}/residents/${id}/edit`);
}

// Deactivate logic
const deactivateResModalRef = ref(null);
let deactivateModalInstance = null;

function confirmDeactivate(resident) {
  selectedResident.value = resident;
  deactivateModalInstance = new Modal(deactivateResModalRef.value);
  deactivateModalInstance.show();
}

function closeDeactivateModal() {
  deactivateModalInstance?.hide();
}

async function deactivateResident() {
  isProcessing.value = true;
  try {
    await api.delete(`/residents/${selectedResident.value.id}`);
    closeDeactivateModal();
    successMessage.value = `${selectedResident.value.full_name} has been archived successfully.`;
    fetchResidents(meta.current_page);
    setTimeout(() => (successMessage.value = ''), 4000);
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to archive resident profile.');
  } finally {
    isProcessing.value = false;
  }
}

onMounted(() => {
  fetchPuroks();
  fetchResidents();
});
</script>

<style scoped>
.avatar-thumbnail {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  overflow: hidden;
  background-color: var(--bs-primary-bg-subtle, #e0f2fe);
  color: var(--bs-primary, #0369a1);
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 0.8125rem;
  font-weight: 700;
}

.avatar-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.purok-tag {
  background-color: #f1f5f9;
  color: #475569;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

/* Custom indicator badges */
.badge-custom {
  font-size: 0.6875rem;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 4px;
}

.badge-voter {
  background-color: rgba(59, 130, 246, 0.1);
  color: #2563eb;
}

.badge-indigent {
  background-color: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.badge-pwd {
  background-color: rgba(139, 92, 246, 0.1);
  color: #7c3aed;
}

.badge-senior {
  background-color: rgba(245, 158, 11, 0.1);
  color: #d97706;
}

/* Active filter state buttons styling */
.btn-outline-secondary.active {
  background-color: #64748b;
  border-color: #64748b;
  color: white;
}
</style>
