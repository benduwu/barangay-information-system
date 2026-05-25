<template>
  <div class="timeline">
    <div v-if="!updates || updates.length === 0" class="text-center py-4 text-muted small">
      No status updates logged for this record.
    </div>
    <div v-else class="timeline-container position-relative ps-4 py-2">
      <!-- Vertical line -->
      <div class="timeline-line"></div>

      <!-- Timeline Items -->
      <div v-for="update in sortedUpdates" :key="update.id" class="timeline-item mb-4 position-relative">
        <!-- Circle indicator -->
        <span class="timeline-marker shadow-sm d-flex align-items-center justify-content-center" :class="getMarkerClass(update.new_status)">
          <i class="bi" :class="getMarkerIcon(update.new_status)"></i>
        </span>

        <!-- Card info -->
        <div class="card border-0 bg-light shadow-xs py-2 px-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="fw-bold text-dark small">
              Status changed from
              <span class="text-muted">{{ formatStatus(update.previous_status) }}</span>
              to
              <span class="fw-bold">{{ formatStatus(update.new_status) }}</span>
            </span>
            <small class="text-muted" style="font-size: 0.7rem;">
              {{ formatDate(update.created_at) }}
            </small>
          </div>
          <p class="mb-1 text-secondary small" style="white-space: pre-wrap;">{{ update.notes }}</p>
          <div class="d-flex align-items-center gap-1 mt-1" style="font-size: 0.7rem; color: #64748b;">
            <i class="bi bi-person-fill"></i>
            <span>Logged by: {{ update.updated_by_name }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  updates: {
    type: Array,
    required: true,
    default: () => []
  }
});

// Sort updates descending by date / ID
const sortedUpdates = computed(() => {
  return [...props.updates].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

function formatStatus(status) {
  if (!status || status === 'none') return 'None';
  if (status === 'under_investigation') return 'Under Investigation';
  return status.charAt(0).toUpperCase() + status.slice(1);
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function getMarkerClass(status) {
  switch (status?.toLowerCase()) {
    case 'filed':
      return 'bg-secondary text-white';
    case 'under_investigation':
      return 'bg-warning text-dark';
    case 'settled':
      return 'bg-success text-white';
    case 'escalated':
      return 'bg-danger text-white';
    default:
      return 'bg-secondary text-white';
  }
}

function getMarkerIcon(status) {
  switch (status?.toLowerCase()) {
    case 'filed':
      return 'bi-file-earmark-plus-fill';
    case 'under_investigation':
      return 'bi-search';
    case 'settled':
      return 'bi-check-circle-fill';
    case 'escalated':
      return 'bi-exclamation-triangle-fill';
    default:
      return 'bi-info-circle-fill';
  }
}
</script>

<style scoped>
.timeline-container {
  border-left: 2px solid #e2e8f0;
}

.timeline-marker {
  position: absolute;
  left: -33px;
  top: 4px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  font-size: 0.6875rem;
  border: 2px solid #fff;
  z-index: 2;
}

.shadow-xs {
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.timeline-item {
  position: relative;
}
</style>
