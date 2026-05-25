<template>
  <div class="container-fluid py-2">
    <!-- Header -->
    <div class="d-flex align-items-center gap-3 mb-4">
      <button class="btn btn-outline-secondary btn-sm rounded-circle px-2" @click="goBack" title="Back to Registry">
        <i class="bi bi-arrow-left"></i>
      </button>
      <div>
        <h4 class="mb-1" style="font-weight: 700;">{{ isEditMode ? 'Edit Resident Profile' : 'Register New Resident' }}</h4>
        <p class="mb-0 text-muted" style="font-size: 0.8125rem;">
          {{ isEditMode ? 'Modify demographic records and affiliations.' : 'Add a new household member to the barangay registry.' }}
        </p>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Photo Upload Card -->
      <div class="col-lg-3">
        <div class="card border-0 shadow-sm text-center p-4">
          <h6 class="card-title text-muted mb-3" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Profile Picture</h6>
          <PhotoUpload
            :photo-url="photoUrl"
            @change="handlePhotoSelected"
          />
          <div class="mt-3 text-muted small" style="line-height: 1.4;">
            Capture or drop a formal 2x2 or portrait style photo for documents & identification.
          </div>
        </div>
      </div>

      <!-- Right Column: Form Information Card -->
      <div class="col-lg-9">
        <div class="card border-0 shadow-sm p-4">
          <!-- Error Alerts -->
          <div v-if="errorMessage" class="alert alert-danger" style="font-size: 0.875rem;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ errorMessage }}
          </div>

          <form @submit.prevent="handleSubmit" class="needs-validation" id="resident-form">
            <!-- Section 1: Demographics -->
            <h5 class="mb-3 pb-2 border-bottom fw-bold text-dark" style="font-size: 1rem;">
              <i class="bi bi-person-fill me-2 text-primary"></i>Personal Information
            </h5>
            <div class="row g-3 mb-4">
              <!-- First Name -->
              <div class="col-md-6">
                <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                <input
                  type="text"
                  id="firstName"
                  class="form-control"
                  placeholder="e.g. Juan"
                  v-model="form.first_name"
                  required
                />
              </div>
              
              <!-- Last Name -->
              <div class="col-md-6">
                <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input
                  type="text"
                  id="lastName"
                  class="form-control"
                  placeholder="e.g. Dela Cruz"
                  v-model="form.last_name"
                  required
                />
              </div>

              <!-- DOB -->
              <div class="col-md-4">
                <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                <input
                  type="date"
                  id="dob"
                  class="form-control"
                  v-model="form.date_of_birth"
                  @change="handleDobChange"
                  required
                />
              </div>

              <!-- Gender -->
              <div class="col-md-4">
                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                <select id="gender" class="form-select" v-model="form.gender" required>
                  <option value="" disabled>Select Gender</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                  <option value="Other">Other</option>
                </select>
              </div>

              <!-- Civil Status -->
              <div class="col-md-4">
                <label for="civilStatus" class="form-label">Civil Status <span class="text-danger">*</span></label>
                <select id="civilStatus" class="form-select" v-model="form.civil_status" required>
                  <option value="" disabled>Select Status</option>
                  <option value="Single">Single</option>
                  <option value="Married">Married</option>
                  <option value="Widowed">Widowed</option>
                  <option value="Divorced">Divorced</option>
                </select>
              </div>

              <!-- Occupation -->
              <div class="col-md-12">
                <label for="occupation" class="form-label">Occupation <span class="text-muted">(optional)</span></label>
                <input
                  type="text"
                  id="occupation"
                  class="form-control"
                  placeholder="e.g. Engineer, Store Owner, Student, Unemployed"
                  v-model="form.occupation"
                />
              </div>
            </div>

            <!-- Section 2: Affiliations & Households -->
            <h5 class="mb-3 pb-2 border-bottom fw-bold text-dark" style="font-size: 1rem;">
              <i class="bi bi-house-fill me-2 text-primary"></i>Purok & Household Association
            </h5>
            <div class="row g-3 mb-4">
              <!-- Purok Select -->
              <div class="col-md-6">
                <label for="purokSelect" class="form-label">Barangay Purok <span class="text-danger">*</span></label>
                <select id="purokSelect" class="form-select" v-model="form.purok_id" required>
                  <option value="" disabled>Select Purok Boundary</option>
                  <option v-for="purok in puroks" :key="purok.id" :value="purok.id">
                    {{ purok.purok_name }} ({{ purok.zone }})
                  </option>
                </select>
              </div>

              <!-- Household Group Head Select -->
              <div class="col-md-6">
                <HouseholdGroup
                  v-model="form.head_of_household_id"
                  :purok-id="form.purok_id"
                  :current-resident-id="residentId"
                />
              </div>
            </div>

            <!-- Section 3: Categories -->
            <h5 class="mb-3 pb-2 border-bottom fw-bold text-dark" style="font-size: 1rem;">
              <i class="bi bi-tag-fill me-2 text-primary"></i>Classification Indicators
            </h5>
            <div class="row g-3 mb-5">
              <!-- Voter -->
              <div class="col-sm-6 col-md-3">
                <div class="card p-3 border-0 bg-light text-center cursor-pointer checkbox-card" :class="{ selected: form.is_voter }" @click="form.is_voter = !form.is_voter">
                  <i class="bi bi-check-circle-fill checkbox-icon text-primary" v-if="form.is_voter"></i>
                  <i class="bi bi-circle checkbox-icon text-muted" v-else></i>
                  <div class="fw-bold mt-2 text-dark" style="font-size: 0.875rem;">Registered Voter</div>
                </div>
              </div>

              <!-- Indigent -->
              <div class="col-sm-6 col-md-3">
                <div class="card p-3 border-0 bg-light text-center cursor-pointer checkbox-card" :class="{ selected: form.is_indigent }" @click="form.is_indigent = !form.is_indigent">
                  <i class="bi bi-check-circle-fill checkbox-icon text-success" v-if="form.is_indigent"></i>
                  <i class="bi bi-circle checkbox-icon text-muted" v-else></i>
                  <div class="fw-bold mt-2 text-dark" style="font-size: 0.875rem;">Indigent Status</div>
                </div>
              </div>

              <!-- PWD -->
              <div class="col-sm-6 col-md-3">
                <div class="card p-3 border-0 bg-light text-center cursor-pointer checkbox-card" :class="{ selected: form.is_pwd }" @click="form.is_pwd = !form.is_pwd">
                  <i class="bi bi-check-circle-fill checkbox-icon text-purple" v-if="form.is_pwd"></i>
                  <i class="bi bi-circle checkbox-icon text-muted" v-else></i>
                  <div class="fw-bold mt-2 text-dark" style="font-size: 0.875rem;">PWD / Disabled</div>
                </div>
              </div>

              <!-- Senior Citizen -->
              <div class="col-sm-6 col-md-3">
                <div class="card p-3 border-0 bg-light text-center checkbox-card disabled-card" :class="{ selected: form.is_senior_citizen }">
                  <i class="bi bi-check-circle-fill checkbox-icon text-warning" v-if="form.is_senior_citizen"></i>
                  <i class="bi bi-circle checkbox-icon text-muted" v-else></i>
                  <div class="fw-bold mt-2 text-dark" style="font-size: 0.875rem;">Senior Citizen</div>
                  <small class="text-muted mt-1" style="font-size: 0.6875rem;">Auto-determined by age</small>
                </div>
              </div>
            </div>

            <!-- Footer Buttons -->
            <div class="d-flex justify-content-end gap-2 border-top pt-4">
              <button type="button" class="btn btn-outline-secondary px-4" @click="goBack" :disabled="isSaving">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary px-4" :disabled="isSaving" id="resident-form-submit">
                <span v-if="isSaving" class="spinner-border spinner-border-sm me-1"></span>
                {{ isEditMode ? 'Save Changes' : 'Register Profile' }}
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
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';
import PhotoUpload from '../../components/PhotoUpload.vue';
import HouseholdGroup from '../../components/HouseholdGroup.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

// --- Configuration ---
const residentId = route.params.id ? Number(route.params.id) : null;
const isEditMode = ref(!!residentId);

// --- State ---
const puroks = ref([]);
const isSaving = ref(false);
const errorMessage = ref('');
const photoUrl = ref(null);
const selectedPhotoFile = ref(null);

const form = reactive({
  first_name: '',
  last_name: '',
  date_of_birth: '',
  gender: '',
  civil_status: '',
  occupation: '',
  purok_id: '',
  head_of_household_id: '',
  is_voter: false,
  is_indigent: false,
  is_pwd: false,
  is_senior_citizen: false,
});

// --- Methods ---
function goBack() {
  router.push(`/${authStore.role}/residents`);
}

function handlePhotoSelected(file) {
  selectedPhotoFile.value = file;
}

function handleDobChange() {
  if (!form.date_of_birth) return;
  const dob = new Date(form.date_of_birth);
  const diffMs = Date.now() - dob.getTime();
  const ageDate = new Date(diffMs);
  const age = Math.abs(ageDate.getUTCFullYear() - 1970);
  
  // Auto senior citizen check
  form.is_senior_citizen = age >= 60;
}

async function fetchPuroks() {
  try {
    const response = await api.get('/puroks');
    puroks.value = response.data.puroks || [];
  } catch (e) {
    console.error('Failed to load puroks:', e);
  }
}

async function fetchResidentDetails() {
  if (!residentId) return;
  try {
    const response = await api.get(`/residents/${residentId}`);
    const resident = response.data.resident;
    
    form.first_name = resident.first_name;
    form.last_name = resident.last_name;
    form.date_of_birth = resident.date_of_birth;
    form.gender = resident.gender;
    form.civil_status = resident.civil_status;
    form.occupation = resident.occupation || '';
    form.purok_id = resident.purok_id;
    form.head_of_household_id = resident.head_of_household_id || '';
    form.is_voter = resident.is_voter;
    form.is_indigent = resident.is_indigent;
    form.is_pwd = resident.is_pwd;
    form.is_senior_citizen = resident.is_senior_citizen;
    photoUrl.value = resident.photo_url;
  } catch (error) {
    errorMessage.value = 'Failed to load resident profile details.';
    console.error(error);
  }
}

async function handleSubmit() {
  isSaving.value = true;
  errorMessage.value = '';

  try {
    const payload = { ...form };
    if (payload.head_of_household_id === '') {
      payload.head_of_household_id = null;
    }

    let residentSaved;

    if (isEditMode.value) {
      const response = await api.put(`/residents/${residentId}`, payload);
      residentSaved = response.data.resident;
    } else {
      const response = await api.post('/residents', payload);
      residentSaved = response.data.resident;
    }

    // Decoupled profile photo upload if a file was selected
    if (selectedPhotoFile.value && residentSaved && residentSaved.id) {
      const formData = new FormData();
      formData.append('photo', selectedPhotoFile.value);
      await api.post(`/residents/${residentSaved.id}/photo`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    }

    router.push({
      path: `/${authStore.role}/residents`,
      query: { success: isEditMode.value ? 'Profile updated successfully.' : 'Resident registered successfully.' }
    });
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data.errors;
      errorMessage.value = errors
        ? Object.values(errors).flat().join(' ')
        : 'Validation error.';
    } else {
      errorMessage.value = error.response?.data?.message || 'An error occurred during submission.';
    }
  } finally {
    isSaving.value = false;
  }
}

onMounted(() => {
  fetchPuroks();
  if (isEditMode.value) {
    fetchResidentDetails();
  }
});
</script>

<style scoped>
.checkbox-card {
  transition: all 0.2s ease-in-out;
  cursor: pointer;
  border: 1.5px solid transparent !important;
}

.checkbox-card:hover {
  transform: translateY(-2px);
  background-color: #f1f5f9 !important;
}

.checkbox-card.selected {
  border-color: var(--bs-primary-border-subtle, #bae6fd) !important;
  background-color: var(--bs-primary-bg-subtle, #f0f9ff) !important;
}

.disabled-card {
  pointer-events: none;
  background-color: #f8fafc !important;
}

.disabled-card.selected {
  border-color: var(--bs-warning-border-subtle, #fef3c7) !important;
  background-color: var(--bs-warning-bg-subtle, #fffbeb) !important;
}

.checkbox-icon {
  font-size: 1.35rem;
}

.text-purple {
  color: #7c3aed;
}
</style>
