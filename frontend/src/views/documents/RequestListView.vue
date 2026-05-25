<template>
  <div>
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h4 class="mb-1" style="font-weight: 700;">Document Requests Registry</h4>
        <p class="mb-0 text-muted" style="font-size: 0.8125rem;">
          Track and process barangay clearances, residency certificates, and indigency claims.
        </p>
      </div>
      <button class="btn btn-primary" @click="navigateCreate" id="new-request-btn">
        <i class="bi bi-file-earmark-plus-fill me-1"></i> New Request
      </button>
    </div>

    <!-- Filters Panel -->
    <div class="card mb-4 border-0 shadow-sm">
      <div class="card-body py-3">
        <div class="row g-3 align-items-end">
          <!-- Search Resident -->
          <div class="col-md-4">
            <label class="form-label text-muted small fw-semibold">Search Resident</label>
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
              </span>
              <input
                type="text"
                class="form-control border-start-0 ps-0"
                placeholder="Search by name..."
                v-model="filters.search"
                @input="debouncedFetch"
              />
            </div>
          </div>

          <!-- Document Type -->
          <div class="col-md-3">
            <label class="form-label text-muted small fw-semibold">Document Type</label>
            <select class="form-select" v-model="filters.document_type" @change="fetchRequests">
              <option value="">All Types</option>
              <option value="clearance">Barangay Clearance</option>
              <option value="residency">Certificate of Residency</option>
              <option value="indigency">Certificate of Indigency</option>
            </select>
          </div>

          <!-- Status -->
          <div class="col-md-3">
            <label class="form-label text-muted small fw-semibold">Status</label>
            <select class="form-select" v-model="filters.status" @change="fetchRequests">
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
              <option value="released">Released</option>
            </select>
          </div>

          <!-- Reset -->
          <div class="col-md-2 text-end">
            <button class="btn btn-outline-secondary w-100" @click="resetFilters" title="Reset Filters">
              <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
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
          <table class="table table-hover align-middle mb-0" id="documents-table">
            <thead class="table-light text-muted" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">
              <tr>
                <th class="ps-4">Control #</th>
                <th>Resident</th>
                <th>Document Type</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Request Date</th>
                <th class="text-end pe-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Loading State -->
              <tr v-if="isLoading">
                <td colspan="7" class="text-center py-5 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                  Loading document requests registry...
                </td>
              </tr>
              <!-- Empty State -->
              <tr v-else-if="requests.length === 0">
                <td colspan="7" class="text-center py-5 text-muted">
                  <i class="bi bi-file-earmark-x fs-1"></i>
                  <p class="mt-2 mb-0">No document requests found.</p>
                </td>
              </tr>
              <!-- Row Data -->
              <tr v-else v-for="req in requests" :key="req.id">
                <td class="ps-4 fw-bold text-primary" style="font-size: 0.875rem;">
                  {{ req.control_number }}
                </td>
                <td>
                  <div class="fw-bold text-dark">{{ req.resident_name }}</div>
                  <div class="text-muted small" style="font-size: 0.75rem;">ID: #{{ req.resident_id }}</div>
                </td>
                <td>
                  <span class="fw-semibold text-secondary" style="font-size: 0.875rem;">
                    {{ req.document_type_label }}
                  </span>
                </td>
                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" :title="req.purpose">
                  {{ req.purpose }}
                </td>
                <td>
                  <StatusBadge :status="req.status" />
                </td>
                <td style="font-size: 0.8125rem;">
                  {{ formatDate(req.created_at) }}
                </td>
                <td class="text-end pe-4">
                  <div class="d-flex gap-1 justify-content-end">
                    <!-- Actions for Pending -->
                    <template v-if="req.status === 'pending'">
                      <button class="btn btn-success btn-xs py-1 px-2 text-white" title="Approve Request" @click="approveRequest(req.id)">
                        <i class="bi bi-check-lg"></i>
                      </button>
                      <button class="btn btn-danger btn-xs py-1 px-2 text-white" title="Reject Request" @click="confirmReject(req)">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </template>

                    <!-- Actions for Approved -->
                    <template v-if="req.status === 'approved'">
                      <button class="btn btn-primary btn-xs py-1 px-2 text-white" title="Release & Pay" @click="openReleaseModal(req)">
                        <i class="bi bi-cash-coin me-1"></i> Release
                      </button>
                      <button class="btn btn-outline-info btn-xs py-1 px-2" title="Preview PDF" @click="previewPdf(req.id)">
                        <i class="bi bi-file-pdf"></i>
                      </button>
                    </template>

                    <!-- Actions for Released -->
                    <template v-if="req.status === 'released'">
                      <button class="btn btn-outline-info btn-xs py-1 px-2" title="Preview PDF" @click="previewPdf(req.id)">
                        <i class="bi bi-file-pdf me-1"></i> Print PDF
                      </button>
                    </template>

                    <!-- Info icon for details / tooltip -->
                    <button class="btn btn-ghost btn-sm" title="View details" @click="viewDetails(req)">
                      <i class="bi bi-info-circle text-muted"></i>
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
          of {{ meta.total }} requests
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

    <!-- Details View Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" ref="detailsModalRef">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title fw-bold text-secondary">
              <i class="bi bi-file-earmark-text me-2"></i>Request Specifications
            </h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.75rem;"></button>
          </div>
          <div class="modal-body py-3" v-if="selectedRequest">
            <div class="table-responsive">
              <table class="table table-bordered table-sm small align-middle mb-0">
                <tbody>
                  <tr>
                    <td class="fw-semibold bg-light text-muted w-35">Control Number</td>
                    <td class="fw-bold text-primary">{{ selectedRequest.control_number }}</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold bg-light text-muted">Resident Name</td>
                    <td>{{ selectedRequest.resident_name }} (ID #{{ selectedRequest.resident_id }})</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold bg-light text-muted">Document Type</td>
                    <td class="fw-bold">{{ selectedRequest.document_type_label }}</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold bg-light text-muted">Purpose</td>
                    <td>{{ selectedRequest.purpose }}</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold bg-light text-muted">Status</td>
                    <td><StatusBadge :status="selectedRequest.status" /></td>
                  </tr>
                  <tr v-if="selectedRequest.status === 'rejected'">
                    <td class="fw-semibold bg-light text-danger">Rejection Reason</td>
                    <td class="text-danger">{{ selectedRequest.rejection_reason }}</td>
                  </tr>
                  <tr v-if="selectedRequest.status === 'released'">
                    <td class="fw-semibold bg-light text-muted">Amount Paid</td>
                    <td>₱{{ selectedRequest.amount }}</td>
                  </tr>
                  <tr v-if="selectedRequest.status === 'released'">
                    <td class="fw-semibold bg-light text-muted">Official Receipt (OR)</td>
                    <td>{{ selectedRequest.official_receipt_no }}</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold bg-light text-muted">Processor</td>
                    <td>{{ selectedRequest.processor_name }}</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold bg-light text-muted">Date Requested</td>
                    <td>{{ formatDate(selectedRequest.created_at, true) }}</td>
                  </tr>
                  <tr v-if="selectedRequest.issued_date">
                    <td class="fw-semibold bg-light text-muted">Date Issued</td>
                    <td>{{ formatDate(selectedRequest.issued_date, true) }}</td>
                  </tr>
                  <tr v-if="selectedRequest.valid_until">
                    <td class="fw-semibold bg-light text-muted">Expiration Date</td>
                    <td>{{ formatDate(selectedRequest.valid_until, true) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Prompt Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" ref="rejectModalRef">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title text-danger">
              <i class="bi bi-x-circle-fill me-2"></i>Reject Request
            </h6>
          </div>
          <form @submit.prevent="rejectRequest">
            <div class="modal-body" style="font-size: 0.875rem;">
              <p class="mb-2">Are you sure you want to reject request <strong>{{ selectedRequest?.control_number }}</strong>?</p>
              <div>
                <label for="rejectReason" class="form-label text-muted small fw-semibold">Reason for Rejection</label>
                <textarea
                  id="rejectReason"
                  class="form-control form-control-sm"
                  rows="3"
                  v-model="rejectionReason"
                  placeholder="Provide clarification details..."
                  required
                ></textarea>
              </div>
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-sm btn-outline-secondary" @click="closeRejectModal">Cancel</button>
              <button type="submit" class="btn btn-sm btn-danger" :disabled="isProcessing">
                <span v-if="isProcessing" class="spinner-border spinner-border-sm me-1"></span>
                Reject Request
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Payment Release Modal Component -->
    <PaymentModal
      ref="paymentModalRef"
      :documentControlNumber="selectedRequest?.control_number || ''"
      :isSubmitting="isProcessing"
      @submit="releaseRequest"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Modal } from 'bootstrap';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';
import StatusBadge from '../../components/StatusBadge.vue';
import PaymentModal from '../../components/PaymentModal.vue';

const router = useRouter();
const authStore = useAuthStore();

// --- State ---
const requests = ref([]);
const isLoading = ref(false);
const isProcessing = ref(false);
const successMessage = ref('');
const selectedRequest = ref(null);
const rejectionReason = ref('');

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const filters = reactive({
  search: '',
  document_type: '',
  status: '',
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
  debounceTimer = setTimeout(() => fetchRequests(), 300);
}

async function fetchRequests(page = 1) {
  isLoading.value = true;
  try {
    const params = { page, per_page: meta.per_page };
    if (filters.search) params.search = filters.search;
    if (filters.document_type) params.document_type = filters.document_type;
    if (filters.status) params.status = filters.status;

    const response = await api.get('/documents', { params });
    requests.value = response.data.documents;
    Object.assign(meta, response.data.meta);
  } catch (e) {
    console.error('Failed to fetch document requests:', e);
  } finally {
    isLoading.value = false;
  }
}

function goToPage(page) {
  if (page < 1 || page > meta.last_page) return;
  fetchRequests(page);
}

function resetFilters() {
  filters.search = '';
  filters.document_type = '';
  filters.status = '';
  fetchRequests();
}

function navigateCreate() {
  router.push(`/${authStore.role}/documents/create`);
}

function previewPdf(id) {
  router.push(`/${authStore.role}/documents/${id}/preview`);
}

function formatDate(dateStr, includeTime = false) {
  if (!dateStr) return 'N/A';
  const date = new Date(dateStr);
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  if (includeTime) {
    options.hour = '2-digit';
    options.minute = '2-digit';
  }
  return date.toLocaleDateString(undefined, options);
}

// --- Action triggers ---
async function approveRequest(id) {
  if (!confirm('Are you sure you want to approve this request?')) return;
  
  isLoading.value = true;
  try {
    const response = await api.put(`/documents/${id}/approve`);
    successMessage.value = response.data.message;
    fetchRequests(meta.current_page);
    setTimeout(() => (successMessage.value = ''), 4000);
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to approve request.');
  } finally {
    isLoading.value = false;
  }
}

// Rejection handling
const rejectModalRef = ref(null);
let rejectModalInstance = null;

function confirmReject(req) {
  selectedRequest.value = req;
  rejectionReason.value = '';
  rejectModalInstance = new Modal(rejectModalRef.value);
  rejectModalInstance.show();
}

function closeRejectModal() {
  rejectModalInstance?.hide();
}

async function rejectRequest() {
  isProcessing.value = true;
  try {
    const response = await api.put(`/documents/${selectedRequest.value.id}/reject`, {
      rejection_reason: rejectionReason.value
    });
    closeRejectModal();
    successMessage.value = response.data.message;
    fetchRequests(meta.current_page);
    setTimeout(() => (successMessage.value = ''), 4000);
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to reject request.');
  } finally {
    isProcessing.value = false;
  }
}

// Release payment modal triggers
const paymentModalRef = ref(null);

function openReleaseModal(req) {
  selectedRequest.value = req;
  paymentModalRef.value?.open();
}

async function releaseRequest(paymentData) {
  isProcessing.value = true;
  try {
    const response = await api.put(`/documents/${selectedRequest.value.id}/release`, paymentData);
    paymentModalRef.value?.close();
    successMessage.value = response.data.message;
    fetchRequests(meta.current_page);
    setTimeout(() => (successMessage.value = ''), 4000);
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to release document.');
  } finally {
    isProcessing.value = false;
  }
}

// Specifications modal details
const detailsModalRef = ref(null);
let detailsModalInstance = null;

function viewDetails(req) {
  selectedRequest.value = req;
  detailsModalInstance = new Modal(detailsModalRef.value);
  detailsModalInstance.show();
}

onMounted(() => {
  fetchRequests();
});
</script>

<style scoped>
.btn-xs {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
}

.table th {
  letter-spacing: 0.5px;
}

.w-35 {
  width: 35%;
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
</style>
