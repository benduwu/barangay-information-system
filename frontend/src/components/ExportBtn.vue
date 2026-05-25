<template>
  <div class="dropdown d-inline-block">
    <button
      class="btn btn-primary d-flex align-items-center gap-2 px-3 py-2"
      type="button"
      id="exportDropdown"
      data-bs-toggle="dropdown"
      aria-expanded="false"
      :disabled="isExporting"
      style="font-weight: 500; font-size: 0.875rem; border-radius: 6px; box-shadow: 0 2px 4px rgba(30, 58, 95, 0.08); transition: all 0.2s;"
      onmouseover="this.style.transform='translateY(-1px)'"
      onmouseout="this.style.transform='none'"
    >
      <span v-if="isExporting" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
      <i v-else class="bi bi-download"></i>
      <span>{{ isExporting ? 'Exporting...' : 'Export Report' }}</span>
      <i class="bi bi-chevron-down small"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="exportDropdown" style="border-radius: 8px;">
      <li>
        <button class="dropdown-item d-flex align-items-center gap-2 py-2" @click="handleExport('pdf')">
          <i class="bi bi-file-pdf-fill text-danger fs-5"></i>
          <div>
            <div style="font-weight: 500; font-size: 0.8125rem;">Download PDF Document</div>
            <div style="font-size: 0.6875rem; color: #94a3b8;">Best for physical printing & seals</div>
          </div>
        </button>
      </li>
      <li><hr class="dropdown-divider my-1" style="border-color: #f1f5f9;" /></li>
      <li>
        <button class="dropdown-item d-flex align-items-center gap-2 py-2" @click="handleExport('excel')">
          <i class="bi bi-file-excel-fill text-success fs-5"></i>
          <div>
            <div style="font-weight: 500; font-size: 0.8125rem;">Export Excel Spreadsheet</div>
            <div style="font-size: 0.6875rem; color: #94a3b8;">Best for records sorting & analysis</div>
          </div>
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../services/api';

const props = defineProps({
  reportType: {
    type: String,
    required: true // 'residents', 'blotters', 'monthly'
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['export-success', 'export-error']);
const isExporting = ref(false);

async function handleExport(format) {
  isExporting.value = ref(true);
  try {
    const endpoint = `/reports/export/${format}`;
    const payload = {
      report_type: props.reportType,
      ...props.filters
    };

    const response = await api.post(endpoint, payload, {
      responseType: 'blob'
    });

    // Determine filename
    const disposition = response.headers['content-disposition'];
    let filename = `report_${props.reportType}_${new Date().getTime()}.${format === 'pdf' ? 'pdf' : 'xlsx'}`;
    
    if (disposition && disposition.indexOf('attachment') !== -1) {
      const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
      const matches = filenameRegex.exec(disposition);
      if (matches != null && matches[1]) { 
        filename = matches[1].replace(/['"]/g, '');
      }
    }

    // Trigger download
    const blob = new Blob([response.data], {
      type: format === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    });
    
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);

    emit('export-success', { format, filename });
  } catch (error) {
    console.error('Export error:', error);
    emit('export-error', error);
    alert('Failed to generate report export. Ensure you have administrator permissions.');
  } finally {
    isExporting.value = false;
  }
}
</script>

<style scoped>
.dropdown-item {
  transition: background-color 0.15s;
}
.dropdown-item:hover {
  background-color: #f8fafc;
}
</style>
