<template>
  <div class="public-feed-page">
    <!-- Navbar Header -->
    <header class="navbar navbar-expand-lg navbar-dark bg-primary-gradient shadow-sm">
      <div class="container py-2">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
          <i class="bi bi-building fs-3"></i>
          <div>
            <span class="fw-bold d-block leading-tight">Barangay Information Portal</span>
            <small class="text-xs text-blue-100 font-normal">Official Announcement & News Feed</small>
          </div>
        </a>
        <div class="d-flex align-items-center gap-3">
          <router-link :to="{ name: 'login' }" class="btn btn-light-glass btn-sm font-semibold">
            <i class="bi bi-box-arrow-in-right me-1"></i> Staff Login
          </router-link>
        </div>
      </div>
    </header>

    <!-- Hero Announcement Board Banner -->
    <section class="hero-banner py-5 text-center text-white bg-slate-900 position-relative overflow-hidden">
      <div class="glow-effect"></div>
      <div class="container position-relative z-1">
        <h1 class="display-5 fw-extrabold mb-3">Community Announcements</h1>
        <p class="lead text-slate-300 max-w-2xl mx-auto font-medium">
          Stay informed about local events, security warnings, administrative schedules, and public notices in your purok.
        </p>
      </div>
    </section>

    <!-- Main Content Area -->
    <main class="container py-5">
      <div class="row">
        <!-- Sidebar filters -->
        <div class="col-lg-3 mb-4">
          <div class="filter-card shadow-sm p-4">
            <h4 class="filter-title mb-3">Filter News</h4>
            
            <div class="mb-4">
              <label class="form-label text-slate-500 font-semibold text-xs uppercase tracking-wider mb-2">Search</label>
              <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-slate-400">
                  <i class="bi bi-search"></i>
                </span>
                <input 
                  type="text" 
                  class="form-control border-start-0 ps-0" 
                  v-model="searchQuery" 
                  placeholder="Search headlines..."
                  @input="debounceSearch"
                />
              </div>
            </div>

            <div>
              <label class="form-label text-slate-500 font-semibold text-xs uppercase tracking-wider mb-2">Priority Level</label>
              <div class="d-flex flex-column gap-2">
                <button 
                  class="btn btn-filter text-start" 
                  :class="{ active: selectedPriority === '' }"
                  @click="setPriority('')"
                >
                  <span class="dot bg-slate-400"></span> All Announcements
                </button>
                <button 
                  class="btn btn-filter text-start" 
                  :class="{ active: selectedPriority === 'high' }"
                  @click="setPriority('high')"
                >
                  <span class="dot bg-danger"></span> High Priority Only
                </button>
                <button 
                  class="btn btn-filter text-start" 
                  :class="{ active: selectedPriority === 'normal' }"
                  @click="setPriority('normal')"
                >
                  <span class="dot bg-primary"></span> Normal Priority
                </button>
                <button 
                  class="btn btn-filter text-start" 
                  :class="{ active: selectedPriority === 'low' }"
                  @click="setPriority('low')"
                >
                  <span class="dot bg-success"></span> Low Priority
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- News Feed Timeline -->
        <div class="col-lg-9">
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary spinner-lg mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
            <p class="text-muted font-medium">Fetching active bulletins...</p>
          </div>

          <div v-else-if="error" class="alert alert-danger shadow-sm border-0 d-flex align-items-center gap-3 p-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-2"></i>
            <div>
              <h5 class="alert-heading fw-bold mb-1">Unable to load feed</h5>
              <p class="mb-0">{{ error }}</p>
            </div>
          </div>

          <div v-else-if="announcements.length === 0" class="text-center py-5 empty-feed-state shadow-sm bg-white rounded-4 border p-5">
            <div class="empty-icon-circle mx-auto mb-4">
              <i class="bi bi-megaphone text-slate-400 fs-1"></i>
            </div>
            <h3 class="fw-bold text-slate-800 mb-2">No Announcements Found</h3>
            <p class="text-slate-500 max-w-md mx-auto">
              There are currently no active bulletins matching your filters. Please check back later for updates.
            </p>
          </div>

          <div v-else class="announcements-timeline">
            <div class="grid-layout">
              <AnnouncementCard 
                v-for="ann in announcements" 
                :key="ann.id" 
                :announcement="ann" 
              />
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="footer bg-slate-900 py-4 text-center text-slate-400 mt-auto border-top border-slate-800">
      <div class="container">
        <p class="mb-1 font-semibold text-white">Barangay Information Portal &copy; 2026</p>
        <p class="text-xs mb-0">Powered by Automated Workflow Management System</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AnnouncementCard from '../../components/AnnouncementCard.vue';

const announcements = ref([]);
const loading = ref(true);
const error = ref(null);
const searchQuery = ref('');
const selectedPriority = ref('');

const getApiBaseUrl = () => {
  return import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';
};

const fetchAnnouncements = async () => {
  try {
    loading.value = true;
    error.value = null;
    
    // Public endpoint doesn't require Bearer token
    const params = {};
    if (searchQuery.value) params.search = searchQuery.value;
    if (selectedPriority.value) params.priority = selectedPriority.value;

    const response = await axios.get(`${getApiBaseUrl()}/announcements`, { params });
    announcements.value = response.data.announcements;
  } catch (err) {
    console.error('Error fetching public announcements:', err);
    error.value = 'We encountered an issue retrieving the latest bulletins. Please refresh the page.';
  } finally {
    loading.value = false;
  }
};

const setPriority = (priority) => {
  selectedPriority.value = priority;
  fetchAnnouncements();
};

let searchDebounceTimeout = null;
const debounceSearch = () => {
  clearTimeout(searchDebounceTimeout);
  searchDebounceTimeout = setTimeout(() => {
    fetchAnnouncements();
  }, 400);
};

onMounted(() => {
  fetchAnnouncements();
});
</script>

<style scoped>
.public-feed-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #f8fafc;
}

.bg-primary-gradient {
  background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.btn-light-glass {
  background: rgba(255, 255, 255, 0.15);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.25);
  transition: all 0.2s ease;
}

.btn-light-glass:hover {
  background: #fff;
  color: #1e40af;
  border-color: #white;
}

.hero-banner {
  background: radial-gradient(circle at top right, #1e293b 0%, #0f172a 100%);
  box-shadow: inset 0 -10px 20px rgba(0,0,0,0.1);
}

.glow-effect {
  position: absolute;
  top: -50%;
  left: -20%;
  width: 100%;
  height: 200%;
  background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 60%);
  pointer-events: none;
}

.filter-card {
  background: #ffffff;
  border-radius: 1rem;
  border: 1px solid #e2e8f0;
}

.filter-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
}

.btn-filter {
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #475569;
  display: flex;
  align-items: center;
  gap: 0.625rem;
  border: 1px solid transparent;
  transition: all 0.15s ease;
  background: transparent;
}

.btn-filter:hover {
  background-color: #f1f5f9;
  color: #1e293b;
}

.btn-filter.active {
  background-color: #eff6ff;
  color: #1e40af;
  border-color: #bfdbfe;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.grid-layout {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.empty-feed-state {
  border: 1px dashed #cbd5e1;
}

.empty-icon-circle {
  width: 5rem;
  height: 5rem;
  background-color: #f1f5f9;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.max-w-2xl {
  max-w: 42rem;
}

.max-w-md {
  max-w: 28rem;
}

.mx-auto {
  margin-left: auto;
  margin-right: auto;
}

.font-semibold {
  font-weight: 600;
}

.font-medium {
  font-weight: 500;
}

.bg-slate-900 {
  background-color: #0f172a;
}

.bg-slate-400 {
  background-color: #94a3b8;
}

.text-slate-300 {
  color: #cbd5e1;
}

.text-slate-500 {
  color: #64748b;
}

.text-slate-400 {
  color: #94a3b8;
}

.text-slate-800 {
  color: #1e293b;
}

.z-1 {
  z-index: 1;
}

.leading-tight {
  line-height: 1.25;
}
</style>
