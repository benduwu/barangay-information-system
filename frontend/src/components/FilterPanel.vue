<template>
  <div class="card mb-4 border-0 shadow-sm overflow-hidden">
    <div class="card-body p-4">
      <h5 class="card-title d-flex align-items-center gap-2 mb-3" style="font-weight: 600; color: var(--bs-primary);">
        <i class="bi bi-filter-left fs-4"></i>
        <span>Filter Report Parameters</span>
      </h5>
      <form @submit.prevent="applyFilters" class="row g-3">
        <!-- Date Range -->
        <div class="col-md-5">
          <label class="form-label small text-muted" style="font-weight: 500;">Start Date</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted">
              <i class="bi bi-calendar-event"></i>
            </span>
            <input
              type="date"
              v-model="filters.start_date"
              class="form-control border-start-0 ps-0"
              style="font-size: 0.875rem;"
            />
          </div>
        </div>

        <div class="col-md-5">
          <label class="form-label small text-muted" style="font-weight: 500;">End Date</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted">
              <i class="bi bi-calendar-event"></i>
            </span>
            <input
              type="date"
              v-model="filters.end_date"
              class="form-control border-start-0 ps-0"
              style="font-size: 0.875rem;"
            />
          </div>
        </div>

        <!-- Actions -->
        <div class="col-md-2 d-flex align-items-end gap-2">
          <button
            type="submit"
            class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2"
            style="font-size: 0.875rem; font-weight: 500; transition: transform 0.2s;"
            onmouseover="this.style.transform='translateY(-1px)'"
            onmouseout="this.style.transform='none'"
          >
            <i class="bi bi-check-circle"></i>
            <span>Apply</span>
          </button>
          
          <button
            type="button"
            @click="resetFilters"
            class="btn btn-outline-secondary d-flex align-items-center justify-content-center py-2"
            title="Reset Filters"
            style="width: 42px;"
          >
            <i class="bi bi-arrow-counterclockwise"></i>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  initialStartDate: {
    type: String,
    default: ''
  },
  initialEndDate: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['filter']);

const filters = ref({
  start_date: props.initialStartDate,
  end_date: props.initialEndDate
});

// Set default ranges to start of month and end of month if empty
onMounted(() => {
  const now = new Date();
  if (!filters.value.start_date) {
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    filters.value.start_date = formatDate(firstDay);
  }
  if (!filters.value.end_date) {
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    filters.value.end_date = formatDate(lastDay);
  }
  
  // Emit initial values
  emit('filter', { ...filters.value });
});

function formatDate(date) {
  const d = new Date(date);
  let month = '' + (d.getMonth() + 1);
  let day = '' + d.getDate();
  const year = d.getFullYear();

  if (month.length < 2) month = '0' + month;
  if (day.length < 2) day = '0' + day;

  return [year, month, day].join('-');
}

function applyFilters() {
  emit('filter', { ...filters.value });
}

function resetFilters() {
  const now = new Date();
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
  const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
  
  filters.value.start_date = formatDate(firstDay);
  filters.value.end_date = formatDate(lastDay);
  
  emit('filter', { ...filters.value });
}
</script>

<style scoped>
.form-control:focus {
  border-color: #cbd5e1;
  box-shadow: none;
}
.input-group:focus-within {
  box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.08);
  border-radius: 0.375rem;
}
.input-group:focus-within .input-group-text,
.input-group:focus-within .form-control {
  border-color: var(--bs-primary-light, #3b82f6) !important;
}
</style>
