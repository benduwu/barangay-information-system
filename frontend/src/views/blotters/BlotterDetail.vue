<template>
  <div>
    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="text-muted mt-3 small">Loading blotter case details...</p>
    </div>

    <!-- Detail Content -->
    <div v-else-if="blotter">
      <!-- Header -->
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
          <h4 class="mb-1" style="font-weight: 700;">
            {{ blotter.blotter_number }}
            <StatusPill :status="blotter.status" class="ms-2" />
          </h4>
          <p class="mb-0 text-muted" style="font-size: 0.8125rem;">
            {{ blotter.incident_type }} · Filed by {{ blotter.creator_name }}
          </p>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm" @click="navigateBack">
            <i class="bi bi-arrow-left me-1"></i> Back to Registry
          </button>
          <button
            v-if="blotter.status !== 'settled' && blotter.status !== 'escalated'"
            class="btn btn-warning btn-sm text-dark"
            @click="openStatusModal"
          >
            <i class="bi bi-arrow-repeat me-1"></i> Update Status
          </button>
          <button class="btn btn-outline-primary btn-sm" @click="openAssignModal">
            <i class="bi bi-person-badge me-1"></i> Assign Officer
          </button>
        </div>
      </div>

      <!-- Two Column Layout -->
      <div class="row g-4">
        <!-- LEFT: Incident Details + Parties -->
        <div class="col-lg-7">
          <!-- Incident Info Card -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
              <h6 class="fw-bold text-secondary mb-3">
                <i class="bi bi-file-earmark-medical me-2"></i>Incident Details
              </h6>

              <div class="table-responsive">
                <table class="table table-bordered table-sm small align-middle mb-0">
                  <tbody>
                    <tr>
                      <td class="fw-semibold bg-light text-muted w-35">Blotter Number</td>
                      <td class="fw-bold text-primary">{{ blotter.blotter_number }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold bg-light text-muted">Incident Type</td>
                      <td>{{ blotter.incident_type }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold bg-light text-muted">Incident Date</td>
                      <td>{{ formatDateTime(blotter.incident_date) }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold bg-light text-muted">Location</td>
                      <td>{{ blotter.incident_location }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold bg-light text-muted">Status</td>
                      <td><StatusPill :status="blotter.status" /></td>
                    </tr>
                    <tr>
                      <td class="fw-semibold bg-light text-muted">Assigned Officer</td>
                      <td :class="{ 'fst-italic text-muted': blotter.assigned_officer_name === 'Unassigned' }">
                        {{ blotter.assigned_officer_name }}
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-semibold bg-light text-muted">Filed By</td>
                      <td>{{ blotter.creator_name }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold bg-light text-muted">Date Filed</td>
                      <td>{{ formatDateTime(blotter.created_at) }}</td>
                    </tr>
                    <tr v-if="blotter.settlement_details">
                      <td class="fw-semibold bg-light text-success">Settlement Details</td>
                      <td class="text-success">{{ blotter.settlement_details }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Narrative Block -->
              <div class="mt-3 bg-light p-3 rounded-3 border border-light-subtle">
                <div class="fw-bold small text-muted text-uppercase mb-2" style="letter-spacing: 0.5px;">
                  Case Narrative
                </div>
                <p class="mb-0 text-secondary small" style="white-space: pre-wrap; line-height: 1.7;">{{ blotter.incident_narrative }}</p>
              </div>
            </div>
          </div>

          <!-- Parties Card -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
              <PartyList
                :parties="blotter.parties || []"
                :allowAdd="blotter.status !== 'settled'"
                :isAdding="isAddingParty"
                @add-party="handleAddParty"
              />
            </div>
          </div>
        </div>

        <!-- RIGHT: Status Update Log Timeline -->
        <div class="col-lg-5">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <h6 class="fw-bold text-secondary mb-3">
                <i class="bi bi-clock-history me-2"></i>Status History Trail
              </h6>
              <UpdateLog :updates="blotter.updates || []" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" ref="statusModalRef">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title fw-bold text-warning">
              <i class="bi bi-arrow-repeat me-2"></i>Update Case Status
            </h6>
          </div>
          <form @submit.prevent="handleStatusUpdate">
            <div class="modal-body py-3">
              <div class="mb-3">
                <label for="newStatus" class="form-label text-muted small fw-semibold">New Status</label>
                <select class="form-select form-select-sm" id="newStatus" v-model="statusForm.status" required>
                  <option value="" disabled>-- Select New Status --</option>
                  <option value="filed">Filed</option>
                  <option value="under_investigation">Under Investigation</option>
                  <option value="settled">Settled</option>
                  <option value="escalated">Escalated</option>
                </select>
              </div>

              <div class="mb-3">
                <label for="updateNotes" class="form-label text-muted small fw-semibold">Notes / Remarks</label>
                <textarea
                  class="form-control form-control-sm"
                  id="updateNotes"
                  rows="3"
                  placeholder="Reason for status update..."
                  v-model="statusForm.notes"
                  required
                ></textarea>
              </div>

              <div v-if="statusForm.status === 'settled'" class="mb-2">
                <label for="settlementDetails" class="form-label text-muted small fw-semibold">Settlement Details</label>
                <textarea
                  class="form-control form-control-sm"
                  id="settlementDetails"
                  rows="3"
                  placeholder="How was the case resolved..."
                  v-model="statusForm.settlement_details"
                  required
                ></textarea>
              </div>
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-sm btn-outline-secondary" @click="closeStatusModal">Cancel</button>
              <button type="submit" class="btn btn-sm btn-warning text-dark" :disabled="isUpdatingStatus">
                <span v-if="isUpdatingStatus" class="spinner-border spinner-border-sm me-1"></span>
                Update Status
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Assign Officer Modal -->
    <div class="modal fade" id="assignModal" tabindex="-1" ref="assignModalRef">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title fw-bold text-primary">
              <i class="bi bi-person-badge me-2"></i>Assign Officer / Investigator
            </h6>
          </div>
          <form @submit.prevent="handleAssignOfficer">
            <div class="modal-body py-3">
              <div class="mb-3">
                <label for="officerSelect" class="form-label text-muted small fw-semibold">Select User</label>
                <select class="form-select form-select-sm" id="officerSelect" v-model="assignForm.assigned_officer_id" required>
                  <option value="" disabled>-- Select Officer --</option>
                  <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.full_name }} ({{ user.role }})
                  </option>
                </select>
              </div>
              <div class="mb-2">
                <label for="assignNotes" class="form-label text-muted small fw-semibold">Notes (optional)</label>
                <input
                  type="text"
                  class="form-control form-control-sm"
                  id="assignNotes"
                  placeholder="Assignment remarks"
                  v-model="assignForm.notes"
                />
              </div>
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-sm btn-outline-secondary" @click="closeAssignModal">Cancel</button>
              <button type="submit" class="btn btn-sm btn-primary" :disabled="isAssigning">
                <span v-if="isAssigning" class="spinner-border spinner-border-sm me-1"></span>
                Assign Officer
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
import { useRoute, useRouter } from 'vue-router';
import { Modal } from 'bootstrap';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';
import StatusPill from '../../components/StatusPill.vue';
import UpdateLog from '../../components/UpdateLog.vue';
import PartyList from '../../components/PartyList.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// --- State ---
const blotter = ref(null);
const isLoading = ref(true);
const users = ref([]);

// Status form
const isUpdatingStatus = ref(false);
const statusForm = reactive({ status: '', notes: '', settlement_details: '' });
const statusModalRef = ref(null);
let statusModalInstance = null;

// Assign form
const isAssigning = ref(false);
const assignForm = reactive({ assigned_officer_id: '', notes: '' });
const assignModalRef = ref(null);
let assignModalInstance = null;

// Add party
const isAddingParty = ref(false);

// --- Fetch ---
async function fetchBlotter() {
  isLoading.value = true;
  try {
    const response = await api.get(`/blotters/${route.params.id}`);
    blotter.value = response.data.blotter;
  } catch (e) {
    console.error('Failed to fetch blotter:', e);
    alert('Failed to load blotter details.');
    navigateBack();
  } finally {
    isLoading.value = false;
  }
}

async function fetchUsers() {
  try {
    const response = await api.get('/users');
    users.value = response.data.users || [];
  } catch (e) {
    // Users endpoint might only be for admins; fail silently for staff
    console.warn('Could not fetch users list:', e);
  }
}

// --- Navigation ---
function navigateBack() {
  router.push(`/${authStore.role}/blotters`);
}

// --- Status Update ---
function openStatusModal() {
  statusForm.status = '';
  statusForm.notes = '';
  statusForm.settlement_details = '';
  statusModalInstance = new Modal(statusModalRef.value);
  statusModalInstance.show();
}

function closeStatusModal() {
  statusModalInstance?.hide();
}

async function handleStatusUpdate() {
  isUpdatingStatus.value = true;
  try {
    const response = await api.put(`/blotters/${blotter.value.id}/status`, statusForm);
    blotter.value = response.data.blotter;
    closeStatusModal();
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to update status.');
  } finally {
    isUpdatingStatus.value = false;
  }
}

// --- Assign Officer ---
function openAssignModal() {
  assignForm.assigned_officer_id = blotter.value?.assigned_officer_id || '';
  assignForm.notes = '';
  assignModalInstance = new Modal(assignModalRef.value);
  assignModalInstance.show();
}

function closeAssignModal() {
  assignModalInstance?.hide();
}

async function handleAssignOfficer() {
  isAssigning.value = true;
  try {
    const response = await api.put(`/blotters/${blotter.value.id}/assign`, assignForm);
    blotter.value = response.data.blotter;
    closeAssignModal();
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to assign officer.');
  } finally {
    isAssigning.value = false;
  }
}

// --- Add Party ---
async function handleAddParty(partyData) {
  isAddingParty.value = true;
  try {
    const response = await api.post(`/blotters/${blotter.value.id}/parties`, partyData);
    blotter.value = response.data.blotter;
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to add party member.');
  } finally {
    isAddingParty.value = false;
  }
}

// --- Helpers ---
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

onMounted(() => {
  fetchBlotter();
  fetchUsers();
});
</script>

<style scoped>
.w-35 {
  width: 35%;
}
</style>
