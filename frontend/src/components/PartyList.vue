<template>
  <div>
    <!-- Header with Add Button -->
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h6 class="mb-0 fw-bold text-muted small uppercase">Involved Parties List</h6>
      <button
        v-if="allowAdd"
        class="btn btn-outline-primary btn-sm px-2 py-1"
        @click="openAddPartyModal"
      >
        <i class="bi bi-person-plus-fill me-1"></i> Add Party Member
      </button>
    </div>

    <!-- Empty State -->
    <div v-if="!parties || parties.length === 0" class="text-center py-4 border rounded-3 bg-light text-muted small">
      No parties registered for this incident.
    </div>

    <!-- Parties Grid / Table -->
    <div v-else class="row g-3">
      <div v-for="party in parties" :key="party.id" class="col-md-6 col-lg-4">
        <div class="card border border-light-subtle shadow-xs h-100 position-relative overflow-hidden">
          <!-- Role Color Bar -->
          <div class="position-absolute top-0 start-0 w-100" :class="getRoleColorBar(party.role)" style="height: 4px;"></div>
          
          <div class="card-body pt-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <span class="badge" :class="getRoleBadgeClass(party.role)">
                  {{ formatRole(party.role) }}
                </span>
                <span v-if="party.resident_id" class="badge bg-light text-primary border border-primary-subtle ms-1" style="font-size: 0.65rem;">
                  Registered Resident
                </span>
                <span v-else class="badge bg-light text-secondary border border-secondary-subtle ms-1" style="font-size: 0.65rem;">
                  Walk-In
                </span>
              </div>
            </div>

            <h6 class="fw-bold mb-1 text-dark">{{ party.full_name }}</h6>
            
            <div class="text-muted small mb-2" style="font-size: 0.75rem;">
              <div class="d-flex align-items-center gap-1 mb-1" v-if="party.contact_number">
                <i class="bi bi-telephone-fill"></i> {{ party.contact_number }}
              </div>
              <div class="d-flex align-items-center gap-1" v-if="party.address">
                <i class="bi bi-geo-alt-fill"></i> {{ party.address }}
              </div>
            </div>

            <!-- Testimony / Statement -->
            <div v-if="party.statement" class="bg-light p-2 rounded-2 mt-2 border border-light-subtle">
              <span class="text-muted d-block small fw-bold mb-1" style="font-size: 0.65rem; text-transform: uppercase;">
                Statement / Testimony
              </span>
              <p class="mb-0 text-secondary small italic text-wrap" style="font-size: 0.75rem; max-height: 80px; overflow-y: auto;">
                "{{ party.statement }}"
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Party Modal -->
    <div class="modal fade" id="addPartyModal" tabindex="-1" ref="partyModalRef" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title fw-bold text-primary">
              <i class="bi bi-person-plus-fill me-2"></i>Add Incident Party Member
            </h6>
            <button type="button" class="btn-close" @click="closeAddPartyModal" style="font-size: 0.75rem;"></button>
          </div>
          
          <form @submit.prevent="handleSubmit">
            <div class="modal-body py-3">
              <!-- Role -->
              <div class="mb-3">
                <label for="partyRole" class="form-label text-muted small fw-semibold">Case Role</label>
                <select class="form-select form-select-sm" id="partyRole" v-model="form.role" required>
                  <option value="" disabled>-- Select Role --</option>
                  <option value="complainant">Complainant (Filer)</option>
                  <option value="respondent">Respondent (Accused)</option>
                  <option value="witness">Witness</option>
                </select>
              </div>

              <!-- Selection Method Toggle -->
              <div class="mb-3">
                <label class="form-label text-muted small fw-semibold d-block">Party Registry Status</label>
                <div class="btn-group w-100 btn-group-sm" role="group">
                  <input
                    type="radio"
                    class="btn-check"
                    name="party_type"
                    id="type_resident"
                    value="resident"
                    v-model="partyType"
                    @change="clearInputs"
                  />
                  <label class="btn btn-outline-secondary" for="type_resident">Barangay Resident</label>

                  <input
                    type="radio"
                    class="btn-check"
                    name="party_type"
                    id="type_walkin"
                    value="walkin"
                    v-model="partyType"
                    @change="clearInputs"
                  />
                  <label class="btn btn-outline-secondary" for="type_walkin">Walk-in / External</label>
                </div>
              </div>

              <!-- Resident Selection Search -->
              <div v-if="partyType === 'resident'" class="mb-3 position-relative">
                <label class="form-label text-muted small fw-semibold">Search Resident</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-person-search"></i>
                  </span>
                  <input
                    type="text"
                    class="form-control border-start-0 ps-1"
                    placeholder="Search by name..."
                    v-model="residentSearchQuery"
                    @input="handleResidentSearch"
                    :disabled="selectedResident !== null"
                    :required="partyType === 'resident'"
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
                  style="max-height: 150px; overflow-y: auto; z-index: 1060;"
                >
                  <li v-for="res in searchResults" :key="res.id">
                    <a
                      class="dropdown-item py-2 d-flex justify-content-between align-items-center cursor-pointer"
                      @click="selectResident(res)"
                      href="#"
                    >
                      <div>
                        <strong class="text-dark">{{ res.full_name }}</strong>
                        <div class="text-muted" style="font-size: 0.65rem;">Purok: {{ res.purok_name }}</div>
                      </div>
                      <span class="badge bg-light text-muted small">ID: #{{ res.id }}</span>
                    </a>
                  </li>
                </ul>

                <div v-if="isSearching" class="position-absolute end-0 top-50 translate-middle-y me-3 mt-1">
                  <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                </div>

                <div v-if="selectedResident" class="form-text text-success fw-semibold">
                  <i class="bi bi-check-circle-fill me-1"></i> Selected: {{ selectedResident.full_name }}
                </div>
              </div>

              <!-- Manual Fields for Walk-in / Loaded for resident -->
              <div v-if="partyType === 'walkin' || selectedResident">
                <div class="mb-2">
                  <label for="pName" class="form-label text-muted small fw-semibold">Full Name</label>
                  <input
                    type="text"
                    class="form-control form-control-sm"
                    id="pName"
                    v-model="form.full_name"
                    :disabled="partyType === 'resident'"
                    required
                  />
                </div>

                <div class="row g-2">
                  <div class="col-md-6 mb-2">
                    <label for="pContact" class="form-label text-muted small fw-semibold">Contact #</label>
                    <input
                      type="text"
                      class="form-control form-control-sm"
                      id="pContact"
                      placeholder="e.g. 0917-XXXXXXX"
                      v-model="form.contact_number"
                      :disabled="partyType === 'resident'"
                    />
                  </div>
                  <div class="col-md-6 mb-2">
                    <label for="pAddress" class="form-label text-muted small fw-semibold">Address</label>
                    <input
                      type="text"
                      class="form-control form-control-sm"
                      id="pAddress"
                      placeholder="Street, City"
                      v-model="form.address"
                      :disabled="partyType === 'resident'"
                    />
                  </div>
                </div>
              </div>

              <!-- Statement -->
              <div class="mb-1" v-if="partyType === 'walkin' || selectedResident">
                <label for="pStatement" class="form-label text-muted small fw-semibold">Statement / Testimony</label>
                <textarea
                  class="form-control form-control-sm"
                  id="pStatement"
                  rows="3"
                  placeholder="Involved party's statement concerning the event..."
                  v-model="form.statement"
                ></textarea>
              </div>
            </div>

            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-sm btn-outline-secondary" @click="closeAddPartyModal">Cancel</button>
              <button type="submit" class="btn btn-sm btn-primary" :disabled="isAdding || (partyType === 'resident' && !selectedResident)">
                <span v-if="isAdding" class="spinner-border spinner-border-sm me-1" role="status"></span>
                Add Party Member
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Modal } from 'bootstrap';
import api from '../services/api';

const props = defineProps({
  parties: {
    type: Array,
    required: true,
    default: () => []
  },
  allowAdd: {
    type: Boolean,
    default: false
  },
  isAdding: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['add-party']);

const partyModalRef = ref(null);
let partyModalInstance = null;

const partyType = ref('resident'); // resident or walkin
const residentSearchQuery = ref('');
const searchResults = ref([]);
const selectedResident = ref(null);
const isSearching = ref(false);
let searchTimer = null;

const form = reactive({
  resident_id: null,
  role: '',
  full_name: '',
  address: '',
  contact_number: '',
  statement: ''
});

function openAddPartyModal() {
  clearForm();
  partyModalInstance?.show();
}

function closeAddPartyModal() {
  partyModalInstance?.hide();
}

function clearForm() {
  partyType.value = 'resident';
  residentSearchQuery.value = '';
  searchResults.value = [];
  selectedResident.value = null;
  form.resident_id = null;
  form.role = '';
  form.full_name = '';
  form.address = '';
  form.contact_number = '';
  form.statement = '';
}

function clearInputs() {
  residentSearchQuery.value = '';
  searchResults.value = [];
  selectedResident.value = null;
  form.resident_id = null;
  form.full_name = '';
  form.address = '';
  form.contact_number = '';
  form.statement = '';
}

// --- Autocomplete search ---
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
      console.error(e);
    } finally {
      isSearching.value = false;
    }
  }, 350);
}

function selectResident(resident) {
  selectedResident.value = resident;
  form.resident_id = resident.id;
  form.full_name = resident.full_name;
  form.address = resident.purok_name || 'N/A';
  form.contact_number = ''; // residents don't explicitly store contact_number in migrations, or if they do we can map it
  residentSearchQuery.value = resident.full_name;
  searchResults.value = [];
}

function clearSelectedResident() {
  selectedResident.value = null;
  form.resident_id = null;
  form.full_name = '';
  form.address = '';
  form.contact_number = '';
  residentSearchQuery.value = '';
  searchResults.value = [];
}

function handleSubmit() {
  if (partyType.value === 'resident' && !form.resident_id) return;
  if (!form.role) return;
  if (!form.full_name.trim()) return;

  emit('add-party', { ...form });
}

// Formatting helpers
function formatRole(role) {
  if (!role) return '';
  return role.toUpperCase();
}

function getRoleBadgeClass(role) {
  switch (role?.toLowerCase()) {
    case 'complainant':
      return 'bg-primary';
    case 'respondent':
      return 'bg-danger';
    case 'witness':
      return 'bg-info text-dark';
    default:
      return 'bg-secondary';
  }
}

function getRoleColorBar(role) {
  switch (role?.toLowerCase()) {
    case 'complainant':
      return 'bg-primary';
    case 'respondent':
      return 'bg-danger';
    case 'witness':
      return 'bg-info';
    default:
      return 'bg-secondary';
  }
}

onMounted(() => {
  if (partyModalRef.value) {
    partyModalInstance = new Modal(partyModalRef.value);
  }
});

defineExpose({ open: openAddPartyModal, close: closeAddPartyModal });
</script>

<style scoped>
.shadow-xs {
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.cursor-pointer {
  cursor: pointer;
}

.dropdown-item:hover {
  background-color: #f8fafc;
}

.uppercase {
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.italic {
  font-style: italic;
}
</style>
