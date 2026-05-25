<template>
  <div class="admin-announcements-page">
    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h2 class="fw-bold text-slate-800 mb-1">Announcements</h2>
        <p class="text-slate-500 mb-0">Manage and publish community bulletins.</p>
      </div>
      <router-link
        :to="{ name: `${authStore.role}-announcements-create` }"
        class="btn btn-primary d-flex align-items-center gap-2"
      >
        <i class="bi bi-plus-lg"></i>
        <span>New Announcement</span>
      </router-link>
    </div>

    <!-- Stats Summary Cards -->
    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-total">
          <div class="stat-icon"><i class="bi bi-megaphone-fill"></i></div>
          <div>
            <div class="stat-number">{{ stats.total }}</div>
            <div class="stat-label">Total</div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-published">
          <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
          <div>
            <div class="stat-number">{{ stats.published }}</div>
            <div class="stat-label">Published</div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-draft">
          <div class="stat-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
          <div>
            <div class="stat-number">{{ stats.draft }}</div>
            <div class="stat-label">Drafts</div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-expired">
          <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
          <div>
            <div class="stat-number">{{ stats.expired }}</div>
            <div class="stat-label">Expired</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filter-bar mb-4">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
            <input
              type="text"
              class="form-control"
              v-model="searchQuery"
              placeholder="Search announcements..."
              @input="debounceSearch"
            />
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select" v-model="statusFilter" @change="fetchAnnouncements">
            <option value="">All Statuses</option>
            <option value="published">Published</option>
            <option value="draft">Drafts</option>
            <option value="expired">Expired</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select" v-model="priorityFilter" @change="fetchAnnouncements">
            <option value="">All Priorities</option>
            <option value="high">High</option>
            <option value="normal">Normal</option>
            <option value="low">Low</option>
          </select>
        </div>
      </div>
    </div>

    <!-- List -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
      <p class="text-muted mt-3">Loading announcements...</p>
    </div>

    <div v-else-if="announcements.length === 0" class="text-center py-5 bg-white rounded-4 border p-5">
      <div class="empty-icon mx-auto mb-3">
        <i class="bi bi-megaphone text-slate-300 fs-1"></i>
      </div>
      <h4 class="fw-bold text-slate-700">No Announcements Found</h4>
      <p class="text-slate-500">Try adjusting your search or create a new announcement.</p>
    </div>

    <div v-else class="table-responsive bg-white rounded-4 border shadow-sm">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr class="text-slate-500 text-xs uppercase border-bottom" style="letter-spacing: 0.05em;">
            <th class="ps-4 py-3">Title</th>
            <th class="py-3">Priority</th>
            <th class="py-3">Audience</th>
            <th class="py-3">Status</th>
            <th class="py-3">Publish Date</th>
            <th class="py-3 text-end pe-4">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ann in announcements" :key="ann.id" class="announcement-row">
            <td class="ps-4 py-3">
              <div class="fw-semibold text-slate-800" style="max-width: 280px;">{{ ann.title }}</div>
              <small class="text-slate-400">by {{ ann.author?.full_name || 'Unknown' }}</small>
            </td>
            <td>
              <span class="priority-pill" :class="`priority-${ann.priority}`">
                {{ ann.priority }}
              </span>
            </td>
            <td>
              <span v-if="ann.is_barangay_wide" class="badge bg-slate-100 text-slate-700">
                <i class="bi bi-globe2 me-1"></i> All
              </span>
              <span v-else class="badge bg-blue-50 text-blue-700">
                <i class="bi bi-geo-alt me-1"></i> {{ ann.targets?.length || 0 }} Purok(s)
              </span>
            </td>
            <td>
              <span :class="getStatusBadge(ann)">{{ getStatusLabel(ann) }}</span>
            </td>
            <td>
              <span class="text-slate-600 text-sm">{{ formatDate(ann.publish_date) }}</span>
            </td>
            <td class="text-end pe-4">
              <div class="d-flex justify-content-end gap-1">
                <button
                  v-if="!ann.is_published"
                  class="btn btn-sm btn-success-soft"
                  title="Publish"
                  @click="publishAnnouncement(ann)"
                  :disabled="publishing === ann.id"
                >
                  <span v-if="publishing === ann.id" class="spinner-border spinner-border-sm"></span>
                  <i v-else class="bi bi-send-fill"></i>
                </button>
                <router-link
                  :to="{ name: `${authStore.role}-announcements-edit`, params: { id: ann.id } }"
                  class="btn btn-sm btn-primary-soft"
                  title="Edit"
                >
                  <i class="bi bi-pencil-fill"></i>
                </router-link>
                <button
                  class="btn btn-sm btn-danger-soft"
                  title="Delete"
                  @click="confirmDelete(ann)"
                  :disabled="deleting === ann.id"
                >
                  <span v-if="deleting === ann.id" class="spinner-border spinner-border-sm"></span>
                  <i v-else class="bi bi-trash-fill"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="meta.last_page > 1" class="d-flex justify-content-center mt-4">
      <nav>
        <ul class="pagination pagination-sm">
          <li class="page-item" :class="{ disabled: meta.current_page <= 1 }">
            <button class="page-link" @click="goToPage(meta.current_page - 1)">&laquo;</button>
          </li>
          <li
            v-for="p in meta.last_page"
            :key="p"
            class="page-item"
            :class="{ active: p === meta.current_page }"
          >
            <button class="page-link" @click="goToPage(p)">{{ p }}</button>
          </li>
          <li class="page-item" :class="{ disabled: meta.current_page >= meta.last_page }">
            <button class="page-link" @click="goToPage(meta.current_page + 1)">&raquo;</button>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-backdrop-custom" @click.self="showDeleteModal = false">
      <div class="modal-dialog-custom">
        <div class="modal-content-custom">
          <div class="text-center">
            <div class="delete-icon-circle mx-auto mb-3">
              <i class="bi bi-exclamation-triangle-fill text-danger fs-2"></i>
            </div>
            <h5 class="fw-bold mb-2">Delete Announcement</h5>
            <p class="text-muted mb-4">
              Are you sure you want to delete "<strong>{{ announcementToDelete?.title }}</strong>"? This action cannot be undone.
            </p>
            <div class="d-flex justify-content-center gap-2">
              <button class="btn btn-outline-secondary" @click="showDeleteModal = false">Cancel</button>
              <button class="btn btn-danger" @click="deleteAnnouncement" :disabled="deleting">
                <span v-if="deleting" class="spinner-border spinner-border-sm me-1"></span>
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';

const authStore = useAuthStore();

const announcements = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const statusFilter = ref('');
const priorityFilter = ref('');
const publishing = ref(null);
const deleting = ref(null);
const showDeleteModal = ref(false);
const announcementToDelete = ref(null);

const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const stats = reactive({
  total: 0,
  published: 0,
  draft: 0,
  expired: 0,
});

const fetchAnnouncements = async (page = 1) => {
  try {
    loading.value = true;
    const params = { page, per_page: 10 };
    if (searchQuery.value) params.search = searchQuery.value;
    if (statusFilter.value) params.status = statusFilter.value;
    if (priorityFilter.value) params.priority = priorityFilter.value;

    const { data } = await api.get('/admin/announcements', { params });
    announcements.value = data.announcements;
    meta.value = data.meta;
  } catch (err) {
    console.error('Failed to load announcements:', err);
  } finally {
    loading.value = false;
  }
};

const fetchStats = async () => {
  try {
    // Total
    const totalRes = await api.get('/admin/announcements', { params: { per_page: 1 } });
    stats.total = totalRes.data.meta.total;

    // Published
    const pubRes = await api.get('/admin/announcements', { params: { per_page: 1, status: 'published' } });
    stats.published = pubRes.data.meta.total;

    // Drafts
    const draftRes = await api.get('/admin/announcements', { params: { per_page: 1, status: 'draft' } });
    stats.draft = draftRes.data.meta.total;

    // Expired
    const expRes = await api.get('/admin/announcements', { params: { per_page: 1, status: 'expired' } });
    stats.expired = expRes.data.meta.total;
  } catch (err) {
    console.error('Failed to fetch stats:', err);
  }
};

const goToPage = (page) => {
  if (page < 1 || page > meta.value.last_page) return;
  fetchAnnouncements(page);
};

let searchTimeout = null;
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchAnnouncements();
  }, 400);
};

const publishAnnouncement = async (ann) => {
  publishing.value = ann.id;
  try {
    await api.post(`/admin/announcements/${ann.id}/publish`);
    ann.is_published = true;
    ann.publish_date = ann.publish_date || new Date().toISOString();
    fetchStats();
  } catch (err) {
    console.error('Publish failed:', err);
  } finally {
    publishing.value = null;
  }
};

const confirmDelete = (ann) => {
  announcementToDelete.value = ann;
  showDeleteModal.value = true;
};

const deleteAnnouncement = async () => {
  if (!announcementToDelete.value) return;
  deleting.value = announcementToDelete.value.id;
  try {
    await api.delete(`/admin/announcements/${announcementToDelete.value.id}`);
    showDeleteModal.value = false;
    announcementToDelete.value = null;
    fetchAnnouncements(meta.value.current_page);
    fetchStats();
  } catch (err) {
    console.error('Delete failed:', err);
  } finally {
    deleting.value = null;
  }
};

const getStatusLabel = (ann) => {
  if (!ann.is_published) return 'Draft';
  const now = new Date();
  if (ann.expiry_date && new Date(ann.expiry_date) <= now) return 'Expired';
  return 'Published';
};

const getStatusBadge = (ann) => {
  const label = getStatusLabel(ann);
  if (label === 'Draft') return 'badge bg-warning-soft text-warning-dark';
  if (label === 'Published') return 'badge bg-success-soft text-success-dark';
  return 'badge bg-danger-soft text-danger-dark';
};

const formatDate = (dateStr) => {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

onMounted(() => {
  fetchAnnouncements();
  fetchStats();
});
</script>

<style scoped>
.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: #fff;
  border-radius: 1rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.06);
}

.stat-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.stat-total .stat-icon { background: #eff6ff; color: #3b82f6; }
.stat-published .stat-icon { background: #d1fae5; color: #10b981; }
.stat-draft .stat-icon { background: #fef3c7; color: #f59e0b; }
.stat-expired .stat-icon { background: #fee2e2; color: #ef4444; }

.stat-number {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
  line-height: 1;
}

.stat-label {
  font-size: 0.8125rem;
  color: #64748b;
  font-weight: 500;
  margin-top: 0.25rem;
}

.filter-bar {
  background: #fff;
  border-radius: 1rem;
  padding: 1rem 1.5rem;
  border: 1px solid #e2e8f0;
}

.priority-pill {
  padding: 0.25rem 0.625rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.priority-high { background: #fee2e2; color: #dc2626; }
.priority-normal { background: #dbeafe; color: #2563eb; }
.priority-low { background: #d1fae5; color: #059669; }

.bg-slate-100 { background: #f1f5f9; }
.text-slate-700 { color: #334155; }
.bg-blue-50 { background: #eff6ff; }
.text-blue-700 { color: #1d4ed8; }

.bg-success-soft { background: #d1fae5; }
.text-success-dark { color: #065f46; }
.bg-warning-soft { background: #fef3c7; }
.text-warning-dark { color: #92400e; }
.bg-danger-soft { background: #fee2e2; }
.text-danger-dark { color: #991b1b; }

.btn-primary-soft {
  background: #eff6ff;
  color: #2563eb;
  border: none;
  transition: all 0.15s ease;
}
.btn-primary-soft:hover { background: #dbeafe; color: #1d4ed8; }

.btn-success-soft {
  background: #d1fae5;
  color: #059669;
  border: none;
  transition: all 0.15s ease;
}
.btn-success-soft:hover { background: #a7f3d0; color: #047857; }

.btn-danger-soft {
  background: #fee2e2;
  color: #dc2626;
  border: none;
  transition: all 0.15s ease;
}
.btn-danger-soft:hover { background: #fecaca; color: #b91c1c; }

.announcement-row {
  transition: background-color 0.15s ease;
}
.announcement-row:hover {
  background-color: #f8fafc;
}

.empty-icon {
  width: 5rem;
  height: 5rem;
  border-radius: 50%;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Delete Modal */
.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(4px);
}

.modal-dialog-custom {
  width: 100%;
  max-width: 420px;
}

.modal-content-custom {
  background: #fff;
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
}

.delete-icon-circle {
  width: 4rem;
  height: 4rem;
  border-radius: 50%;
  background: #fee2e2;
  display: flex;
  align-items: center;
  justify-content: center;
}

.text-slate-800 { color: #1e293b; }
.text-slate-500 { color: #64748b; }
.text-slate-400 { color: #94a3b8; }
.text-slate-300 { color: #cbd5e1; }
.text-slate-600 { color: #475569; }
</style>
