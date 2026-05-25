<template>
  <div>
    <!-- Welcome Section -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="p-4 rounded-3 text-white" style="background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-light, #2d5a8e) 100%); box-shadow: 0 4px 15px rgba(30, 58, 95, 0.15);">
          <h2 class="mb-1" style="font-weight: 700; letter-spacing: -0.5px;">
            Welcome back, {{ authStore.fullName }}!
          </h2>
          <p class="mb-0" style="opacity: 0.85; font-size: 0.9375rem;">
            Here's an analytical overview of your Barangay Information & Operations System.
          </p>
        </div>
      </div>
    </div>

    <!-- Stats Cards (The 4 Summary Cards) -->
    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card border-0 shadow-sm" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
          <div class="stat-icon" style="background: rgba(30, 58, 95, 0.1); color: var(--bs-primary);">
            <i class="bi bi-people-fill"></i>
          </div>
          <div class="stat-value">{{ stats.totalResidents }}</div>
          <div class="stat-label">Total Residents</div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card border-0 shadow-sm" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
          <div class="stat-icon" style="background: rgba(240, 165, 0, 0.1); color: var(--bs-accent);">
            <i class="bi bi-file-earmark-text-fill"></i>
          </div>
          <div class="stat-value">{{ stats.pendingDocs }}</div>
          <div class="stat-label">Pending Clearances</div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card border-0 shadow-sm" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
          <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--bs-danger);">
            <i class="bi bi-journal-text"></i>
          </div>
          <div class="stat-value">{{ stats.activeBlotters }}</div>
          <div class="stat-label">Active Blotters</div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card border-0 shadow-sm" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
          <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--bs-success);">
            <i class="bi bi-megaphone-fill"></i>
          </div>
          <div class="stat-value">{{ announcements.length }}</div>
          <div class="stat-label">Announcements</div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-3">
      <!-- Announcements Section -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
            <span class="fw-bold text-dark"><i class="bi bi-megaphone-fill me-2 text-success"></i>Community Announcements</span>
            <span class="badge bg-success-subtle text-success small" style="font-size:0.75rem;">Latest Broadcasts</span>
          </div>
          <div class="card-body p-0">
            <div class="list-group list-group-flush">
              <div v-for="ann in announcements" :key="ann.id" class="list-group-item p-3 border-0 border-bottom border-light">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <h6 class="mb-0 fw-bold text-primary">{{ ann.title }}</h6>
                  <span class="text-muted small" style="font-size: 0.75rem;">{{ ann.date }}</span>
                </div>
                <p class="mb-0 text-muted small" style="line-height: 1.5;">{{ ann.content }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions Sidebar -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white py-3 border-0">
            <span class="fw-bold text-dark"><i class="bi bi-lightning-fill me-2 text-warning"></i>Administrative Hub</span>
          </div>
          <div class="card-body p-0">
            <div class="list-group list-group-flush">
              <router-link :to="{ name: 'admin-residents' }" class="list-group-item list-group-item-action d-flex align-items-center gap-3 p-3 border-0 border-bottom border-light">
                <div class="stat-icon mb-0" style="width:36px; height:36px; font-size:0.875rem; background: rgba(30,58,95,0.08); color: var(--bs-primary);">
                  <i class="bi bi-person-vcard-fill"></i>
                </div>
                <div>
                  <div style="font-weight: 600; font-size: 0.8125rem;">Residents Registry</div>
                  <div style="font-size: 0.6875rem; color: #94a3b8;">Manage family households</div>
                </div>
                <i class="bi bi-chevron-right ms-auto text-muted small"></i>
              </router-link>

              <router-link :to="{ name: 'admin-documents' }" class="list-group-item list-group-item-action d-flex align-items-center gap-3 p-3 border-0 border-bottom border-light">
                <div class="stat-icon mb-0" style="width:36px; height:36px; font-size:0.875rem; background: rgba(240,165,0,0.08); color: var(--bs-accent);">
                  <i class="bi bi-file-earmark-plus-fill"></i>
                </div>
                <div>
                  <div style="font-weight: 600; font-size: 0.8125rem;">Issue Certificates</div>
                  <div style="font-size: 0.6875rem; color: #94a3b8;">Clearances and Indigency forms</div>
                </div>
                <i class="bi bi-chevron-right ms-auto text-muted small"></i>
              </router-link>

              <router-link :to="{ name: 'admin-blotters' }" class="list-group-item list-group-item-action d-flex align-items-center gap-3 p-3 border-0 border-bottom border-light">
                <div class="stat-icon mb-0" style="width:36px; height:36px; font-size:0.875rem; background: rgba(239,68,68,0.08); color: var(--bs-danger);">
                  <i class="bi bi-journal-plus"></i>
                </div>
                <div>
                  <div style="font-weight: 600; font-size: 0.8125rem;">Blotter Center</div>
                  <div style="font-size: 0.6875rem; color: #94a3b8;">Incident logs and disputation trails</div>
                </div>
                <i class="bi bi-chevron-right ms-auto text-muted small"></i>
              </router-link>

              <router-link :to="{ name: 'admin-reports' }" class="list-group-item list-group-item-action d-flex align-items-center gap-3 p-3 border-0">
                <div class="stat-icon mb-0" style="width:36px; height:36px; font-size:0.875rem; background: rgba(16,185,129,0.08); color: var(--bs-success);">
                  <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                  <div style="font-weight: 600; font-size: 0.8125rem;">Reports & Analytics</div>
                  <div style="font-size: 0.6875rem; color: #94a3b8;">Excel & PDF statistical compilations</div>
                </div>
                <i class="bi bi-chevron-right ms-auto text-muted small"></i>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';

const authStore = useAuthStore();

const stats = ref({
  totalResidents: 0,
  pendingDocs: 0,
  activeBlotters: 0
});

const announcements = ref([
  {
    id: 1,
    title: 'Barangay General Assembly',
    date: 'May 30, 2026',
    content: 'All residents are invited to the upcoming General Assembly at 2:00 PM in the Barangay gymnasium to discuss safety protocols, neighborhood watch setups, and digitalization pathways.'
  },
  {
    id: 2,
    title: 'Purok Cleanup Drive',
    date: 'June 02, 2026',
    content: 'A joint community clean-up operation across Purok 1 through 5 will commence at 6:00 AM this Saturday. Residents are requested to bring personal collection gear.'
  },
  {
    id: 3,
    title: 'Digital Clearance Advisory',
    date: 'June 05, 2026',
    content: 'Clearance application workflow is fully digitized. Application clearances are now processed in under 5 minutes through automated control key assignments.'
  }
]);

onMounted(async () => {
  try {
    // 1. Total Residents
    const resResponse = await api.get('/residents', { params: { per_page: 1 } });
    stats.value.totalResidents = resResponse.data.meta?.total || 0;

    // 2. Pending Clearances
    const docResponse = await api.get('/documents', { params: { per_page: 1, status: 'pending' } });
    stats.value.pendingDocs = docResponse.data.meta?.total || 0;

    // 3. Active Blotters
    const blotterResponse1 = await api.get('/blotters', { params: { per_page: 1, status: 'filed' } });
    const blotterResponse2 = await api.get('/blotters', { params: { per_page: 1, status: 'under_investigation' } });
    stats.value.activeBlotters = (blotterResponse1.data.meta?.total || 0) + (blotterResponse2.data.meta?.total || 0);
  } catch (e) {
    console.error('Stats load failure:', e);
  }
});
</script>

<style scoped>
.metric-card {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
}
</style>
