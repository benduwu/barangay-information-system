<template>
  <div class="container-fluid py-2">
    <!-- Header -->
    <div class="d-flex align-items-center gap-3 mb-4">
      <button class="btn btn-outline-secondary btn-sm rounded-circle px-2" @click="goBack" title="Back to Registry">
        <i class="bi bi-arrow-left"></i>
      </button>
      <div>
        <h4 class="mb-1" style="font-weight: 700;">Resident Profile Card</h4>
        <p class="mb-0 text-muted" style="font-size: 0.8125rem;">
          View detailed demographic records and household relationships.
        </p>
      </div>
      <button class="btn btn-outline-primary btn-sm ms-auto px-3" @click="navigateEdit">
        <i class="bi bi-pencil-square me-1"></i> Edit Profile
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-5 text-muted">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2">Loading resident profile dashboard...</p>
    </div>

    <div v-else-if="resident" class="row g-4">
      <!-- Left Column: Core Identity Card -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm overflow-hidden mb-4">
          <!-- Profile Cover Header Background -->
          <div class="profile-cover"></div>

          <!-- Avatar Image & Name -->
          <div class="card-body pt-0 text-center position-relative" style="margin-top: -60px;">
            <div class="profile-avatar shadow">
              <img v-if="resident.photo_url" :src="resident.photo_url" alt="Profile" />
              <span v-else>{{ getInitials(resident.full_name) }}</span>
            </div>

            <h4 class="fw-bold text-dark mt-3 mb-1">{{ resident.full_name }}</h4>
            <span class="purok-badge mb-3 d-inline-block">{{ resident.purok_name }}</span>

            <!-- Classifications Grid -->
            <div class="d-flex flex-wrap justify-content-center gap-1 mb-3">
              <span class="badge-custom" :class="resident.is_voter ? 'badge-voter' : 'badge-inactive'">
                <i class="bi bi-check2-circle me-1" v-if="resident.is_voter"></i>Voter
              </span>
              <span class="badge-custom" :class="resident.is_indigent ? 'badge-indigent' : 'badge-inactive'">
                <i class="bi bi-gift me-1" v-if="resident.is_indigent"></i>Indigent
              </span>
              <span class="badge-custom" :class="resident.is_pwd ? 'badge-pwd' : 'badge-inactive'">
                <i class="bi bi-heart-pulse me-1" v-if="resident.is_pwd"></i>PWD
              </span>
              <span class="badge-custom" :class="resident.is_senior_citizen ? 'badge-senior' : 'badge-inactive'">
                <i class="bi bi-award me-1" v-if="resident.is_senior_citizen"></i>Senior
              </span>
            </div>
            
            <div class="border-top pt-3 d-flex justify-content-around text-center">
              <div>
                <div class="text-muted small" style="font-size: 0.6875rem; text-transform:uppercase;">Age</div>
                <div class="fw-bold text-dark fs-5">{{ calculateAge(resident.date_of_birth) }}</div>
              </div>
              <div class="border-start"></div>
              <div>
                <div class="text-muted small" style="font-size: 0.6875rem; text-transform:uppercase;">Gender</div>
                <div class="fw-bold text-dark fs-5">{{ resident.gender }}</div>
              </div>
              <div class="border-start"></div>
              <div>
                <div class="text-muted small" style="font-size: 0.6875rem; text-transform:uppercase;">Status</div>
                <div class="fw-bold" :class="resident.is_active ? 'text-success' : 'text-danger'" style="font-size: 1.05rem;">
                  {{ resident.is_active ? 'Active' : 'Inactive' }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Meta System Records -->
        <div class="card border-0 shadow-sm p-3">
          <h6 class="text-muted mb-3 pb-2 border-bottom" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">System Logs</h6>
          <div class="d-flex flex-column gap-2" style="font-size: 0.8125rem;">
            <div class="d-flex justify-between align-center">
              <span class="text-muted">Registered By:</span>
              <span class="fw-semibold text-dark">{{ resident.creator_name }}</span>
            </div>
            <div class="d-flex justify-between align-center">
              <span class="text-muted">Registered Date:</span>
              <span class="fw-semibold text-dark">{{ formatDate(resident.created_at) }}</span>
            </div>
            <div class="d-flex justify-between align-center">
              <span class="text-muted">Database ID:</span>
              <span class="fw-semibold text-dark">#{{ resident.id }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Personal Details & Households -->
      <div class="col-lg-8">
        <!-- Demographics Info -->
        <div class="card border-0 shadow-sm p-4 mb-4">
          <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom" style="font-size: 1.05rem;">
            <i class="bi bi-card-text me-2 text-primary"></i>Demographic Profile Details
          </h5>
          <div class="row g-3">
            <div class="col-sm-6">
              <div class="text-muted small" style="font-size: 0.75rem;">First Name</div>
              <div class="fw-semibold text-dark" style="font-size: 0.9375rem;">{{ resident.first_name }}</div>
            </div>
            <div class="col-sm-6">
              <div class="text-muted small" style="font-size: 0.75rem;">Last Name</div>
              <div class="fw-semibold text-dark" style="font-size: 0.9375rem;">{{ resident.last_name }}</div>
            </div>
            <div class="col-sm-6">
              <div class="text-muted small" style="font-size: 0.75rem;">Date of Birth</div>
              <div class="fw-semibold text-dark" style="font-size: 0.9375rem;">{{ formatDate(resident.date_of_birth, false) }}</div>
            </div>
            <div class="col-sm-6">
              <div class="text-muted small" style="font-size: 0.75rem;">Civil Status</div>
              <div class="fw-semibold text-dark" style="font-size: 0.9375rem;">{{ resident.civil_status }}</div>
            </div>
            <div class="col-12">
              <div class="text-muted small" style="font-size: 0.75rem;">Occupation / Employment</div>
              <div class="fw-semibold text-dark" style="font-size: 0.9375rem;">{{ resident.occupation || 'Unspecified / None' }}</div>
            </div>
          </div>
        </div>

        <!-- Household Members Section -->
        <div class="card border-0 shadow-sm p-4">
          <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex justify-content-between align-items-center" style="font-size: 1.05rem;">
            <span><i class="bi bi-people-fill me-2 text-primary"></i>Household Directory</span>
            <span class="badge bg-secondary font-weight-normal" style="font-size: 0.75rem;">
              {{ householdMembers.length + 1 }} total members
            </span>
          </h5>

          <!-- Household Structure Header -->
          <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-3 border-start border-primary border-3">
            <i class="bi bi-house-door-fill text-primary fs-3"></i>
            <div>
              <div class="fw-bold text-dark" style="font-size: 0.875rem;">
                Head of Household:
                <span class="text-primary">{{ isHeadOfHousehold ? 'Self (' + resident.full_name + ')' : resident.head_of_household_name }}</span>
              </div>
              <div class="text-muted small" style="font-size: 0.75rem;">
                All listed members must belong to the same geographical zone.
              </div>
            </div>
          </div>

          <!-- Members Table/List -->
          <div v-if="householdMembers.length === 0" class="text-center py-4 text-muted">
            <i class="bi bi-info-circle fs-3 mb-2 d-block"></i>
            <span style="font-size: 0.8125rem;">No other household members are registered under this household head.</span>
          </div>

          <div v-else class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
              <thead class="table-light">
                <tr>
                  <th>Name</th>
                  <th>Age / Gender</th>
                  <th>Civil Status</th>
                  <th class="text-end">Profile</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="member in householdMembers" :key="member.id">
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar-circle shadow-sm">
                        <img v-if="member.photo_url" :src="member.photo_url" alt="" />
                        <span v-else>{{ getInitials(member.full_name) }}</span>
                      </div>
                      <div class="fw-semibold text-dark">{{ member.full_name }}</div>
                    </div>
                  </td>
                  <td>{{ calculateAge(member.date_of_birth) }} yrs, {{ member.gender }}</td>
                  <td>{{ member.civil_status }}</td>
                  <td class="text-end">
                    <button class="btn btn-ghost btn-sm" @click="navigateProfile(member.id)">
                      <i class="bi bi-chevron-right text-muted"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// --- Configuration ---
const residentId = computed(() => Number(route.params.id));

// --- State ---
const resident = ref(null);
const householdMembers = ref([]);
const isLoading = ref(false);

// --- Computed ---
const isHeadOfHousehold = computed(() => {
  return resident.value && resident.value.head_of_household_id === null;
});

// --- Methods ---
function goBack() {
  router.push(`/${authStore.role}/residents`);
}

function navigateEdit() {
  router.push(`/${authStore.role}/residents/${residentId.value}/edit`);
}

function navigateProfile(id) {
  router.push(`/${authStore.role}/residents/${id}`);
}

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

function formatDate(dateStr, includeTime = true) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  if (includeTime) {
    options.hour = '2-digit';
    options.minute = '2-digit';
  }
  return d.toLocaleDateString('en-PH', options);
}

async function fetchProfileDetails() {
  isLoading.value = true;
  try {
    const response = await api.get(`/residents/${residentId.value}`);
    resident.value = response.data.resident;
    
    // Determine household directory members:
    if (isHeadOfHousehold.value) {
      // If they are head of household, household members are returned directly by the backend controller
      householdMembers.value = response.data.household_members || [];
    } else if (resident.value.head_of_household_id) {
      // If they are a member, query their head to list all other household members sharing that head!
      const headResponse = await api.get(`/residents/${resident.value.head_of_household_id}`);
      const headOfH = headResponse.data.resident;
      const allMembers = headResponse.data.household_members || [];
      
      // Household directory is the head + all fellow members except this resident themselves!
      const result = [headOfH, ...allMembers];
      householdMembers.value = result.filter(m => m.id !== residentId.value);
    }
  } catch (error) {
    console.error('Failed to load profile details:', error);
  } finally {
    isLoading.value = false;
  }
}

// Watch route params so that clicking members links dynamically re-loads the new profile!
watch(
  () => route.params.id,
  () => {
    if (route.params.id) {
      fetchProfileDetails();
    }
  }
);

onMounted(() => {
  fetchProfileDetails();
});
</script>

<style scoped>
.profile-cover {
  height: 120px;
  background: linear-gradient(135deg, var(--bs-primary) 0%, #1e40af 100%);
}

.profile-avatar {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  border: 4px solid #fff;
  background-color: #f1f5f9;
  overflow: hidden;
  margin: 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--bs-primary);
}

.profile-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.purok-badge {
  background-color: rgba(30, 58, 95, 0.08);
  color: var(--bs-primary);
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.8125rem;
  font-weight: 600;
}

.avatar-circle {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  overflow: hidden;
  background-color: #f1f5f9;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 0.6875rem;
  font-weight: 700;
  color: var(--bs-primary);
}

.avatar-circle img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Custom badges */
.badge-custom {
  font-size: 0.6875rem;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 4px;
  display: inline-flex;
  align-items: center;
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

.badge-inactive {
  background-color: #f1f5f9;
  color: #94a3b8;
}

.border-start {
  border-left: 1px solid #e2e8f0 !important;
}

.d-flex.justify-between {
  justify-content: space-between;
}
.d-flex.align-center {
  align-items: center;
}
</style>
