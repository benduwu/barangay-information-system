<template>
  <div class="announcement-card" :class="priorityClass">
    <div v-if="announcement.image_path" class="card-image-wrapper">
      <img :src="getImageUrl(announcement.image_path)" alt="Announcement Graphic" class="card-img" />
      <span class="priority-badge" :class="priorityBadgeClass">
        {{ announcement.priority }}
      </span>
    </div>
    
    <div class="card-body-content">
      <div v-if="!announcement.image_path" class="d-flex align-items-center justify-content-between mb-3">
        <span class="priority-badge-standalone" :class="priorityBadgeClass">
          {{ announcement.priority }} priority
        </span>
        <span v-if="announcement.is_barangay_wide" class="badge bg-slate-100 text-slate-700">
          <i class="bi bi-globe me-1"></i> Community-wide
        </span>
        <span v-else class="badge bg-blue-50 text-blue-700 border border-blue-100">
          <i class="bi bi-geo-alt-fill me-1"></i> Targeted
        </span>
      </div>

      <div v-else class="d-flex align-items-center justify-content-between mb-2">
        <span v-if="announcement.is_barangay_wide" class="badge bg-slate-100 text-slate-700">
          <i class="bi bi-globe me-1"></i> Community-wide
        </span>
        <span v-else class="badge bg-blue-50 text-blue-700 border border-blue-100">
          <i class="bi bi-geo-alt-fill me-1"></i> Targeted
        </span>
      </div>

      <h3 class="announcement-title">{{ announcement.title }}</h3>
      
      <p class="announcement-narrative">{{ announcement.content }}</p>
      
      <div v-if="!announcement.is_barangay_wide && announcement.targets?.length" class="targeted-puroks-list mb-3">
        <span class="text-xs text-muted me-2">Targets:</span>
        <span v-for="t in announcement.targets" :key="t.id" class="purok-tag">
          {{ t.purok?.purok_name }}
        </span>
      </div>

      <div class="card-footer-meta">
        <div class="author-info">
          <div class="avatar-circle">
            {{ getInitials(announcement.author?.full_name) }}
          </div>
          <div class="author-details">
            <span class="author-name">{{ announcement.author?.full_name || 'System Admin' }}</span>
            <span class="publish-time">{{ formatDate(announcement.publish_date) }}</span>
          </div>
        </div>
        
        <div v-if="isAdminOrStaff" class="admin-actions-overlay">
          <slot name="admin-actions"></slot>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  announcement: {
    type: Object,
    required: true
  },
  isAdminOrStaff: {
    type: Boolean,
    default: false
  }
});

const priorityClass = computed(() => {
  return `priority-${props.announcement.priority}`;
});

const priorityBadgeClass = computed(() => {
  return `badge-${props.announcement.priority}`;
});

const getImageUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  const base = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';
  const origin = base.replace('/api/v1', '');
  return `${origin}/storage/${path}`;
};

const getInitials = (name) => {
  if (!name) return 'SA';
  const parts = name.split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.slice(0, 2).toUpperCase();
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'Draft';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<style scoped>
.announcement-card {
  background: #ffffff;
  border-radius: 1rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
  overflow: hidden;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
  display: flex;
  flex-direction: column;
}

.announcement-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Priority styling left borders */
.priority-high {
  border-left: 4px solid #ef4444;
}
.priority-normal {
  border-left: 4px solid #3b82f6;
}
.priority-low {
  border-left: 4px solid #10b981;
}

.card-image-wrapper {
  position: relative;
  width: 100%;
  height: 200px;
  overflow: hidden;
}

.card-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.announcement-card:hover .card-img {
  transform: scale(1.05);
}

.priority-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.priority-badge-standalone {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge-high {
  background-color: #fee2e2;
  color: #ef4444;
}

.badge-normal {
  background-color: #dbeafe;
  color: #3b82f6;
}

.badge-low {
  background-color: #d1fae5;
  color: #10b981;
}

.card-body-content {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.announcement-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.75rem;
  line-height: 1.4;
}

.announcement-narrative {
  font-size: 0.9375rem;
  color: #475569;
  line-height: 1.6;
  margin-bottom: 1.25rem;
  white-space: pre-wrap;
}

.targeted-puroks-list {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.35rem;
}

.purok-tag {
  background: #f1f5f9;
  color: #64748b;
  font-size: 0.75rem;
  font-weight: 500;
  padding: 0.125rem 0.5rem;
  border-radius: 0.375rem;
}

.card-footer-meta {
  margin-top: auto;
  border-top: 1px solid #f1f5f9;
  padding-top: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.author-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.avatar-circle {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 50%;
  background: #e2e8f0;
  color: #475569;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.8125rem;
}

.author-details {
  display: flex;
  flex-direction: column;
}

.author-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1e293b;
}

.publish-time {
  font-size: 0.75rem;
  color: #64748b;
  margin-top: 0.125rem;
}

.bg-slate-100 {
  background-color: #f1f5f9;
}
.text-slate-700 {
  color: #334155;
}
.bg-blue-50 {
  background-color: #eff6ff;
}
.text-blue-700 {
  color: #1d4ed8;
}
.border-blue-100 {
  border-color: #dbeafe;
}
</style>
