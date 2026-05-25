<template>
  <div class="app-layout">
    <!-- Sidebar Overlay (mobile) -->
    <div
      v-if="sidebarOpen"
      class="sidebar-overlay d-lg-none"
      @click="sidebarOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside class="sidebar" :class="{ show: sidebarOpen }">
      <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
          <i class="bi bi-building"></i>
        </div>
        <div class="sidebar-brand-text">
          Barangay IS
          <small>Information System</small>
        </div>
      </div>

      <nav class="sidebar-nav">
        <div class="sidebar-section-title">Main Menu</div>

        <!-- Admin Links -->
        <template v-if="authStore.role === 'admin'">
          <router-link :to="{ name: 'admin-dashboard' }" class="sidebar-link" @click="closeSidebar">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
          </router-link>
          <router-link :to="{ name: 'admin-users' }" class="sidebar-link" @click="closeSidebar">
            <i class="bi bi-people-fill"></i>
            <span>User Management</span>
          </router-link>
        </template>

        <!-- Staff Links -->
        <template v-if="authStore.role === 'staff'">
          <router-link :to="{ name: 'staff-dashboard' }" class="sidebar-link" @click="closeSidebar">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
          </router-link>
        </template>

        <!-- Modules -->
        <div class="sidebar-section-title">Modules</div>
        <router-link
          :to="{ name: authStore.role === 'admin' ? 'admin-residents' : 'staff-residents' }"
          class="sidebar-link"
          @click="closeSidebar"
        >
          <i class="bi bi-person-vcard-fill"></i>
          <span>Residents</span>
        </router-link>
        <router-link
          :to="{ name: authStore.role === 'admin' ? 'admin-documents' : 'staff-documents' }"
          class="sidebar-link"
          @click="closeSidebar"
        >
          <i class="bi bi-file-earmark-text-fill"></i>
          <span>Documents</span>
        </router-link>
        <router-link
          :to="{ name: authStore.role === 'admin' ? 'admin-blotters' : 'staff-blotters' }"
          class="sidebar-link"
          @click="closeSidebar"
        >
          <i class="bi bi-journal-text"></i>
          <span>Blotter</span>
        </router-link>
        <router-link
          v-if="authStore.role === 'admin'"
          :to="{ name: 'admin-reports' }"
          class="sidebar-link"
          @click="closeSidebar"
        >
          <i class="bi bi-graph-up-arrow"></i>
          <span>Reports</span>
        </router-link>
        <router-link
          :to="{ name: authStore.role === 'admin' ? 'admin-announcements' : 'staff-announcements' }"
          class="sidebar-link"
          @click="closeSidebar"
        >
          <i class="bi bi-megaphone-fill"></i>
          <span>Announcements</span>
        </router-link>
        <router-link
          v-if="authStore.role === 'admin'"
          :to="{ name: 'admin-workflow-logs' }"
          class="sidebar-link"
          @click="closeSidebar"
        >
          <i class="bi bi-cpu-fill"></i>
          <span>Workflow Automation</span>
        </router-link>
      </nav>

      <!-- Sidebar Footer -->
      <div class="sidebar-footer p-3 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
        <div class="d-flex align-items-center gap-2">
          <div class="topbar-avatar" style="width:32px; height:32px; font-size: 0.75rem;">
            {{ authStore.initials }}
          </div>
          <div style="flex:1; min-width:0;">
            <div style="font-size: 0.8125rem; font-weight: 500; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
              {{ authStore.fullName }}
            </div>
            <div style="font-size: 0.6875rem; color: #94a3b8; text-transform: capitalize;">
              {{ authStore.role }}
            </div>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Top Bar -->
      <header class="topbar">
        <div class="d-flex align-items-center gap-3">
          <button
            class="btn btn-ghost d-lg-none"
            @click="sidebarOpen = !sidebarOpen"
            id="sidebar-toggle"
          >
            <i class="bi bi-list fs-5"></i>
          </button>
          <h1 class="topbar-title mb-0">{{ pageTitle }}</h1>
        </div>

        <div class="topbar-right">
          <div class="dropdown">
            <button
              class="btn btn-ghost d-flex align-items-center gap-2"
              type="button"
              id="user-dropdown"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <div class="topbar-avatar">{{ authStore.initials }}</div>
              <span class="d-none d-md-inline" style="font-size: 0.875rem; font-weight: 500;">
                {{ authStore.fullName }}
              </span>
              <i class="bi bi-chevron-down" style="font-size: 0.75rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="user-dropdown">
              <li>
                <span class="dropdown-item-text" style="font-size: 0.75rem; color: #94a3b8;">
                  Signed in as <strong class="text-dark">{{ authStore.user?.username }}</strong>
                </span>
              </li>
              <li><hr class="dropdown-divider" /></li>
              <li>
                <button class="dropdown-item text-danger" @click="handleLogout" id="logout-btn">
                  <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                </button>
              </li>
            </ul>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="page-content">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const authStore = useAuthStore();
const sidebarOpen = ref(false);

const pageTitle = computed(() => {
  const titles = {
    'admin-dashboard': 'Dashboard',
    'admin-users': 'User Management',
    'staff-dashboard': 'Dashboard',
    'admin-residents': 'Residents Registry',
    'admin-residents-create': 'Register Resident',
    'admin-residents-profile': 'Resident Profile Card',
    'admin-residents-edit': 'Edit Resident Profile',
    'admin-documents': 'Documents Registry',
    'admin-documents-create': 'Create Document Request',
    'admin-documents-preview': 'Certificate Preview',
    'admin-blotters': 'Blotter & Incident Records',
    'admin-blotters-create': 'File Incident Blotter',
    'admin-blotters-detail': 'Blotter Case Details',
    'admin-reports': 'Barangay Analytics & Reports',
    'staff-residents': 'Residents Registry',
    'staff-residents-create': 'Register Resident',
    'staff-residents-profile': 'Resident Profile Card',
    'staff-residents-edit': 'Edit Resident Profile',
    'staff-documents': 'Documents Registry',
    'staff-documents-create': 'Create Document Request',
    'staff-documents-preview': 'Certificate Preview',
    'staff-blotters': 'Blotter & Incident Records',
    'staff-blotters-create': 'File Incident Blotter',
    'staff-blotters-detail': 'Blotter Case Details',
    'admin-announcements': 'Announcements',
    'admin-announcements-create': 'Create Announcement',
    'admin-announcements-edit': 'Edit Announcement',
    'staff-announcements': 'Announcements',
    'staff-announcements-create': 'Create Announcement',
    'staff-announcements-edit': 'Edit Announcement',
    'admin-workflow-logs': 'n8n Workflow Automation',
  };
  return titles[route.name] || 'Dashboard';
});

function closeSidebar() {
  sidebarOpen.value = false;
}

async function handleLogout() {
  await authStore.logout();
}
</script>

<style scoped>
.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1035;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.sidebar-footer {
  margin-top: auto;
}
</style>
