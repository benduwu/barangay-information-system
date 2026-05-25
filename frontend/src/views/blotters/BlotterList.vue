<template>
  <div>
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h4 class="mb-1" style="font-weight: 700;">Blotter & Incident Records</h4>
        <p class="mb-0 text-muted" style="font-size: 0.8125rem;">
          Register, track, and manage civil disputes, minor crimes, and public incidents.
        </p>
      </div>
      <button class="btn btn-primary" @click="navigateCreate" id="new-blotter-btn">
        <i class="bi bi-file-earmark-plus-fill me-1"></i> File Blotter Record
      </button>
    </div>

    <!-- Filters Panel -->
    <div class="card mb-4 border-0 shadow-sm">
      <div class="card-body py-3">
        <div class="row g-3 align-items-end">
          <!-- Search -->
          <div class="col-md-3">
            <label class="form-label text-muted small fw-semibold">Search Cases</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
              </span>
              <input
                type="text"
                class="form-control border-start-0 ps-0"
                placeholder="Number, incident, name..."
                v-model="filters.search"
                @input="debouncedFetch"
              />
            </div>
          </div>

          <!-- Status -->
          <div class="col-md-2">
            <label class="form-label text-muted small fw-semibold">Status</label>
            <select class="form-select form-select-sm" v-model="filters.status" @change="fetchRecords">
              <option value="">All Statuses</option>
              <option value="filed">Filed</option>
              <option value="under_investigation">Under Investigation</option>
              <option value="settled">Settled</option>
              <option value="escalated">Escalated</option>
            </select>
          </div>

          <!-- Date Start -->
          <div class="col-md-2.5 col-sm-6">
            <label class="form-label text-muted small fw-semibold">From Date</label>
            <input
              type="date"
              class="form-control form-control-sm"
              v-model="filters.date_start"
              @change="fetchRecords"
            />
          </div>

          <!-- Date End -->
          <div class="col-md-2.5 col-sm-6">
            <label class="form-label text-muted small fw-semibold">To Date</label>
            <input
              type="date"
              class="form-control form-control-sm"
              v-model="filters.date_end"
              @change="fetchRecords"
            />
          </div>

          <!-- Reset -->
          <div class="col-md-2 text-end">
            <button class="btn btn-sm btn-outline-secondary w-100" @click="resetFilters" title="Reset Filters">
              <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Feedback -->
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
          <table class="table table-hover align-middle mb-0" id="blotters-table">
            <thead class="table-light text-muted" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">
              <tr>
                <th class="ps-4">Blotter #</th>
                <th>Incident Details</th>
                <th>Complainant(s)</th>
                <th>Respondent(s)</th>
                <th>Incident Date</th>
                <th>Status</th>
                <th>Assigned Officer</th>
                <th class="text-end pe-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Loading -->
              <tr v-if="isLoading">
                <td colspan="8" class="text-center py-5 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                  Loading incident records...
                </td>
              </tr>
              <!-- Empty -->
              <tr v-else-if="records.length === 0">
                <td colspan="8" class="text-center py-5 text-muted">
                  <i class="bi bi-folder-x fs-1"></i>
                  <p class="mt-2 mb-0">No blotter records found.</p>
                </td>
              </tr>
              <!-- Row Data -->
              <tr v-else v-for="rec in records" :key="rec.id">
                <td class="ps-4 fw-bold text-primary" style="font-size: 0.875rem;">
                  {{ rec.blotter_number }}
                </td>
                <td>
                  <div class="fw-bold text-dark">{{ rec.incident_type }}</div>
                  <div class="text-muted small" style="font-size: 0.75rem; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ rec.incident_location }}
                  </div>
                </td>
                <td>
                  <div class="small fw-semibold text-dark">
                    {{ getComplainants(rec.parties) }}
                  </div>
                </td>
                <td>
                  <div class="small fw-semibold text-dark">
                    {{ getRespondents(rec.parties) }}
                  </div>
                </td>
                <td style="font-size: 0.8125rem;">
                  {{ formatDateTime(rec.incident_date) }}
                </td>
                <td>
                  <StatusPill :status="rec.status" />
                </td>
                <td style="font-size: 0.8125rem;" :class="{ 'text-muted italic': rec.assigned_officer_name === 'Unassigned' }">
                  {{ rec.assigned_officer_name }}
                </td>
                <td class="text-end pe-4">
                  <button class="btn btn-ghost btn-sm" title="View details" @click="viewDetail(rec.id)">
                    <i class="bi bi-eye text-primary"></i> View Details
                  </button>
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
          of {{ meta.total }} blotter records
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
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';
import StatusPill from '../../components/StatusPill.vue';

const router = useRouter();
const authStore = useAuthStore();

// --- State ---
const records = ref([]);
const isLoading = ref(false);
const successMessage = ref('');

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const filters = reactive({
  search: '',
  status: '',
  date_start: '',
  date_end: '',
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
function debouncedFetch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => fetchRecords(), 300);
}

async function fetchRecords(page = 1) {
  isLoading.value = true;
  try {
    const params = { page, per_page: meta.per_page };
    if (filters.search) params.search = filters.search;
    if (filters.status) params.status = filters.status;
    if (filters.date_start) params.date_start = filters.date_start;
    if (filters.date_end) params.date_end = filters.date_end;

    const response = await api.get('/blotters', { params });
    records.value = response.data.blotters;
    Object.assign(meta, response.data.meta);
  } catch (e) {
    console.error('Failed to fetch blotters list:', e);
  } finally {
    isLoading.value = false;
  }
}

function goToPage(page) {
  if (page < 1 || page > meta.last_page) return;
  fetchRecords(page);
}

function resetFilters() {
  filters.search = '';
  filters.status = '';
  filters.date_start = '';
  filters.date_end = '';
  fetchRecords();
}

function navigateCreate() {
  router.push(`/${authStore.role}/blotters/create`);
}

function viewDetail(id) {
  router.push(`/${authStore.role}/blotters/${id}`);
}

function formatDateTime(dateStr) {
  if (!dateStr) return 'N/A';
  const date = new Date(dateStr);
  return date.toLocaleString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

// Parties helper
function getComplainants(parties) {
  if (!parties) return 'N/A';
  const comps = parties.filter(p => p.role === 'complainant');
  if (comps.length === 0) return 'N/A';
  return comps.map(c => c.full_name).join(', ');
}

function getRespondents(parties) {
  if (!parties) return 'N/A';
  const resp = parties.filter(p => p.role === 'respondent');
  if (resp.length === 0) return 'N/A';
  return resp.map(r => r.full_name).join(', ');
}

onMounted(() => {
  fetchRecords();
  // Read query params for success triggers
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('success') === 'true') {
    successMessage.value = 'Incident blotter report filed and logged successfully.';
    setTimeout(() => {
      successMessage.value = '';
    }, 4500);
  }
});
</script>

<style scoped>
.table th {
  letter-spacing: 0.5px;
}

.btn-ghost {
  border: none;
  background: transparent;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.btn-ghost:hover {
  background-color: #f1f5f9;
}

.italic {
  font-style: italic;
}
</style>
