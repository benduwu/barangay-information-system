<template>
  <span class="badge-custom" :class="badgeClass">
    {{ displayLabel }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    required: true
  }
});

const displayLabel = computed(() => {
  if (!props.status) return 'Unknown';
  return props.status.charAt(0).toUpperCase() + props.status.slice(1);
});

const badgeClass = computed(() => {
  switch (props.status?.toLowerCase()) {
    case 'pending':
      return 'badge-pending';
    case 'approved':
      return 'badge-approved';
    case 'rejected':
      return 'badge-rejected';
    case 'released':
      return 'badge-released';
    default:
      return 'badge-secondary';
  }
});
</script>

<style scoped>
.badge-custom {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 6px;
  display: inline-block;
}

.badge-pending {
  background-color: rgba(245, 158, 11, 0.1);
  color: #d97706;
}

.badge-approved {
  background-color: rgba(59, 130, 246, 0.1);
  color: #2563eb;
}

.badge-rejected {
  background-color: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.badge-released {
  background-color: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.badge-secondary {
  background-color: rgba(100, 116, 139, 0.1);
  color: #475569;
}
</style>
