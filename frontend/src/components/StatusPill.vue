<template>
  <span class="status-pill" :class="pillClass">
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
  if (props.status === 'under_investigation') return 'Under Investigation';
  return props.status.charAt(0).toUpperCase() + props.status.slice(1);
});

const pillClass = computed(() => {
  switch (props.status?.toLowerCase()) {
    case 'filed':
      return 'pill-filed';
    case 'under_investigation':
      return 'pill-investigation';
    case 'settled':
      return 'pill-settled';
    case 'escalated':
      return 'pill-escalated';
    default:
      return 'pill-secondary';
  }
});
</script>

<style scoped>
.status-pill {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 20px;
  display: inline-block;
  text-align: center;
}

.pill-filed {
  background-color: #f1f5f9;
  color: #475569;
}

.pill-investigation {
  background-color: rgba(245, 158, 11, 0.1);
  color: #d97706;
}

.pill-settled {
  background-color: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.pill-escalated {
  background-color: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.pill-secondary {
  background-color: #f1f5f9;
  color: #475569;
}
</style>
