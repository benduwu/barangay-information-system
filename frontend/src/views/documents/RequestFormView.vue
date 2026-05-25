<template>
  <div class="container-fluid max-width-600">
    <!-- Header -->
    <div class="mb-4">
      <h4 style="font-weight: 700;">Create Document Request</h4>
      <p class="text-muted small">File a new official certificate request for a resident.</p>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <form @submit.prevent="handleSubmit">
          <!-- Resident Autocomplete Search -->
          <div class="mb-3 position-relative">
            <label class="form-label text-muted small fw-semibold">Resident Selection</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0">
                <i class="bi bi-person-search"></i>
              </span>
              <input
                type="text"
                class="form-control border-start-0 ps-1"
                placeholder="Search resident by first or last name..."
                v-model="residentSearchQuery"
                @input="handleResidentSearch"
                :disabled="selectedResident !== null"
                required
              />
              <button
                v-if="selectedResident"
                type="button"
                class="btn btn-outline-danger"
                @click="clearSelectedResident"
              >
                <i class="bi bi-x-lg"></i> Clear
              </button>
            </div>

            <!-- Autocomplete Dropdown List -->
            <ul
              v-if="searchResults.length > 0 && !selectedResident"
              class="dropdown-menu show w-100 shadow-sm border border-light-subtle py-1"
              style="max-height: 200px; overflow-y: auto;"
            >
              <li v-for="res in searchResults" :key="res.id">
                <a
                  class="dropdown-item py-2 d-flex justify-content-between align-items-center cursor-pointer"
                  @click="selectResident(res)"
                  href="#"
                >
                  <div>
                    <strong class="text-dark">{{ res.full_name }}</strong>
                    <div class="text-muted small" style="font-size: 0.7rem;">Purok: {{ res.purok_name }}</div>
                  </div>
                  <span class="badge bg-light text-muted small">ID: #{{ res.id }}</span>
                </a>
              </li>
            </ul>

            <div v-if="isSearching" class="position-absolute end-0 top-50 translate-middle-y me-3 mt-2">
              <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            </div>

            <div v-if="selectedResident" class="form-text text-success fw-semibold">
              <i class="bi bi-check-circle-fill me-1"></i> Selected: {{ selectedResident.full_name }} ({{ selectedResident.purok_name }})
            </div>
            <div v-else class="form-text text-muted">
              Type the resident's name to fetch matches from the database.
            </div>
          </div>

          <!-- Document Type -->
          <div class="mb-3">
            <label for="docType" class="form-label text-muted small fw-semibold">Document Certificate Type</label>
            <select class="form-select" id="docType" v-model="form.document_type" required>
              <option value="" disabled>-- Select Document --</option>
              <option value="clearance">Barangay Clearance</option>
              <option value="residency">Certificate of Residency</option>
              <option value="indigency">Certificate of Indigency</option>
            </select>
          </div>

          <!-- Purpose -->
          <div class="mb-4">
            <label for="purpose" class="form-label text-muted small fw-semibold">Purpose of Request</label>
            <textarea
              class="form-control"
              id="purpose"
              rows="4"
              maxlength="500"
              placeholder="e.g. Job Application, Bank Requirements, Financial Assistance..."
              v-model="form.purpose"
              required
            ></textarea>
            <div class="d-flex justify-content-between form-text text-muted">
              <span>Explain why this document is required.</span>
              <span>{{ form.purpose.length }}/500</span>
            </div>
          </div>

          <!-- Error Feedback -->
          <div v-if="errorMessage" class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-exclamation-octagon-fill"></i>
            {{ errorMessage }}
          </div>

          <!-- Actions -->
          <div class="d-flex justify-content-end gap-2 border-top pt-3">
            <button type="button" class="btn btn-outline-secondary" @click="navigateBack" :disabled="isSubmitting">
              Cancel
            </button>
            <button type="submit" class="btn btn-primary" :disabled="isSubmitting || !selectedResident">
              <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1" role="status"></span>
              Create Request
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';

const router = useRouter();
const authStore = useAuthStore();

// --- Form State ---
const form = reactive({
  resident_id: null,
  document_type: '',
  purpose: '',
});

const residentSearchQuery = ref('');
const searchResults = ref([]);
const selectedResident = ref(null);
const isSearching = ref(false);
const isSubmitting = ref(false);
const errorMessage = ref('');
let searchTimer = null;

// --- Auto-complete Search ---
function handleResidentSearch() {
  clearTimeout(searchTimer);
  if (!residentSearchQuery.value.trim()) {
    searchResults.value = [];
    return;
  }
  
  isSearching.value = true;
  searchTimer = setTimeout(async () => {
    try {
      const response = await api.get('/residents', {
        params: { search: residentSearchQuery.value, per_page: 15 }
      });
      searchResults.value = response.data.residents || [];
    } catch (e) {
      console.error('Resident query failed:', e);
    } finally {
      isSearching.value = false;
    }
  }, 350);
}

function selectResident(resident) {
  selectedResident.value = resident;
  form.resident_id = resident.id;
  residentSearchQuery.value = resident.full_name;
  searchResults.value = [];
}

function clearSelectedResident() {
  selectedResident.value = null;
  form.resident_id = null;
  residentSearchQuery.value = '';
  searchResults.value = [];
}

// --- Navigation ---
function navigateBack() {
  router.push(`/${authStore.role}/documents`);
}

// --- Submit form ---
async function handleSubmit() {
  if (!form.resident_id) {
    errorMessage.value = 'Please select a resident from the autocomplete search.';
    return;
  }
  if (!form.document_type) {
    errorMessage.value = 'Please select a document type.';
    return;
  }
  if (!form.purpose.trim()) {
    errorMessage.value = 'Please fill out the purpose field.';
    return;
  }

  isSubmitting.value = true;
  errorMessage.value = '';
  try {
    await api.post('/documents', form);
    router.push({
      path: `/${authStore.role}/documents`,
      query: { success: 'true' }
    });
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Failed to submit document request.';
  } finally {
    isSubmitting.value = false;
  }
}
</script>

<style scoped>
.max-width-600 {
  max-width: 600px;
  margin: 0 auto;
}

.cursor-pointer {
  cursor: pointer;
}

.dropdown-item:hover {
  background-color: #f8fafc;
}
</style>
