<template>
  <div class="container-fluid max-width-800">
    <!-- Header -->
    <div class="mb-4">
      <h4 style="font-weight: 700;">File Incident Blotter Report</h4>
      <p class="text-muted small">Record a new incident blotter case and register involved parties.</p>
    </div>

    <!-- Main Grid -->
    <form @submit.prevent="handleSubmit">
      <div class="row g-4">
        <!-- Left: Incident Details -->
        <div class="col-lg-7">
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-3 text-secondary">
                <i class="bi bi-file-earmark-medical me-2"></i>Incident Specifications
              </h5>

              <!-- Incident Type -->
              <div class="mb-3">
                <label for="incidentType" class="form-label text-muted small fw-semibold">Incident Category / Type</label>
                <input
                  type="text"
                  class="form-control"
                  id="incidentType"
                  placeholder="e.g. Theft, Physical Dispute, Noise Disturbance"
                  v-model="form.incident_type"
                  required
                />
              </div>

              <!-- Date and Location -->
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label for="incidentDate" class="form-label text-muted small fw-semibold">Date & Time Occurred</label>
                  <input
                    type="datetime-local"
                    class="form-control"
                    id="incidentDate"
                    v-model="form.incident_date"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label for="incidentLocation" class="form-label text-muted small fw-semibold">Incident Location</label>
                  <input
                    type="text"
                    class="form-control"
                    id="incidentLocation"
                    placeholder="e.g. Purok 3 Main Road"
                    v-model="form.incident_location"
                    required
                  />
                </div>
              </div>

              <!-- Narrative -->
              <div class="mb-1">
                <label for="narrative" class="form-label text-muted small fw-semibold">Case Narrative / Details</label>
                <textarea
                  class="form-control"
                  id="narrative"
                  rows="6"
                  placeholder="Provide a detailed narrative of the event, including what transpired and who was involved..."
                  v-model="form.incident_narrative"
                  required
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Involved Parties Setup -->
        <div class="col-lg-5">
          <!-- Add Party Subform -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-3 text-secondary">
                <i class="bi bi-person-plus me-2"></i>Add Party to Case
              </h5>

              <!-- Role Selector -->
              <div class="mb-3">
                <label for="partyRole" class="form-label text-muted small fw-semibold">Involved Role</label>
                <select class="form-select form-select-sm" id="partyRole" v-model="partyForm.role">
                  <option value="" disabled>-- Select Role --</option>
                  <option value="complainant">Complainant (Filer)</option>
                  <option value="respondent">Respondent (Accused)</option>
                  <option value="witness">Witness</option>
                </select>
              </div>

              <!-- Selector Toggle -->
              <div class="mb-3">
                <div class="btn-group w-100 btn-group-sm" role="group">
                  <input
                    type="radio"
                    class="btn-check"
                    name="form_party_type"
                    id="form_type_resident"
                    value="resident"
                    v-model="partyType"
                    @change="clearPartyInputs"
                  />
                  <label class="btn btn-outline-secondary" for="form_type_resident">Resident</label>

                  <input
                    type="radio"
                    class="btn-check"
                    name="form_party_type"
                    id="form_type_walkin"
                    value="walkin"
                    v-model="partyType"
                    @change="clearPartyInputs"
                  />
                  <label class="btn btn-outline-secondary" for="form_type_walkin">Walk-in</label>
                </div>
              </div>

              <!-- Resident Lookup -->
              <div v-if="partyType === 'resident'" class="mb-3 position-relative">
                <label class="form-label text-muted small fw-semibold">Search Resident</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-person-search"></i>
                  </span>
                  <input
                    type="text"
                    class="form-control border-start-0 ps-1"
                    placeholder="Search name..."
                    v-model="residentSearchQuery"
                    @input="handleResidentSearch"
                    :disabled="selectedResident !== null"
                  />
                  <button
                    v-if="selectedResident"
                    type="button"
                    class="btn btn-outline-danger"
                    @click="clearSelectedResident"
                  >
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>

                <!-- Autocomplete Dropdown List -->
                <ul
                  v-if="searchResults.length > 0 && !selectedResident"
                  class="dropdown-menu show w-100 shadow-sm border border-light-subtle py-1"
                  style="max-height: 150px; overflow-y: auto; z-index: 10;"
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
              </div>

              <!-- Manual / Walkin Form -->
              <div v-if="partyType === 'walkin' || selectedResident">
                <div class="mb-2">
                  <label class="form-label text-muted small fw-semibold">Full Name</label>
                  <input
                    type="text"
                    class="form-control form-control-sm"
                    v-model="partyForm.full_name"
                    :disabled="partyType === 'resident'"
                    required
                  />
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-6">
                    <label class="form-label text-muted small fw-semibold">Contact #</label>
                    <input
                      type="text"
                      class="form-control form-control-sm"
                      v-model="partyForm.contact_number"
                      :disabled="partyType === 'resident'"
                    />
                  </div>
                  <div class="col-6">
                    <label class="form-label text-muted small fw-semibold">Address</label>
                    <input
                      type="text"
                      class="form-control form-control-sm"
                      v-model="partyForm.address"
                      :disabled="partyType === 'resident'"
                    />
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label text-muted small fw-semibold">Statement</label>
                  <textarea
                    class="form-control form-control-sm"
                    rows="2"
                    placeholder="Short statement (optional)..."
                    v-model="partyForm.statement"
                  ></textarea>
                </div>

                <button
                  type="button"
                  class="btn btn-sm btn-outline-primary w-100"
                  @click="addPartyToDraft"
                  :disabled="partyType === 'resident' && !selectedResident"
                >
                  <i class="bi bi-plus-lg me-1"></i> Add Party Member
                </button>
              </div>
            </div>
          </div>

          <!-- Draft List -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
              <h6 class="fw-bold mb-3 text-secondary">Draft Parties Added ({{ form.parties.length }})</h6>
              
              <div v-if="form.parties.length === 0" class="text-center py-3 text-muted small border rounded-3 bg-light">
                No parties added yet. A blotter typically requires at least a complainant and respondent.
              </div>
              <ul v-else class="list-group list-group-flush">
                <li
                  v-for="(party, idx) in form.parties"
                  :key="idx"
                  class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center"
                >
                  <div>
                    <span class="badge me-2" :class="getRoleClass(party.role)">
                      {{ party.role }}
                    </span>
                    <strong class="text-dark small">{{ party.full_name }}</strong>
                    <div class="text-muted" style="font-size: 0.65rem;">
                      {{ party.resident_id ? 'Resident' : 'Walk-In' }}
                    </div>
                  </div>
                  <button type="button" class="btn btn-outline-danger btn-xs px-2 py-1" @click="removeParty(idx)">
                    <i class="bi bi-trash"></i>
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Panel -->
      <div class="row mt-3 mb-5">
        <div class="col-12">
          <!-- Error Feedback -->
          <div v-if="errorMessage" class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-exclamation-octagon-fill"></i>
            {{ errorMessage }}
          </div>

          <div class="d-flex justify-content-end gap-2 border-top pt-3 bg-white py-3 shadow-xs">
            <button type="button" class="btn btn-outline-secondary" @click="navigateBack" :disabled="isSubmitting">
              Cancel
            </button>
            <button type="submit" class="btn btn-primary" :disabled="isSubmitting || form.parties.length === 0">
              <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1" role="status"></span>
              File Incident Blotter
            </button>
          </div>
        </div>
      </div>
    </form>
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
  incident_type: '',
  incident_date: '',
  incident_location: '',
  incident_narrative: '',
  parties: [],
});

// Add Party Form State
const partyType = ref('resident'); // resident or walkin
const residentSearchQuery = ref('');
const searchResults = ref([]);
const selectedResident = ref(null);
const isSearching = ref(false);
let searchTimer = null;

const partyForm = reactive({
  resident_id: null,
  role: '',
  full_name: '',
  address: '',
  contact_number: '',
  statement: '',
});

const isSubmitting = ref(false);
const errorMessage = ref('');

// --- Methods ---
function navigateBack() {
  router.push(`/${authStore.role}/blotters`);
}

function clearPartyForm() {
  partyType.value = 'resident';
  residentSearchQuery.value = '';
  searchResults.value = [];
  selectedResident.value = null;
  partyForm.resident_id = null;
  partyForm.role = '';
  partyForm.full_name = '';
  partyForm.address = '';
  partyForm.contact_number = '';
  partyForm.statement = '';
}

function clearPartyInputs() {
  residentSearchQuery.value = '';
  searchResults.value = [];
  selectedResident.value = null;
  partyForm.resident_id = null;
  partyForm.full_name = '';
  partyForm.address = '';
  partyForm.contact_number = '';
  partyForm.statement = '';
}

// Autocomplete resident
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
        params: { search: residentSearchQuery.value, per_page: 10 }
      });
      searchResults.value = response.data.residents || [];
    } catch (e) {
      console.error(e);
    } finally {
      isSearching.value = false;
    }
  }, 350);
}

function selectResident(res) {
  selectedResident.value = res;
  partyForm.resident_id = res.id;
  partyForm.full_name = res.full_name;
  partyForm.address = res.purok_name || 'N/A';
  residentSearchQuery.value = res.full_name;
  searchResults.value = [];
}

function clearSelectedResident() {
  selectedResident.value = null;
  partyForm.resident_id = null;
  partyForm.full_name = '';
  partyForm.address = '';
  residentSearchQuery.value = '';
}

// Add to draft list
function addPartyToDraft() {
  if (!partyForm.role) {
    alert('Please specify a role (Complainant, Respondent, Witness).');
    return;
  }
  if (!partyForm.full_name.trim()) {
    alert('Please provide the party name.');
    return;
  }

  form.parties.push({ ...partyForm });
  clearPartyForm();
}

function removeParty(index) {
  form.parties.splice(index, 1);
}

function getRoleClass(role) {
  switch (role) {
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

// Submit Blotter Case
async function handleSubmit() {
  if (form.parties.length === 0) {
    errorMessage.value = 'Please add at least one party involved (Complainant or Respondent).';
    return;
  }

  isSubmitting.value = true;
  errorMessage.value = '';
  try {
    // Format incident_date to a standard DB format if necessary
    await api.post('/blotters', form);
    router.push({
      path: `/${authStore.role}/blotters`,
      query: { success: 'true' }
    });
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Failed to file blotter record.';
  } finally {
    isSubmitting.value = false;
  }
}
</script>

<style scoped>
.max-width-800 {
  max-width: 800px;
  margin: 0 auto;
}

.cursor-pointer {
  cursor: pointer;
}

.dropdown-item:hover {
  background-color: #f8fafc;
}

.btn-xs {
  font-size: 0.75rem;
  padding: 0.2rem 0.4rem;
}

.shadow-xs {
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>
